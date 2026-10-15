<?php
header('Content-type: text/html; charset=utf-8');
require '../config.php';

require "../tools/class.login.php";
$log = new logmein();
if ($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit();
}

// check if super-user
if ($_SESSION['userlevel'] <> 0) {
    header("Location: ../index.php");
    exit();
}

require_once '../include/functions.php';
require_once '../include/functions_general.php';

// Initialize PDO connection
try {
    $db = new PDO(
        "mysql:host=$db_host;dbname=$db_name",
        $db_user,
        $db_password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        )
    );
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete action
    if (isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        $stmt = $db->prepare("DELETE FROM symvouloi WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['flash_message'] = "Η εγγραφή της ενότητας διαγράφηκε επιτυχώς.";
        $_SESSION['flash_type'] = "success";
        header("Location: symvouloi.php");
        exit();
    }

    // Save (Insert / Update) action
    if (isset($_POST['save_advisor'])) {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $perif = isset($_POST['perif']) ? (int)$_POST['perif'] : 0;
        $emp_id = !empty($_POST['emp_id']) ? (int)$_POST['emp_id'] : 0;

        if ($perif <= 0 || $emp_id <= 0) {
            $_SESSION['flash_message'] = "Παρακαλώ συμπληρώστε έγκυρο αριθμό ενότητας και επιλέξτε σύμβουλο.";
            $_SESSION['flash_type'] = "danger";
        } else {
            // Check for duplicate perif
            if ($id) {
                $stmt = $db->prepare("SELECT id FROM symvouloi WHERE perif = ? AND id != ?");
                $stmt->execute([$perif, $id]);
            } else {
                $stmt = $db->prepare("SELECT id FROM symvouloi WHERE perif = ?");
                $stmt->execute([$perif]);
            }

            if ($stmt->fetch()) {
                $_SESSION['flash_message'] = "Η ενότητα $perif είναι ήδη καταχωρημένη.";
                $_SESSION['flash_type'] = "danger";
            } else {
                if ($id) {
                    $stmt = $db->prepare("UPDATE symvouloi SET perif = ?, emp_id = ? WHERE id = ?");
                    $stmt->execute([$perif, $emp_id, $id]);
                    $_SESSION['flash_message'] = "Η ενότητα $perif ενημερώθηκε επιτυχώς.";
                    $_SESSION['flash_type'] = "success";
                } else {
                    $stmt = $db->prepare("INSERT INTO symvouloi (perif, emp_id) VALUES (?, ?)");
                    $stmt->execute([$perif, $emp_id]);
                    $_SESSION['flash_message'] = "Η ενότητα $perif προστέθηκε επιτυχώς.";
                    $_SESSION['flash_type'] = "success";
                }
                header("Location: symvouloi.php");
                exit();
            }
        }
    }
}

// Fetch all registered advisors from symvouloi
$sql = "SELECT s.id, s.perif, s.emp_id, e.surname, e.name, e.afm, k.perigrafh AS klados_name, k.onoma AS klados_onoma
        FROM symvouloi s
        LEFT JOIN employee e ON s.emp_id = e.id
        LEFT JOIN klados k ON e.klados = k.id
        ORDER BY s.perif ASC";
$symvouloi_list = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Fetch employees for Select2 dropdown (active employees plus any already assigned)
$emp_sql = "SELECT e.id, e.surname, e.name, e.afm, k.perigrafh AS klados_name
            FROM employee e
            LEFT JOIN klados k ON e.klados = k.id
            WHERE e.status IN (1, 3) OR e.id IN (SELECT emp_id FROM symvouloi)
            ORDER BY e.surname, e.name";
$employees = $db->query($emp_sql)->fetchAll(PDO::FETCH_ASSOC);

// Check if editing an existing entry
$editing_symvoulos = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM symvouloi WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editing_symvoulos = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="el" xml:lang="el">
<head>
    <?php
    $root_path = '../';
    $page_title = 'Σύμβουλοι Εκπαίδευσης - Ενότητες';
    require '../etc/head.php';
    ?>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link href="../css/select2.min.css" rel="stylesheet" />
    <script src="../js/jquery.js"></script>
    <script src="../js/select2.min.js"></script>
    <style>
        .container {
            max-width: 1000px;
            margin: 24px auto;
            background: #ffffff;
            padding: 28px 32px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .page-header h1 {
            margin: 0;
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 24px;
            margin-bottom: 14px;
        }
        .section-header h2 {
            margin: 0;
            color: #334155;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .card-form {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 30px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            align-items: flex-start;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 6px;
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
        }
        .form-input {
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.95rem;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
            width: 100%;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .form-hint {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 4px;
        }
        .form-actions {
            margin-top: 18px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 12px;
            background: #ffffff;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            color: #1e293b;
            padding-left: 0;
            font-size: 0.95rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 8px;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        .select2-results__option {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb;
        }
        .advisors-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 0.925rem;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .advisors-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #cbd5e1;
        }
        .advisors-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
        }
        .advisors-table tr:last-child td {
            border-bottom: none;
        }
        .advisors-table tr:hover td {
            background: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 600;
            text-align: center;
        }
        .badge-pe70 {
            background: #e0f2fe;
            color: #0369a1;
        }
        .badge-pe60 {
            background: #fce7f3;
            color: #be185d;
        }
        .badge-other {
            background: #f3f4f6;
            color: #4b5563;
        }
        .badge-perif {
            background: #eef2ff;
            color: #4338ca;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 4px 10px;
        }
        .actions-cell {
            white-space: nowrap;
            text-align: right;
        }
        .actions-cell .btn {
            padding: 5px 11px;
            font-size: 0.825rem;
            border-radius: 5px;
            margin-left: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-sm-edit {
            background: #f59e0b;
            color: #ffffff;
            border: none;
        }
        .btn-sm-edit:hover {
            background: #d97706;
        }
        .btn-sm-delete {
            background: #ef4444;
            color: #ffffff;
            border: none;
        }
        .btn-sm-delete:hover {
            background: #dc2626;
        }
        .btn-cancel {
            background: #94a3b8;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-cancel:hover {
            background: #64748b;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.925rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .empty-row {
            text-align: center;
            padding: 24px !important;
            color: #64748b;
            font-style: italic;
        }
        .emp-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .emp-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .container {
                margin: 12px;
                padding: 16px;
            }
        }
    </style>
</head>
<body>
<?php require '../etc/menu.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>Σύμβουλοι Εκπαίδευσης - Ενότητες</h1>
        <button type="button" class="btn btn-red" onclick="window.location.href='../index.php';" style="padding: 6px 14px; font-size: 0.875rem;">
            Επιστροφή
        </button>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_type'] ?? 'info'); ?>">
            <span><?php echo htmlspecialchars($_SESSION['flash_message']); ?></span>
            <button type="button" onclick="this.parentElement.style.display='none';" style="background:none;border:none;cursor:pointer;font-weight:bold;color:inherit;">&times;</button>
        </div>
        <?php 
        unset($_SESSION['flash_message']); 
        unset($_SESSION['flash_type']); 
        ?>
    <?php endif; ?>

    <!-- Form Section: Add or Edit -->
    <div class="card-form">
        <div class="section-header" style="margin-top: 0; margin-bottom: 16px;">
            <h2><?php echo $editing_symvoulos ? 'Επεξεργασία Ενότητας' : 'Προσθήκη Νέας Ενότητας'; ?></h2>
            <?php if ($editing_symvoulos): ?>
                <a href="symvouloi.php" class="btn-cancel" style="padding: 4px 10px; font-size: 0.8rem;">+ Νέα Καταχώρηση</a>
            <?php endif; ?>
        </div>

        <form method="POST" action="symvouloi.php">
            <input type="hidden" name="save_advisor" value="1">
            <?php if ($editing_symvoulos): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editing_symvoulos['id']); ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="perif">Ενότητα (Αριθμός): *</label>
                    <input type="number" id="perif" name="perif" class="form-input" min="1" max="999" required
                           value="<?php echo htmlspecialchars($editing_symvoulos['perif'] ?? ''); ?>"
                           placeholder="π.χ. 1 ή 601">
                    <span class="form-hint">1-9 για Δημοτικά, 601-603 για Νηπιαγωγεία</span>
                </div>

                <div class="form-group">
                    <label for="emp_id">Σύμβουλος Εκπαίδευσης: *</label>
                    <select id="emp_id" name="emp_id" required class="select2-emp">
                        <option value="">-- Επιλέξτε Σύμβουλο --</option>
                        <?php foreach ($employees as $emp): ?>
                            <?php
                            $selected = ($editing_symvoulos && $editing_symvoulos['emp_id'] == $emp['id']) ? 'selected' : '';
                            $klados_display = !empty($emp['klados_name']) ? $emp['klados_name'] : 'Χωρίς κλάδο';
                            $afm_display = !empty($emp['afm']) ? ' - ΑΦΜ: ' . $emp['afm'] : '';
                            $label = $emp['surname'] . ' ' . $emp['name'] . ' (' . $klados_display . $afm_display . ')';
                            ?>
                            <option value="<?php echo $emp['id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Αναζήτηση με επώνυμο, όνομα, κλάδο (π.χ. ΠΕ70, ΠΕ60) ή ΑΦΜ</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-green" style="padding: 9px 20px; font-weight: 600; font-size: 0.95rem; cursor: pointer;">
                    <?php echo $editing_symvoulos ? 'Ενημέρωση Ενότητας' : 'Προσθήκη Ενότητας'; ?>
                </button>
                <?php if ($editing_symvoulos): ?>
                    <a href="symvouloi.php" class="btn-cancel">Ακύρωση</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Table Section: List of Registered Advisors -->
    <div class="section-header">
        <h2>Καταχωρημένες Ενότητες &amp; Σύμβουλοι (<?php echo count($symvouloi_list); ?>)</h2>
    </div>

    <table class="advisors-table">
        <thead>
            <tr>
                <th style="width: 110px;">Ενότητα</th>
                <th>Ονοματεπώνυμο</th>
                <th style="width: 100px;">Κλάδος</th>
                <th style="width: 120px;">ΑΦΜ</th>
                <th style="width: 90px; text-align: center;">Α/Α Εκπ/κού</th>
                <th style="width: 160px; text-align: right;">Ενέργειες</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($symvouloi_list)): ?>
                <?php foreach ($symvouloi_list as $row): ?>
                    <?php
                    $is_kindergarten = ($row['perif'] >= 600);
                    $klados_badge_class = 'badge-other';
                    if (!empty($row['klados_name'])) {
                        if (strpos($row['klados_name'], '60') !== false) {
                            $klados_badge_class = 'badge-pe60';
                        } elseif (strpos($row['klados_name'], '70') !== false) {
                            $klados_badge_class = 'badge-pe70';
                        }
                    }
                    ?>
                    <tr>
                        <td>
                            <span class="badge badge-perif"><?php echo htmlspecialchars($row['perif']); ?>η</span>
                            <?php if ($is_kindergarten): ?>
                                <small style="display:block; color:#64748b; font-size:0.75rem; margin-top:2px;">(Νηπ/γών)</small>
                            <?php else: ?>
                                <small style="display:block; color:#64748b; font-size:0.75rem; margin-top:2px;">(Δημ/κών)</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['emp_id']) && !empty($row['surname'])): ?>
                                <a href="employee.php?id=<?php echo (int)$row['emp_id']; ?>&op=view" class="emp-link" title="Καρτέλα εκπαιδευτικού">
                                    <?php echo htmlspecialchars($row['surname'] . ' ' . $row['name']); ?>
                                </a>
                            <?php else: ?>
                                <span style="color:#ef4444; font-style:italic;">Δεν βρέθηκε εκπαιδευτικός (ID: <?php echo htmlspecialchars($row['emp_id']); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['klados_name'])): ?>
                                <span class="badge <?php echo $klados_badge_class; ?>">
                                    <?php echo htmlspecialchars($row['klados_name']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#94a3b8;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo !empty($row['afm']) ? htmlspecialchars($row['afm']) : '<span style="color:#94a3b8;">-</span>'; ?>
                        </td>
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                            <?php echo htmlspecialchars($row['emp_id']); ?>
                        </td>
                        <td class="actions-cell">
                            <a href="symvouloi.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm-edit">
                                Επεξεργασία
                            </a>
                            <form method="POST" action="symvouloi.php" style="display: inline-block; margin: 0;" onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε την ενότητα <?php echo $row['perif']; ?>;');">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm-delete">
                                    Διαγραφή
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="empty-row">Δεν υπάρχουν καταχωρημένες ενότητες συμβούλων.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#emp_id').select2({
        placeholder: "-- Επιλέξτε Σύμβουλο --",
        allowClear: true,
        width: '100%'
    });
});
</script>
</body>
</html>
