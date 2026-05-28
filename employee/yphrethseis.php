<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require_once "../config.php";
require_once "../include/functions.php";
require_once "../tools/class.login.php";

$log = new logmein();
if ($_SESSION['loggedin'] == false) {
    header("Location: ../tools/login.php");
    exit;
}

$usrlvl = $_SESSION['userlevel'];
$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

// Handle Delete Operation
if ($_GET['op'] == 'delete') {
    if ($usrlvl == 3) {
        die("<h3>Σφάλμα: Δεν επιτρέπεται η πρόσβαση...</h3>");
    }
    $id = intval($_GET['id']);
    // Fetch details first to redirect back
    $res = mysqli_query($mysqlconnection, "SELECT emp_id, mon_anapl, afm FROM yphrethsh_ext WHERE id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        $emp_id = $row['emp_id'];
        $type = ($row['mon_anapl'] == 'Μόνιμος') ? 'mon' : 'anapl';
        $afm = $row['afm'];
        if (empty($afm)) {
            $table = ($type == 'mon') ? 'employee' : 'ektaktoi';
            $afm_res = mysqli_query($mysqlconnection, "SELECT afm FROM $table WHERE id = $emp_id");
            if ($afm_res && $afm_row = mysqli_fetch_assoc($afm_res)) {
                $afm = $afm_row['afm'];
            }
        }

        // Delete record
        mysqli_query($mysqlconnection, "DELETE FROM yphrethsh_ext WHERE id = $id");
        header("Location: yphrethseis.php?afm=$afm&msg=deleted");
        exit;
    }
    die("<h3>Σφάλμα: Δεν βρέθηκε η εγγραφή.</h3>");
}

// Handle Update Operation
$error = null;
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    if ($usrlvl == 3) {
        die("<h3>Σφάλμα: Δεν επιτρέπεται η πρόσβαση...</h3>");
    }
    $id = intval($_POST['id']);
    $emp_id = intval($_POST['emp_id']);
    $type = $_POST['type'];
    $afm = trim($_POST['afm']);

    $school_name = trim($_POST['school_name']);
    $sxesh = trim($_POST['sxesh']);
    $sxesh_topo = trim($_POST['sxesh_topo']);

    // Dates are submitted in Y-m-d format from the modern_datepicker hidden field
    $date_from = !empty($_POST['date_from']) ? $_POST['date_from'] : null;
    $date_to = !empty($_POST['date_to']) ? $_POST['date_to'] : null;

    $hours = intval($_POST['hours']);
    $state = trim($_POST['state']);
    $sxol_etos = trim($_POST['sxol_etos']);

    // Resolve school name to ID or match existing custom name
    $sch_id_val = 'NULL';
    $sch_code_val = 'NULL';
    $sch_name_val = 'NULL';
    $is_valid = false;

    $sch_id = getSchoolID($school_name, $mysqlconnection);
    if ($sch_id) {
        $sch_id_val = $sch_id;
        $code = getSchoolCode($sch_id, $mysqlconnection);
        $sch_code_val = $code ? "'$code'" : "NULL";
        $is_valid = true;
    } else {
        // Check if it matches existing sch_name on this record
        $check_rec = mysqli_query($mysqlconnection, "SELECT sch_name, sch_code FROM yphrethsh_ext WHERE id = $id");
        if ($check_rec && $check_row = mysqli_fetch_assoc($check_rec)) {
            if ($check_row['sch_name'] === $school_name) {
                $sch_name_val = "'" . mysqli_real_escape_string($mysqlconnection, $school_name) . "'";
                $sch_code_val = $check_row['sch_code'] ? "'" . mysqli_real_escape_string($mysqlconnection, $check_row['sch_code']) . "'" : "NULL";
                $is_valid = true;
            }
        }
    }

    if (!$is_valid) {
        $error = "Το σχολείο '" . htmlspecialchars($school_name) . "' δεν βρέθηκε. Παρακαλώ επιλέξτε ένα έγκυρο σχολείο από την αναδυόμενη λίστα.";
    } else {
        $sql = "UPDATE yphrethsh_ext SET 
                  sch_id = $sch_id_val,
                  sch_code = $sch_code_val,
                  sch_name = $sch_name_val,
                  sxesh = " . ($sxesh ? "'$sxesh'" : "NULL") . ",
                  sxesh_topo = " . ($sxesh_topo ? "'$sxesh_topo'" : "NULL") . ",
                  date_from = " . ($date_from ? "'$date_from'" : "NULL") . ",
                  date_to = " . ($date_to ? "'$date_to'" : "NULL") . ",
                  hours = $hours,
                  state = " . ($state ? "'$state'" : "NULL") . ",
                  sxol_etos = '$sxol_etos'
                WHERE id = $id";

        if (mysqli_query($mysqlconnection, $sql)) {
            header("Location: yphrethseis.php?afm=$afm&msg=updated");
            exit;
        } else {
            $error = "Αποτυχία ενημέρωσης: " . mysqli_error($mysqlconnection);
        }
    }
}

// Context parameters
$afm = trim($_GET['afm'] ?: $_POST['afm']);
$emp_id = intval($_GET['emp_id'] ?: $_POST['emp_id']);
$type = $_GET['type'] ?: $_POST['type'];

if (!$afm && $emp_id && in_array($type, ['mon', 'anapl'])) {
    // Get afm from employee or ektaktoi for backward compatibility
    $table = ($type == 'mon') ? 'employee' : 'ektaktoi';
    $afm_res = mysqli_query($mysqlconnection, "SELECT afm FROM $table WHERE id = $emp_id");
    if ($afm_res && $row = mysqli_fetch_assoc($afm_res)) {
        $afm = $row['afm'];
    }
}

if (!$afm) {
    die("<h3>Σφάλμα: Μη έγκυρες παράμετροι.</h3>");
}
$afm = mysqli_real_escape_string($mysqlconnection, $afm);

// Try to find the employee in 'employee' table (mon)
$emp_query = "SELECT surname, name, afm, id, 'mon' as type FROM employee WHERE afm = '$afm'";
$emp_res = mysqli_query($mysqlconnection, $emp_query);
if ($emp_res && mysqli_num_rows($emp_res) > 0) {
    $employee = mysqli_fetch_assoc($emp_res);
    $emp_id = intval($employee['id']);
    $type = 'mon';
    $mon_anapl_db = 'Μόνιμος';
} else {
    // Try to find the employee in 'ektaktoi' table (anapl)
    $emp_query = "SELECT surname, name, afm, id, 'anapl' as type FROM ektaktoi WHERE afm = '$afm'";
    $emp_res = mysqli_query($mysqlconnection, $emp_query);
    if ($emp_res && mysqli_num_rows($emp_res) > 0) {
        $employee = mysqli_fetch_assoc($emp_res);
        $emp_id = intval($employee['id']);
        $type = 'anapl';
        $mon_anapl_db = 'Αναπληρωτής';
    } else {
        die("<h3>Σφάλμα: Ο εκπαιδευτικός δεν βρέθηκε.</h3>");
    }
}

$root_path = '../';
$page_title = "Υπηρετήσεις Εκπαιδευτικού (MySchool)";
?>
<!DOCTYPE html>
<html>

<head>
    <?php require '../etc/head.php'; ?>
    <LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="../js/datepicker-gr.js"></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript" src="../js/common.js"></script>

    <style>
        .yph-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border-top: 4px solid #2563eb;
        }

        .tab-button {
            padding: 10px 20px;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            color: #64748b;
            transition: all 0.2s;
            background: transparent;
            border-top: none;
            border-left: none;
            border-right: none;
            cursor: pointer;
        }

        .tab-button:hover {
            color: #1e293b;
            border-bottom-color: #cbd5e1;
        }

        .tab-button.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .yph-table th {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: white !important;
            font-weight: 600;
            padding: 12px 16px;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen text-slate-800">
    <?php include('../etc/menu.php'); ?>
    <div class="max-w-[90%] mx-auto px-4 py-8">

        <?php if ($_GET['msg'] == 'updated'): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded text-green-700 font-medium">
                Η υπηρεσία ενημερώθηκε με επιτυχία!
            </div>
        <?php elseif ($_GET['msg'] == 'deleted'): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded text-red-700 font-medium">
                Η υπηρεσία διαγράφηκε με επιτυχία!
            </div>
        <?php endif; ?>

        <?php
        if ($_GET['op'] == 'edit') {
            if ($usrlvl == 3) {
                die("<h3>Σφάλμα: Δεν επιτρέπεται η πρόσβαση...</h3>");
            }
            $record_id = intval($_GET['id']);
            $rec_res = mysqli_query($mysqlconnection, "SELECT y.*, s.name as school_name FROM yphrethsh_ext y LEFT JOIN school s ON y.sch_id = s.id WHERE y.id = $record_id");
            $record = mysqli_fetch_assoc($rec_res);
            if (!$record) {
                die("<h3>Σφάλμα: Η εγγραφή δεν βρέθηκε.</h3>");
            }
            ?>
            <div class="mb-6">
                <a href="yphrethseis.php?afm=<?php echo htmlspecialchars($employee['afm']); ?>"
                    class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800">
                    &larr; Επιστροφή στις υπηρετήσεις
                </a>
            </div>

            <div class="yph-card p-6 md:p-8 max-w-4xl mx-auto">
                <h2 class="text-xl font-bold text-slate-800 mb-2">Επεξεργασία Υπηρέτησης (MySchool)</h2>
                <p class="text-slate-500 text-sm mb-6">
                    Εκπαιδευτικός:
                    <strong><?php echo htmlspecialchars($employee['surname'] . ' ' . $employee['name']); ?></strong> (ΑΦΜ:
                    <?php echo htmlspecialchars($employee['afm']); ?>)
                </p>

                <?php if (isset($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded text-red-700 text-sm">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="yphrethseis.php" class="space-y-4">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="id" value="<?php echo $record['id']; ?>" />
                    <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>" />
                    <input type="hidden" name="type" value="<?php echo $type; ?>" />
                    <input type="hidden" name="afm" value="<?php echo htmlspecialchars($employee['afm']); ?>" />

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Σχολείο Υπηρέτησης</label>
                        <input type="text" name="school_name" id="school_name"
                            value="<?php echo htmlspecialchars($record['school_name'] ?: $record['sch_name']); ?>"
                            class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-blue-500"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Σχέση Εργασίας</label>
                        <input type="text" name="sxesh" value="<?php echo htmlspecialchars($record['sxesh']); ?>"
                            class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Σχέση Τοποθέτησης</label>
                        <input type="text" name="sxesh_topo" value="<?php echo htmlspecialchars($record['sxesh_topo']); ?>"
                            class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-blue-500" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Ημ. Έναρξης</label>
                            <?php modern_datepicker("date_from", $record['date_from']); ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Ημ. Λήξης</label>
                            <?php modern_datepicker("date_to", $record['date_to']); ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Ώρες</label>
                            <input type="number" name="hours" value="<?php echo htmlspecialchars($record['hours']); ?>"
                                class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-blue-500"
                                min="0" required />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Κατάσταση</label>
                            <?php
                            $current_state = '';
                            if ($record['state']) {
                                $current_state = mb_strtoupper(trim($record['state']), 'UTF-8');
                                $accents = array('Ά' => 'Α', 'Έ' => 'Ε', 'Ή' => 'Η', 'Ί' => 'Ι', 'Ό' => 'Ο', 'Ύ' => 'Υ', 'Ώ' => 'Ω');
                                $current_state = strtr($current_state, $accents);
                            }
                            ?>
                            <select name="state"
                                class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-blue-500">
                                <option value="">-</option>
                                <option value="ΠΑΡΟΥΣΙΑ" <?php if ($current_state === 'ΠΑΡΟΥΣΙΑ')
                                    echo 'selected'; ?>>ΠΑΡΟΥΣΙΑ
                                </option>
                                <option value="ΑΠΟΥΣΙΑ" <?php if ($current_state === 'ΑΠΟΥΣΙΑ')
                                    echo 'selected'; ?>>ΑΠΟΥΣΙΑ
                                </option>
                                <option value="ΠΑΡΗΛΘΕ" <?php if ($current_state === 'ΠΑΡΗΛΘΕ')
                                    echo 'selected'; ?>>ΠΑΡΗΛΘΕ
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Σχολικό Έτος</label>
                            <input type="text" name="sxol_etos"
                                value="<?php echo htmlspecialchars($record['sxol_etos']); ?>"
                                class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-blue-500"
                                required />
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                        <a href="yphrethseis.php?afm=<?php echo htmlspecialchars($employee['afm']); ?>"
                            class="px-4 py-2 border border-slate-300 rounded text-slate-700 hover:bg-slate-50 transition">
                            Ακύρωση
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow transition">
                            Αποθήκευση
                        </button>
                    </div>
                </form>
            </div>

            <script type="text/javascript">
                $(document).ready(function () {
                    $("#school_name").autocomplete("get_school.php", {
                        width: 300,
                        matchContains: true,
                        selectFirst: false
                    });
                });
            </script>
            <?php
        } else {
            // List view
            $query = "SELECT y.*, s.name as school_name 
                  FROM yphrethsh_ext y 
                  LEFT JOIN school s ON y.sch_id = s.id 
                  WHERE (y.afm = '$afm' OR y.emp_id = $emp_id) 
                    AND y.mon_anapl = '$mon_anapl_db'
                  ORDER BY y.sxol_etos DESC, y.date_from ASC";
            $result = mysqli_query($mysqlconnection, $query);

            $records_by_year = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $year = $row['sxol_etos'] ?: 'Άγνωστο';
                $records_by_year[$year][] = $row;
            }

            krsort($records_by_year);
            ?>
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <?php
                    $back_url = ($type == 'mon') ? "employee.php?id=$emp_id&op=view" : "ektaktoi.php?id=$emp_id&op=view";
                    ?>
                    <a href="<?php echo $back_url; ?>"
                        class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800">
                        &larr; Επιστροφή στην καρτέλα εκπαιδευτικού
                    </a>
                </div>
                <div>
                    <button onclick="window.close();" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded shadow text-sm transition">
                        Κλείσιμο
                    </button>
                </div>
            </div>

            <div class="yph-card p-6 md:p-8 mb-6">
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Υπηρετήσεις Εκπαιδευτικού</h1>
                        <p class="text-blue-600 font-semibold text-sm tracking-wide mt-1">Υπηρετήσεις από σύστημα MySchool
                        </p>
                    </div>
                    <div
                        class="mt-4 md:mt-0 text-slate-500 text-sm border-t md:border-t-0 md:border-l border-slate-200 pt-4 md:pt-0 md:pl-6">
                        <div>Όνομα: <strong
                                class="text-slate-800"><?php echo htmlspecialchars($employee['surname'] . ' ' . $employee['name']); ?></strong>
                        </div>
                        <div>ΑΦΜ: <strong class="text-slate-800"><?php echo htmlspecialchars($employee['afm']); ?></strong>
                        </div>
                        <div>Τύπος: <strong class="text-slate-800"><?php echo $mon_anapl_db; ?></strong></div>
                    </div>
                </div>

                <?php if (empty($records_by_year)): ?>
                    <div class="p-8 text-center bg-slate-50 rounded border border-dashed border-slate-300">
                        <p class="text-slate-600 font-medium">Δεν βρέθηκαν υπηρετήσεις από το σύστημα MySchool για τον
                            συγκεκριμένο εκπαιδευτικό.</p>
                    </div>
                <?php else: ?>
                    <!-- Tabs Navigation -->
                    <div class="flex border-b border-slate-200 mb-6 overflow-x-auto">
                        <?php
                        $first = true;
                        foreach ($records_by_year as $year => $rows):
                            $year_display = $year;
                            if (strlen($year) == 6 && is_numeric($year)) {
                                $year_display = substr($year, 0, 4) . '-' . substr($year, 4, 2);
                            }
                            $active_class = $first ? 'active' : '';
                            $first = false;
                            ?>
                            <button class="tab-button <?php echo $active_class; ?>"
                                onclick="openYearTab(event, 'year-<?php echo $year; ?>')">
                                <?php echo htmlspecialchars($year_display); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tabs Content -->
                    <div class="tabs-content">
                        <?php
                        $first = true;
                        foreach ($records_by_year as $year => $rows):
                            $active_class = $first ? 'active' : '';
                            $first = false;
                            ?>
                            <div id="year-<?php echo $year; ?>" class="tab-panel <?php echo $active_class; ?>">
                                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                                    <table class="min-w-full divide-y divide-slate-200 yph-table">
                                        <thead>
                                            <tr>
                                                <th scope="col"
                                                    class="text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    Σχολείο</th>
                                                <th scope="col"
                                                    class="text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    Σχέση Εργασίας</th>
                                                <th scope="col"
                                                    class="text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    Σχέση Τοποθέτησης</th>
                                                <th scope="col"
                                                    class="text-left text-xs font-semibold text-white uppercase tracking-wider">Ημ.
                                                    Έναρξης</th>
                                                <th scope="col"
                                                    class="text-left text-xs font-semibold text-white uppercase tracking-wider">Ημ.
                                                    Λήξης</th>
                                                <th scope="col"
                                                    class="text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    Ώρες</th>
                                                <th scope="col"
                                                    class="text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    Κατάσταση</th>
                                                <?php if ($usrlvl < 3): ?>
                                                    <th scope="col"
                                                        class="text-center text-xs font-semibold text-white uppercase tracking-wider w-24">
                                                        Ενέργειες</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-slate-100">
                                            <?php foreach ($rows as $row):
                                                $date_from_f = ($row['date_from'] && $row['date_from'] != '0000-00-00') ? date('d-m-Y', strtotime($row['date_from'])) : '-';
                                                $date_to_f = ($row['date_to'] && $row['date_to'] != '0000-00-00') ? date('d-m-Y', strtotime($row['date_to'])) : '-';
                                                ?>
                                                <tr class="hover:bg-slate-50/80 transition-colors">
                                                    <td class="px-6 py-4">
                                                        <div class="font-semibold text-slate-800">
                                                            <?php echo htmlspecialchars($row['school_name'] ?: ($row['sch_name'] ?: 'Άγνωστο')); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-400">Κωδικός:
                                                            <?php echo htmlspecialchars($row['sch_code'] ?: '-'); ?></div>
                                                    </td>
                                                    <td class="px-6 py-4 text-slate-600 text-sm">
                                                        <?php echo htmlspecialchars($row['sxesh'] ?: '-'); ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-slate-600 text-sm">
                                                        <?php echo htmlspecialchars($row['sxesh_topo'] ?: '-'); ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-slate-600 text-sm whitespace-nowrap">
                                                        <?php echo $date_from_f; ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-slate-600 text-sm whitespace-nowrap">
                                                        <?php echo $date_to_f; ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-center text-slate-800 font-semibold text-sm">
                                                        <?php echo htmlspecialchars($row['hours']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        <?php if ($row['state']):
                                                            $state_upper = mb_strtoupper(trim($row['state']), 'UTF-8');
                                                            // Normalize Greek accents for case-insensitive matching
                                                            $accents = array('Ά' => 'Α', 'Έ' => 'Ε', 'Ή' => 'Η', 'Ί' => 'Ι', 'Ό' => 'Ο', 'Ύ' => 'Υ', 'Ώ' => 'Ω');
                                                            $state_upper = strtr($state_upper, $accents);

                                                            $badge_color = 'bg-slate-100 text-slate-800 border-slate-200';
                                                            if ($state_upper === 'ΑΠΟΥΣΙΑ') {
                                                                $badge_color = 'bg-red-100 text-red-800 border-red-200';
                                                            } elseif ($state_upper === 'ΠΑΡΗΛΘΕ') {
                                                                $badge_color = 'bg-blue-100 text-blue-800 border-blue-200';
                                                            } elseif ($state_upper === 'ΠΑΡΟΥΣΙΑ') {
                                                                $badge_color = 'bg-green-100 text-green-800 border-green-200';
                                                            }
                                                            ?>
                                                            <span
                                                                class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full border <?php echo $badge_color; ?>">
                                                                <?php echo htmlspecialchars($row['state']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-slate-400">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($usrlvl < 3): ?>
                                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                                            <div class="flex items-center justify-center space-x-2">
                                                                <span title="Επεξεργασία">
                                                                    <a href="yphrethseis.php?op=edit&id=<?php echo $row['id']; ?>&afm=<?php echo htmlspecialchars($employee['afm']); ?>"
                                                                        class="hover:opacity-80 transition">
                                                                        <img style="border: 0pt none;" src="../images/edit_action.png"
                                                                            alt="Επεξεργασία" />
                                                                    </a>
                                                                </span>
                                                                <span title="Διαγραφή">
                                                                    <a href="javascript:confirmDelete('yphrethseis.php?op=delete&id=<?php echo $row['id']; ?>')"
                                                                        class="hover:opacity-80 transition">
                                                                        <img style="border: 0pt none;" src="../images/delete_action.png"
                                                                            alt="Διαγραφή" />
                                                                    </a>
                                                                </span>
                                                            </div>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <script type="text/javascript">
                        function openYearTab(evt, yearId) {
                            var i, tabpanel, tablinks;
                            tabpanel = document.getElementsByClassName("tab-panel");
                            for (i = 0; i < tabpanel.length; i++) {
                                tabpanel[i].className = tabpanel[i].className.replace(" active", "");
                            }
                            tablinks = document.getElementsByClassName("tab-button");
                            for (i = 0; i < tablinks.length; i++) {
                                tablinks[i].className = tablinks[i].className.replace(" active", "");
                            }
                            document.getElementById(yearId).className += " active";
                            evt.currentTarget.className += " active";
                        }
                    </script>
                <?php endif; ?>
            </div>
            <?php
        }
        ?>
    </div>
</body>

</html>
<?php
mysqli_close($mysqlconnection);
?>