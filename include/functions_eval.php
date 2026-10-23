<?php
/**
 * Shared evaluation logic for Proteas (Αξιολόγηση Εκπαιδευτικών)
 * Centralizes excluded schools, lookups, SQL candidate query generation,
 * and pedagogical/scientific consultant resolution.
 * 
 * - get_eval_excluded_schools($conn): Υπολογίζει τα εξαιρούμενα σχολεία (Άλλο ΠΥΣΠΕ/ΠΥΣΔΕ, εξωτερικό, φορείς, ΔΙΠΕ, σχολεία συμβούλων) 
 * και επιστρέφει τα σχετικά IDs και SQL strings.
 * 
 * - get_eval_lookups($conn): Φορτώνει τους συμβούλους επιστημονικής ευθύνης (symvouloi_epist), τους συμβούλους ΠΕ60 και τους εν ενεργεία διευθυντές.
 * 
 * - build_eval_evaluees_query($conn, $sxol_etos, $filters, $excluded_info): Κατασκευάζει δυναμικά το ενιαίο SQL query επιλογής αξιολογούμενων 
 * (με το subquery υπολογισμού κύριας τοποθέτησης primary_sch_id, τα joins με συμβούλους και διευθυντές, τα βασικά κριτήρια αποκλεισμού και τα 
 * επιμέρους φίλτρα διορισμού/κλάδου/μονιμοποίησης/μεμονωμένου εκπαιδευτικού).
 * 
 * - resolve_evaluee_consultants(array $row, array $se_by_klados): Αναλαμβάνει τον πλήρη προσδιορισμό του επιστημονικού συμβούλου (Α1) και του 
 * παιδαγωγικού συμβούλου (Α2), ενσωματώνοντας όλους τους κανόνες:
 *  - Κριτήρια Ειδικής Αγωγής (κλάδοι ΕΑΕ εκτός τμημάτων ένταξης γενικών σχολείων, ειδικά σχολεία).
 *  - Εκπαιδευτικοί σε Τμήμα Ένταξης γενικών σχολείων: αξιολογούνται στο Α1 από τον σύμβουλο γενικής αγωγής (ΠΕ60/ΠΕ70) του σχολείου.
 *  - Ανάθεση συμβούλου Ειδικής Αγωγής ανά κλάδο (Νηπιαγωγοί vs Δάσκαλοι) και ανά σχολείο.
 *  - Ανάθεση σε γενική αγωγή και fallback στον παιδαγωγικό σύμβουλο για ΠΕ60/ΠΕ70.
 *  - Ανάθεση του συμβούλου ΕΑΕ και ως παιδαγωγικού αν πρόκειται για ειδικό σχολείο.
 *  - Υπολογισμός ταύτισης (taytish).
 * 
 * - clean_str / normalize_afm: Καθολικές βοηθητικές συναρτήσεις καθαρισμού και κανονικοποίησης ΑΦΜ.
 */

if (!function_exists('clean_str')) {
    function clean_str($val) {
        if ($val === null) return "";
        $s = trim((string)$val);
        $s = str_replace(["\xC2\xA0", "\xEF\xBB\xBF"], [' ', ''], $s);
        return trim($s);
    }
}

if (!function_exists('normalize_afm')) {
    function normalize_afm($afm) {
        $afm_clean = clean_str($afm);
        if (ctype_digit($afm_clean) && strlen($afm_clean) <= 9) {
            return str_pad($afm_clean, 9, '0', STR_PAD_LEFT);
        }
        return $afm_clean;
    }
}

/**
 * Determines whether an employee is a Director or School Head (Διευθυντής / Προϊστάμενος).
 * Evaluates:
 * 1. thesi == 2 (Διευθυντής / Προϊστάμενος)
 * 2. dnt_afm == emp_afm (School's active director matches the employee)
 * 3. Employee's AFM is in the active directors lookup table ($all_director_afms)
 *
 * @param array $row Database row containing employee details (thesi, emp_afm, dnt_afm, etc.)
 * @param array $all_director_afms Array map of normalized director AFMs (from get_eval_lookups)
 * @return bool
 */
if (!function_exists('is_eval_director')) {
    function is_eval_director(array $row, array $all_director_afms = []) {
        $thesi = (int)($row['thesi'] ?? 0);
        if ($thesi === 2) {
            return true;
        }
        $emp_afm = normalize_afm($row['emp_afm'] ?? $row['afm'] ?? '');
        if (empty($emp_afm)) {
            return false;
        }
        $dnt_afm = normalize_afm($row['dnt_afm'] ?? '');
        if (!empty($dnt_afm) && $dnt_afm === $emp_afm) {
            return true;
        }
        if (!empty($all_director_afms) && isset($all_director_afms[$emp_afm])) {
            return true;
        }
        return false;
    }
}

/**
 * Returns IDs and comma-separated string lists of schools to exclude from evaluation
 * (other PYSPE/PYSDE, foreign, organizational bodies, DIPE, consultant schools).
 */
function get_eval_excluded_schools($conn) {
    $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ', $conn);
    $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ', $conn);
    $ekswteriko = getSchoolID('Απόσπαση στο εξωτερικό', $conn);
    $foreas = getSchoolID('Απόσπαση σε φορέα', $conn);
    $sxol_symv = getSchoolID('Σύμβουλος Εκπαίδευσης', $conn) ?: getSchoolID('Σύμβουλος εκπαίδευσης', $conn);
    $dipe = '398';

    $symv_school_ids = $sxol_symv ? [(int)$sxol_symv] : [];
    $res_symv_s = mysqli_query($conn, "SELECT id FROM school WHERE name LIKE '%Σύμβουλος Εκπαίδευσης%' OR name LIKE '%Σύμβουλος εκπαίδευσης%'");
    if ($res_symv_s) {
        while ($r_s = mysqli_fetch_assoc($res_symv_s)) {
            $symv_school_ids[] = (int)$r_s['id'];
        }
    }
    if (empty($symv_school_ids)) {
        $symv_school_ids = [397];
    }
    $symv_school_ids = array_values(array_unique($symv_school_ids));
    $symv_school_ids_str = implode(',', $symv_school_ids);

    $excluded_schools = array_values(array_unique(array_filter([
        $allo_pysde,
        $allo_pyspe,
        $dipe,
        $foreas,
        $ekswteriko,
        ...$symv_school_ids
    ])));
    $excluded_schools_str = implode(',', $excluded_schools);

    return [
        'symv_school_ids' => $symv_school_ids,
        'symv_school_ids_str' => $symv_school_ids_str,
        'excluded_schools' => $excluded_schools,
        'excluded_schools_str' => $excluded_schools_str,
    ];
}

/**
 * Pre-loads lookup datasets (scientific consultants by klados, PE60 consultants, directors).
 */
function get_eval_lookups($conn) {
    $se_by_klados = [];
    $se_edra_allou = [];
    $se_aksiologhsh_ews = [];

    $se_res = mysqli_query($conn, "SELECT id, klados, afm, eponymo, onoma, emp_id, sch_ids, edra_allou, aksiologhsh_ews FROM symvouloi_epist");
    if ($se_res) {
        while ($se_row = mysqli_fetch_assoc($se_res)) {
            $se_by_klados[$se_row['klados']][] = $se_row;
            $c_afm = trim($se_row['afm']);
            if (!empty($c_afm)) {
                if (!empty($se_row['edra_allou'])) {
                    $se_edra_allou[$c_afm] = true;
                }
                if (!empty($se_row['aksiologhsh_ews']) && $se_row['aksiologhsh_ews'] !== '0000-00-00') {
                    $se_aksiologhsh_ews[$c_afm] = $se_row['aksiologhsh_ews'];
                }
            }
        }
    }

    $pe60_consultant_afms = [];
    $res_pe60 = mysqli_query($conn, "SELECT e.afm FROM symvouloi s JOIN employee e ON s.emp_id = e.id WHERE s.perif >= 600 OR e.klados = 1");
    if ($res_pe60) {
        while ($r_pe60 = mysqli_fetch_assoc($res_pe60)) {
            if (!empty($r_pe60['afm'])) {
                $pe60_consultant_afms[trim($r_pe60['afm'])] = true;
            }
        }
    }

    $all_director_afms = [];
    $res_dir = mysqli_query($conn, "SELECT afm FROM employee WHERE thesi = 2 AND status IN (1,3)");
    if ($res_dir) {
        while ($r_dir = mysqli_fetch_assoc($res_dir)) {
            if (!empty($r_dir['afm'])) {
                $all_director_afms[normalize_afm($r_dir['afm'])] = true;
            }
        }
    }

    return [
        'se_by_klados' => $se_by_klados,
        'se_edra_allou' => $se_edra_allou,
        'se_aksiologhsh_ews' => $se_aksiologhsh_ews,
        'pe60_consultant_afms' => $pe60_consultant_afms,
        'all_director_afms' => $all_director_afms,
    ];
}

/**
 * Builds the standard SQL query to select evaluee candidates.
 *
 * @param mysqli $conn
 * @param string|int $sxol_etos School year (e.g. '2026-27' or int)
 * @param array $filters Optional filters:
 *                       - emp_id: int (single employee)
 *                       - emp_ids: array of ints
 *                       - hm_dior_from: string YYYY-MM-DD
 *                       - hm_dior_to: string YYYY-MM-DD
 *                       - klados: array of ints or comma-separated string
 *                       - monimopoihsh: int/string
 *                       - ignore_evaluated: bool (default true, filters e.aksiologhsh = 0 OR NULL)
 *                       - order_by: bool (default true, orders by hm_dior, surname, name)
 * @param array|null $excluded_info Result from get_eval_excluded_schools (computed automatically if null)
 * @return string The SQL query
 */
function build_eval_evaluees_query($conn, $sxol_etos, array $filters = [], $excluded_info = null) {
    if ($excluded_info === null) {
        $excluded_info = get_eval_excluded_schools($conn);
    }
    $excluded_schools_str = $excluded_info['excluded_schools_str'];
    $symv_school_ids_str = $excluded_info['symv_school_ids_str'];
    $sxol_etos_esc = mysqli_real_escape_string($conn, (string)$sxol_etos);

    $query = "SELECT
        e.id as emp_id,
        e.am as emp_am,
        e.am,
        e.patrwnymo,
        e.surname as emp_surname,
        e.name as emp_name,
        e.afm as emp_afm,
        e.wres,
        e.sx_yphrethshs,
        sp.surname as symv_paid_surname,
        sp.name as symv_paid_name,
        sp.afm as symv_paid_afm,
        d.surname as dnt_surname,
        d.name as dnt_name,
        d.afm as dnt_afm,
        k.perigrafh as klados,
        e.klados as klados_id,
        s.name as sch_name,
        s.name as default_sch_name,
        s.id as sch_id,
        s.type2,
        s.perif,
        e.hm_dior,
        e.thesi,
        e.ent_ty
    FROM employee e
    LEFT JOIN klados k ON e.klados = k.id
    LEFT JOIN (
        SELECT 
            emp_id,
            SUBSTRING_INDEX(
                GROUP_CONCAT(
                    yphrethsh 
                    ORDER BY 
                        total_hours DESC, 
                        is_sx_yphr DESC,
                        yphrethsh ASC
                    SEPARATOR ','
                ), 
                ',', 
                1
            ) AS primary_sch_id
        FROM (
            SELECT 
                y.emp_id,
                y.yphrethsh,
                SUM(y.hours + 0) as total_hours,
                MAX(y.yphrethsh = e.sx_yphrethshs) as is_sx_yphr
            FROM yphrethsh y
            JOIN employee e ON y.emp_id = e.id
            WHERE y.sxol_etos = '$sxol_etos_esc'
            GROUP BY y.emp_id, y.yphrethsh
        ) sch_hours
        GROUP BY emp_id
    ) yp ON e.id = yp.emp_id
    LEFT JOIN school s ON COALESCE(yp.primary_sch_id, e.sx_yphrethshs) = s.id
    LEFT JOIN symvouloi sm ON s.perif = sm.perif
    LEFT JOIN employee sp ON sm.emp_id = sp.id
    LEFT JOIN employee d ON (s.id = d.sx_yphrethshs AND d.thesi = 2 AND d.status IN (1,3))
    WHERE 1=1";

    if (!empty($filters['emp_id'])) {
        $single_id = (int)$filters['emp_id'];
        $query .= " AND e.id = $single_id";
    } else {
        $query .= " AND e.status = 1";
        if (!empty($excluded_schools_str)) {
            $query .= " AND e.sx_yphrethshs NOT IN ($excluded_schools_str)";
            $query .= " AND COALESCE(yp.primary_sch_id, e.sx_yphrethshs) NOT IN ($excluded_schools_str)";
        }
        if (!empty($symv_school_ids_str)) {
            $query .= " AND (e.sx_organikhs NOT IN ($symv_school_ids_str) OR e.sx_organikhs IS NULL)";
            $query .= " AND e.id NOT IN (SELECT emp_id FROM yphrethsh WHERE yphrethsh IN ($symv_school_ids_str) AND sxol_etos = '$sxol_etos_esc')";
            $query .= " AND s.id NOT IN ($symv_school_ids_str)";
        }
        $query .= " AND s.type2 IN (0, 2)";

        $ignore_evaluated = $filters['ignore_evaluated'] ?? true;
        if ($ignore_evaluated) {
            $query .= " AND (e.aksiologhsh = 0 OR e.aksiologhsh IS NULL)";
        }
        if (!empty($filters['exclude_directors'])) {
            $query .= " AND e.thesi != 2";
        }
    }

    if (!empty($filters['emp_ids']) && is_array($filters['emp_ids'])) {
        $clean_ids = implode(',', array_filter(array_map('intval', $filters['emp_ids'])));
        if (!empty($clean_ids)) {
            $query .= " AND e.id IN ($clean_ids)";
        }
    }

    if (!empty($filters['hm_dior_from'])) {
        $d_from = mysqli_real_escape_string($conn, $filters['hm_dior_from']);
        $query .= " AND e.hm_dior >= '$d_from'";
    }
    if (!empty($filters['hm_dior_to'])) {
        $d_to = mysqli_real_escape_string($conn, $filters['hm_dior_to']);
        $query .= " AND e.hm_dior <= '$d_to'";
    }

    if (!empty($filters['klados'])) {
        $kl_arr = is_array($filters['klados']) ? $filters['klados'] : explode(',', $filters['klados']);
        $kl_clean = implode(",", array_filter(array_map('intval', $kl_arr)));
        if (!empty($kl_clean)) {
            $query .= " AND e.klados IN ($kl_clean)";
        }
    }

    if (isset($filters['monimopoihsh']) && $filters['monimopoihsh'] !== '') {
        $mon = (int)$filters['monimopoihsh'];
        $query .= " AND e.monimopoihsh = $mon";
    }

    $query .= " GROUP BY e.id";

    $order_by = $filters['order_by'] ?? true;
    if ($order_by && empty($filters['emp_id'])) {
        $query .= " ORDER BY 
            CASE 
                WHEN e.hm_dior >= '2020-01-01' THEN 1 
                WHEN e.hm_dior > '1950-01-01' THEN 2 
                ELSE 3 
            END ASC,
            CASE 
                WHEN e.hm_dior >= '2020-01-01' THEN e.hm_dior 
                ELSE NULL 
            END ASC,
            CASE 
                WHEN e.hm_dior < '2020-01-01' AND e.hm_dior > '1950-01-01' THEN e.hm_dior 
                ELSE NULL 
            END DESC,
            e.surname ASC, 
            e.name ASC";
    }

    return $query;
}

/**
 * Resolves the appropriate pedagogical and scientific consultant for an evaluee.
 *
 * @param array $row The database record for the employee with school, klados, and director info
 * @param array $se_by_klados The array of scientific advisors grouped by klados
 * @param array $all_director_afms Map of director AFMs for identifying directors
 * @return array Evaluation metadata:
 *               - is_eae (bool)
 *               - is_in_ent_ty (bool)
 *               - is_director (bool)
 *               - symv_epist_afm, symv_epist_surname, symv_epist_name
 *               - symv_paid_afm, symv_paid_surname, symv_paid_name
 *               - dnt_afm, dnt_surname, dnt_name
 *               - taytish (bool)
 */
function resolve_evaluee_consultants(array $row, array $se_by_klados, array $all_director_afms = []) {
    $klados_id = (int)($row['klados_id'] ?? 0);
    $klados_str = (string)($row['klados'] ?? '');
    $sch_id = (int)($row['sch_id'] ?? 0);
    $type2 = (int)($row['type2'] ?? 0);
    $is_in_ent_ty = in_array((int)($row['ent_ty'] ?? 0), [1, 2], true);

    $is_eae_klados = (
        in_array($klados_str, ['ΠΕ60ΕΑΕ', 'ΠΕ70ΕΑΕ', 'ΠΕ61', 'ΠΕ71'], true) || 
        in_array($klados_id, [16, 17, 18, 19], true)
    );

    // Εκπαιδευτικοί σε Τμήμα Ένταξης γενικών σχολείων (type2 != 2)
    // αξιολογούνται στο Α1 από τον σύμβουλο γενικής αγωγής του σχολείου (ΠΕ60/ΠΕ70)
    $eval_as_geniki = ($is_in_ent_ty && $type2 !== 2);

    $effective_klados_id = $klados_id;
    $effective_klados_str = $klados_str;
    if ($eval_as_geniki && $is_eae_klados) {
        $is_nipiagwgos = ($klados_id === 1 || in_array($klados_id, [16, 17], true) || $klados_str === 'ΠΕ60' || $klados_str === 'ΠΕ60ΕΑΕ' || $klados_str === 'ΠΕ61');
        if ($is_nipiagwgos) {
            $effective_klados_id = 1;
            $effective_klados_str = 'ΠΕ60';
        } else {
            $effective_klados_id = 2;
            $effective_klados_str = 'ΠΕ70';
        }
    }

    $consultants = $se_by_klados[$effective_klados_id] ?? [];
    $consultant_found = null;
    $default_consultant = null;

    foreach ($consultants as $c) {
        if (!empty($c['sch_ids'])) {
            $c_schools = array_map('intval', explode(',', $c['sch_ids']));
            if (in_array($sch_id, $c_schools, true)) {
                $consultant_found = $c;
                break;
            }
        } else {
            if (!$default_consultant) {
                $default_consultant = $c;
            }
        }
    }

    $is_eae = (
        ($is_eae_klados && !$eval_as_geniki) ||
        ($type2 === 2 && (in_array($klados_str, ['ΠΕ60', 'ΠΕ70'], true) || in_array($klados_id, [1, 2], true) || $is_eae_klados))
    );

    $symv_epist_afm = '';
    $symv_epist_surname = '';
    $symv_epist_name = '';

    $symv_paid_afm = $row['symv_paid_afm'] ?? '';
    $symv_paid_surname = $row['symv_paid_surname'] ?? '';
    $symv_paid_name = $row['symv_paid_name'] ?? '';

    if ($is_eae) {
        $eae_c = null;
        if (in_array($klados_id, [16, 17, 18, 19], true)) {
            $eae_c = $consultant_found ?: $default_consultant;
        }
        if (!$eae_c) {
            $is_nipiagwgos = ($klados_id === 1 || in_array($klados_id, [16, 17], true) || $klados_str === 'ΠΕ60' || $klados_str === 'ΠΕ60ΕΑΕ' || $klados_str === 'ΠΕ61');
            $eae_kladoi = $is_nipiagwgos ? [16, 17] : [18, 19];
            foreach ($eae_kladoi as $ek) {
                if (!empty($se_by_klados[$ek])) {
                    foreach ($se_by_klados[$ek] as $c) {
                        if (!empty($c['sch_ids'])) {
                            $c_schools = array_map('intval', explode(',', $c['sch_ids']));
                            if (in_array($sch_id, $c_schools, true)) {
                                $eae_c = $c;
                                break 2;
                            }
                        } elseif (!$eae_c) {
                            $eae_c = $c;
                        }
                    }
                }
            }
        }

        if ($eae_c) {
            $symv_epist_afm = $eae_c['afm'];
            $symv_epist_surname = $eae_c['eponymo'];
            $symv_epist_name = $eae_c['onoma'];
        } else {
            $symv_epist_afm = $symv_paid_afm;
            $symv_epist_surname = $symv_paid_surname;
            $symv_epist_name = $symv_paid_name;
        }

        if ($type2 === 2 && $eae_c) {
            $symv_paid_afm = $eae_c['afm'];
            $symv_paid_surname = $eae_c['eponymo'];
            $symv_paid_name = $eae_c['onoma'];
        }
    } else {
        if ($consultant_found) {
            $symv_epist_afm = $consultant_found['afm'];
            $symv_epist_surname = $consultant_found['eponymo'];
            $symv_epist_name = $consultant_found['onoma'];
        } elseif ($default_consultant) {
            $symv_epist_afm = $default_consultant['afm'];
            $symv_epist_surname = $default_consultant['eponymo'];
            $symv_epist_name = $default_consultant['onoma'];
        } elseif ($effective_klados_str === 'ΠΕ60' || $effective_klados_str === 'ΠΕ70' || in_array($effective_klados_id, [1, 2], true)) {
            $symv_epist_afm = $symv_paid_afm;
            $symv_epist_surname = $symv_paid_surname;
            $symv_epist_name = $symv_paid_name;
        } else {
            $symv_epist_afm = '';
            $symv_epist_surname = '';
            $symv_epist_name = '';
        }
    }

    $se_afm_clean = normalize_afm($symv_epist_afm);
    $sp_afm_clean = normalize_afm($symv_paid_afm);
    $taytish = (!empty($se_afm_clean) && !empty($sp_afm_clean) && $se_afm_clean === $sp_afm_clean);
    $is_director = is_eval_director($row, $all_director_afms);

    return [
        'is_eae' => $is_eae,
        'is_in_ent_ty' => $is_in_ent_ty,
        'is_director' => $is_director,
        'symv_epist_afm' => $symv_epist_afm,
        'symv_epist_surname' => $symv_epist_surname,
        'symv_epist_name' => $symv_epist_name,
        'symv_paid_afm' => $symv_paid_afm,
        'symv_paid_surname' => $symv_paid_surname,
        'symv_paid_name' => $symv_paid_name,
        'dnt_afm' => $row['dnt_afm'] ?? '',
        'dnt_surname' => $row['dnt_surname'] ?? '',
        'dnt_name' => $row['dnt_name'] ?? '',
        'taytish' => $taytish,
    ];
}
