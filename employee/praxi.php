<?php
header('Content-type: text/html; charset=utf-8'); 
require_once "../config.php";
require_once "../include/functions.php";
require_once "../tools/class.login.php";

$log = new logmein();
if ($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit;
}

if ($_SESSION['userlevel'] == 3) {
    echo "Σφάλμα: Δεν επιτρέπεται η πρόσβαση...";
    echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
    die();
}

$can_delete = $_SESSION['userlevel'] < 2;
$can_edit = $_SESSION['userlevel'] <= 2;

// Database connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) {
    die("Σφάλμα σύνδεσης με τη βάση δεδομένων: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");

$action = $_GET['action'] ?? 'list';
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

// ==========================================
// Handle POST Actions
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';
    
    // --- CREATE NEW PRAXI ---
    if ($post_action === 'create') {
        if (!$can_edit) {
            header("Location: praxi.php?err=" . urlencode("Δεν έχετε δικαίωμα προσθήκης πράξης."));
            exit;
        }
        
        $name        = trim($_POST['name'] ?? '');
        $type        = trim($_POST['type'] ?? '');
        $ya          = trim($_POST['ya'] ?? '');
        $ada         = trim($_POST['ada'] ?? '');
        $apofasi     = trim($_POST['apofasi'] ?? '');
        $ada_apof    = trim($_POST['ada_apof'] ?? '');
        $sxolio      = trim($_POST['sxolio'] ?? '');

        if ($name === '') {
            header("Location: praxi.php?action=add&err=" . urlencode("Το όνομα της πράξης είναι υποχρεωτικό πεδίο."));
            exit;
        }

        $stmt = $mysqli->prepare("INSERT INTO praxi (name, ya, ada, apofasi, ada_apof, sxolio, type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssss", $name, $ya, $ada, $apofasi, $ada_apof, $sxolio, $type);
            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                $stmt->close();
                header("Location: praxi.php?action=view&id=$new_id&msg=" . urlencode("Η πράξη δημιουργήθηκε με επιτυχία!"));
                exit;
            } else {
                $error_msg = $stmt->error;
                $stmt->close();
                header("Location: praxi.php?action=add&err=" . urlencode("Σφάλμα κατά την αποθήκευση: " . $error_msg));
                exit;
            }
        } else {
            header("Location: praxi.php?action=add&err=" . urlencode("Σφάλμα προετοιμασίας ερωτήματος: " . $mysqli->error));
            exit;
        }
    }

    // --- UPDATE PRAXI ---
    elseif ($post_action === 'update') {
        if (!$can_edit) {
            header("Location: praxi.php?err=" . urlencode("Δεν έχετε δικαίωμα επεξεργασίας πράξης."));
            exit;
        }

        $id          = intval($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $type        = trim($_POST['type'] ?? '');
        $ya          = trim($_POST['ya'] ?? '');
        $ada         = trim($_POST['ada'] ?? '');
        $apofasi     = trim($_POST['apofasi'] ?? '');
        $ada_apof    = trim($_POST['ada_apof'] ?? '');
        $sxolio      = trim($_POST['sxolio'] ?? '');

        if ($id <= 0) {
            header("Location: praxi.php?err=" . urlencode("Μη έγκυρο αναγνωριστικό πράξης."));
            exit;
        }

        if ($name === '') {
            header("Location: praxi.php?action=edit&id=$id&err=" . urlencode("Το όνομα της πράξης είναι υποχρεωτικό πεδίο."));
            exit;
        }

        $stmt = $mysqli->prepare("UPDATE praxi SET name=?, ya=?, ada=?, apofasi=?, ada_apof=?, sxolio=?, type=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sssssssi", $name, $ya, $ada, $apofasi, $ada_apof, $sxolio, $type, $id);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: praxi.php?action=view&id=$id&msg=" . urlencode("Η πράξη ενημερώθηκε με επιτυχία!"));
                exit;
            } else {
                $error_msg = $stmt->error;
                $stmt->close();
                header("Location: praxi.php?action=edit&id=$id&err=" . urlencode("Σφάλμα κατά την ενημέρωση: " . $error_msg));
                exit;
            }
        } else {
            header("Location: praxi.php?action=edit&id=$id&err=" . urlencode("Σφάλμα προετοιμασίας ερωτήματος: " . $mysqli->error));
            exit;
        }
    }

    // --- DELETE PRAXI ---
    elseif ($post_action === 'delete') {
        if (!$can_delete) {
            header("Location: praxi.php?err=" . urlencode("Μόνο προϊστάμενος ή διαχειριστής έχει δικαίωμα διαγραφής πράξης."));
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header("Location: praxi.php?err=" . urlencode("Μη έγκυρο αναγνωριστικό πράξης."));
            exit;
        }

        if ($id === 1) {
            header("Location: praxi.php?err=" . urlencode("Η πρώτη εγγραφή (Καμία) αποτελεί προεπιλογή του συστήματος και δεν μπορεί να διαγραφεί!"));
            exit;
        }

        // Check if substitute teachers are assigned to this praxi
        $check_stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM ektaktoi WHERE praxi = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $cnt_res = $check_stmt->get_result()->fetch_assoc();
        $total_teachers = intval($cnt_res['total'] ?? 0);
        $check_stmt->close();

        if ($total_teachers > 0) {
            header("Location: praxi.php?action=view&id=$id&err=" . urlencode("Η πράξη δεν μπορεί να διαγραφεί διότι είναι ανατεθειμένη σε $total_teachers εκπαιδευτικούς! Πρέπει πρώτα να αλλάξετε την πράξη τους."));
            exit;
        }

        $del_stmt = $mysqli->prepare("DELETE FROM praxi WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        if ($del_stmt->execute()) {
            $del_stmt->close();
            header("Location: praxi.php?msg=" . urlencode("Η πράξη διαγράφηκε με επιτυχία!"));
            exit;
        } else {
            $error_msg = $del_stmt->error;
            $del_stmt->close();
            header("Location: praxi.php?err=" . urlencode("Σφάλμα κατά τη διαγραφή: " . $error_msg));
            exit;
        }
    }
}

// Fetch single record for edit or view
$current_praxi = null;
if (in_array($action, ['edit', 'view'])) {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $mysqli->prepare("SELECT * FROM praxi WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $current_praxi = $res->fetch_assoc();
        $stmt->close();
    }
    if (!$current_praxi) {
        header("Location: praxi.php?err=" . urlencode("Η πράξη δεν βρέθηκε."));
        exit;
    }
}

// Fallback/definition for allowed praxi types
if (!isset($anapl_praxeis) || empty($anapl_praxeis)) {
    $anapl_praxeis = [
        'NULL' => 'Χωρίς τύπο',
        'ΚΡΑΤ' => 'Κρατικού',
        'ΕΣΠΑ' => 'ΕΣΠΑ'
    ];
}

// Helper to get human label for praxi type
function get_praxi_type_label($type_key, $anapl_praxeis) {
    if (empty($type_key) || $type_key === 'NULL') {
        return 'Χωρίς τύπο';
    }
    return $anapl_praxeis[$type_key] ?? $type_key;
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <?php 
    $root_path = '../';
    $page_title = 'Διαχείριση Πράξεων';
    require '../etc/head.php'; 
    ?>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <?php require_once('../js/datatables/includes.html'); ?>

    <style>
        .praxi-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            font-family: inherit;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .action-bar-left, .action-bar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-teachers {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .badge-teachers:hover {
            background-color: #10b981;
            color: #ffffff;
        }

        .diavgeia-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            font-family: monospace;
            font-size: 0.85rem;
            padding: 2px 6px;
            border-radius: 4px;
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            transition: all 0.15s;
        }
        .diavgeia-link:hover {
            color: #1d4ed8;
            background-color: #dbeafe;
            text-decoration: underline;
        }

        /* Form Card */
        .crud-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .crud-card-header {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .crud-card-header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
        }

        .crud-card-body {
            padding: 24px;
        }

        .crud-table {
            width: 100%;
            border-collapse: collapse;
        }

        .crud-table th, .crud-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .crud-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-align: left;
        }

        .crud-table tr:hover {
            background-color: #f8fafc;
        }

        /* Detail view list styling */
        .detail-row {
            display: flex;
            border-bottom: 1px solid #f1f5f9;
            padding: 12px 16px;
        }
        .detail-row:nth-child(even) {
            background-color: #f8fafc;
        }
        .detail-label {
            width: 240px;
            font-weight: 600;
            color: #334155;
            flex-shrink: 0;
        }
        .detail-value {
            color: #0f172a;
            flex-grow: 1;
            word-break: break-word;
        }

        .text-block-preview {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 0.9rem;
            white-space: pre-wrap;
            color: #1e293b;
            max-height: 250px;
            overflow-y: auto;
        }

        /* Form elements */
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }
        .form-label .required {
            color: #ef4444;
        }
        .form-control {
            width: 100%;
            box-sizing: border-box;
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.2s;
            color: #0f172a;
            background-color: #ffffff;
        }
        .form-control:focus {
            border-color: #0284c7;
            outline: none;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
        }
        .form-help {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 4px;
        }

        /* Grid for 2-column form */
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
            .detail-row {
                flex-direction: column;
                gap: 4px;
            }
            .detail-label {
                width: 100%;
            }
        }

        /* Alert notifications */
        .alert-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .alert-success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Actions buttons inside table */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 5px 9px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
        }
        .btn-action-view {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .btn-action-view:hover {
            background-color: #bae6fd;
            color: #0284c7;
        }
        .btn-action-edit {
            background-color: #fef3c7;
            color: #b45309;
        }
        .btn-action-edit:hover {
            background-color: #fde68a;
            color: #92400e;
        }
        .btn-action-del {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .btn-action-del:hover {
            background-color: #fecaca;
            color: #991b1b;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
<?php require '../etc/menu.php'; ?>

<div class="praxi-container">

    <!-- Flash Messages -->
    <?php if ($msg): ?>
        <div class="alert-box alert-success">
            <span style="font-size: 1.25rem;">✅</span>
            <span><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($err): ?>
        <div class="alert-box alert-error">
            <span style="font-size: 1.25rem;">⚠️</span>
            <span><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

    <!-- ======================================================= -->
    <!-- VIEW: ADD NEW / EDIT FORM -->
    <!-- ======================================================= -->
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <?php 
        $is_edit = ($action === 'edit' && $current_praxi);
        $card_title = $is_edit 
            ? 'Επεξεργασία Πράξης: ' . htmlspecialchars($current_praxi['name'] ?? '', ENT_QUOTES, 'UTF-8') 
            : '➕ Προσθήκη Νέας Πράξης';
        ?>

        <div class="action-bar">
            <div class="action-bar-left">
                <a href="praxi.php" class="btn btn-sm btn-yellow">↩️ Επιστροφή στη Λίστα</a>
                <?php if ($is_edit): ?>
                    <a href="praxi.php?action=view&id=<?php echo $current_praxi['id']; ?>" class="btn btn-sm btn-primary">👁️ Προβολή Πράξης</a>
                <?php endif; ?>
            </div>
            <div class="action-bar-right">
                <span class="text-sm text-gray-500">
                    <?php echo $is_edit ? 'Αναγνωριστικό (ID): #'.$current_praxi['id'] : 'Νέα Εγγραφή'; ?>
                </span>
            </div>
        </div>

        <div class="crud-card">
            <div class="crud-card-header">
                <h2><?php echo $card_title; ?></h2>
            </div>
            <div class="crud-card-body">
                <form method="POST" action="praxi.php">
                    <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $current_praxi['id']; ?>">
                    <?php endif; ?>

                    <!-- Row 1: Basic Info -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">
                                Όνομα Πράξης <span class="required">*</span>
                            </label>
                            <input type="text" name="name" class="form-control" required
                                   placeholder="π.χ. Ολοήμερο Β', Παράλληλη Γ', κλπ."
                                   value="<?php echo htmlspecialchars($is_edit ? $current_praxi['name'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-help">Να είναι περιγραφικό & σύντομο.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Τύπος Πράξης</label>
                            <select name="type" class="form-control">
                                <?php 
                                $selected_type = $is_edit ? ($current_praxi['type'] ?? '') : '';
                                foreach ($anapl_praxeis as $t_key => $t_label): 
                                    $val = ($t_key === 'NULL') ? '' : $t_key;
                                    $is_sel = ($selected_type === $t_key || ($t_key === 'NULL' && ($selected_type === '' || $selected_type === 'NULL'))) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $is_sel; ?>>
                                        <?php echo htmlspecialchars($t_label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-help">Κατηγοριοποίηση της πράξης (Χωρίς τύπο, Κρατικού, ΕΣΠΑ).</div>
                        </div>
                    </div>

                    <!-- Row 2: Decisions & Diavgeia -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Υπουργική Απόφαση (Υ.Α.)</label>
                            <input type="text" name="ya" class="form-control"
                                   placeholder="π.χ. 113872/Ε1/02-09-2026"
                                   value="<?php echo htmlspecialchars($is_edit ? $current_praxi['ya'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-help">Αριθμός και ημερομηνία της Υπουργικής Απόφασης.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Α.Δ.Α. Υ.Α.</label>
                            <input type="text" name="ada" class="form-control"
                                   placeholder="π.χ. 6ΠΜΦ4653ΠΣ-4ΝΠ"
                                   value="<?php echo htmlspecialchars($is_edit ? $current_praxi['ada'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-help">Να αναγράφεται μόνο ο κωδικός ΑΔΑ της Υ.Α. για άμεσο σύνδεσμο με τη Διαύγεια.</div>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Απόφαση Διευθυντή (Τοποθέτησης)</label>
                            <input type="text" name="apofasi" class="form-control"
                                   placeholder="π.χ. 14131/04-09-2026"
                                   value="<?php echo htmlspecialchars($is_edit ? $current_praxi['apofasi'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-help">Αριθμός & ημερομηνία απόφασης τοποθέτησης Δ/ντή Εκπαίδευσης.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Α.Δ.Α. Απόφασης Διευθυντή</label>
                            <input type="text" name="ada_apof" class="form-control"
                                   placeholder="π.χ. Ψ00Ψ46ΝΚΠΔ-0ΕΧ"
                                   value="<?php echo htmlspecialchars($is_edit ? $current_praxi['ada_apof'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-help">Μόνο ο ΑΔΑ της απόφασης τοποθέτησης.</div>
                        </div>
                    </div>

                    <!-- Row 3: Comments -->
                    <div class="form-group">
                        <label class="form-label">Σχόλια / Παρατηρήσεις</label>
                        <textarea name="sxolio" class="form-control" rows="3"
                                  placeholder="Επιπλέον παρατηρήσεις..."><?php echo htmlspecialchars($is_edit ? ($current_praxi['sxolio'] ?? '') : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div style="margin-top: 24px; display: flex; gap: 12px; align-items: center;">
                        <button type="submit" class="btn btn-green">
                            💾 <?php echo $is_edit ? 'Ενημέρωση Πράξης' : 'Αποθήκευση Πράξης'; ?>
                        </button>
                        <a href="praxi.php" class="btn btn-yellow">Ακύρωση</a>
                    </div>
                </form>
            </div>
        </div>

    <!-- ======================================================= -->
    <!-- VIEW: DETAILS (ΚΑΡΤΕΛΑ ΠΡΟΒΟΛΗΣ) -->
    <!-- ======================================================= -->
    <?php elseif ($action === 'view' && $current_praxi): ?>
        <?php
        $id = $current_praxi['id'];

        // Fetch count of assigned substitute teachers
        $teacher_count = 0;
        $cnt_stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM ektaktoi WHERE praxi = ?");
        if ($cnt_stmt) {
            $cnt_stmt->bind_param("i", $id);
            $cnt_stmt->execute();
            $cnt_res = $cnt_stmt->get_result()->fetch_assoc();
            $teacher_count = intval($cnt_res['total'] ?? 0);
            $cnt_stmt->close();
        }
        ?>

        <div class="action-bar">
            <div class="action-bar-left">
                <a href="praxi.php" class="btn btn-sm btn-yellow">↩️ Όλες οι Πράξεις</a>
                <?php if ($can_edit): ?>
                    <a href="praxi.php?action=edit&id=<?php echo $id; ?>" class="btn btn-sm btn-primary">✏️ Επεξεργασία</a>
                <?php endif; ?>
                <?php if ($can_delete && $id > 1): ?>
                    <form method="POST" action="praxi.php" style="display:inline;" onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε την πράξη «<?php echo addslashes($current_praxi['name']); ?>»;');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" class="btn btn-sm btn-red">🗑️ Διαγραφή</button>
                    </form>
                <?php endif; ?>
                <a href="praxi.php?action=add" class="btn btn-sm btn-green">➕ Νέα Πράξη</a>
            </div>
            <div class="action-bar-right">
                <a href="ektaktoi_list.php?praxi=<?php echo $id; ?>" class="badge-teachers" style="font-size: 0.9rem; padding: 6px 14px;">
                    👥 Αναπληρωτές στην πράξη: <?php echo $teacher_count; ?>
                </a>
            </div>
        </div>

        <div class="crud-card">
            <div class="crud-card-header">
                <h2>👁️ Καρτέλα Πράξης: #<?php echo $id; ?> - <?php echo htmlspecialchars($current_praxi['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
            <div class="crud-card-body" style="padding: 0;">
                <div class="detail-row">
                    <div class="detail-label">Α/Α (ID):</div>
                    <div class="detail-value font-mono font-bold text-gray-600">#<?php echo $id; ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Όνομα Πράξης:</div>
                    <div class="detail-value font-bold text-lg text-blue-900">
                        <?php echo htmlspecialchars($current_praxi['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Τύπος Πράξης:</div>
                    <div class="detail-value">
                        <span class="badge-type">
                            <?php echo htmlspecialchars(get_praxi_type_label($current_praxi['type'], $anapl_praxeis), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <?php if (!empty($current_praxi['type']) && $current_praxi['type'] !== 'NULL' && $current_praxi['type'] !== get_praxi_type_label($current_praxi['type'], $anapl_praxeis)): ?>
                            <span class="text-xs text-gray-500 ml-2 font-mono">(Κωδικός: <?php echo htmlspecialchars($current_praxi['type'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Υπουργική Απόφαση (Υ.Α.):</div>
                    <div class="detail-value">
                        <?php if (!empty($current_praxi['ya'])): ?>
                            <strong><?php echo htmlspecialchars($current_praxi['ya'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($current_praxi['ada'])): ?>
                            <span class="ml-3">
                                <span class="text-gray-500 text-sm">Α.Δ.Α.:</span> 
                                <a href="https://diavgeia.gov.gr/decision/view/<?php echo urlencode($current_praxi['ada']); ?>" 
                                   target="_blank" class="diavgeia-link" title="Προβολή στη Διαύγεια">
                                    <?php echo htmlspecialchars($current_praxi['ada'], ENT_QUOTES, 'UTF-8'); ?> ↗
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Απόφαση Διευθυντή (Τοποθέτησης):</div>
                    <div class="detail-value">
                        <?php if (!empty($current_praxi['apofasi'])): ?>
                            <strong><?php echo htmlspecialchars($current_praxi['apofasi'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>

                        <?php if (!empty($current_praxi['ada_apof'])): ?>
                            <span class="ml-3">
                                <span class="text-gray-500 text-sm">Α.Δ.Α.:</span> 
                                <a href="https://diavgeia.gov.gr/decision/view/<?php echo urlencode($current_praxi['ada_apof']); ?>" 
                                   target="_blank" class="diavgeia-link" title="Προβολή στη Διαύγεια">
                                    <?php echo htmlspecialchars($current_praxi['ada_apof'], ENT_QUOTES, 'UTF-8'); ?> ↗
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>



                <div class="detail-row">
                    <div class="detail-label">Σχόλια:</div>
                    <div class="detail-value">
                        <?php if (!empty($current_praxi['sxolio'])): ?>
                            <?php echo nl2br(htmlspecialchars($current_praxi['sxolio'], ENT_QUOTES, 'UTF-8')); ?>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <!-- ======================================================= -->
    <!-- VIEW: LIST (ΠΙΝΑΚΑΣ / ΛΙΣΤΑ ΕΓΓΡΑΦΩΝ) -->
    <!-- ======================================================= -->
    <?php else: ?>
        <?php
        // Fetch all praxeis along with teacher count per praxi
        $praxeis_query = "
            SELECT p.*, COUNT(e.id) as teacher_count
            FROM praxi p
            LEFT JOIN ektaktoi e ON e.praxi = p.id
            GROUP BY p.id
            ORDER BY p.id ASC
        ";
        $praxeis_result = $mysqli->query($praxeis_query);
        $praxeis = [];
        if ($praxeis_result) {
            while ($row = $praxeis_result->fetch_assoc()) {
                $praxeis[] = $row;
            }
        }
        ?>

        <!-- Action Header Bar -->
        <div class="action-bar">
            <div class="action-bar-left">
                <h1 style="font-size: 1.4rem; font-weight: 700; margin: 0; color: #0f172a;">
                    📋 Διαχείριση Πράξεων
                </h1>
                <span class="text-sm text-gray-500 font-medium">
                    (Σύνολο: <?php echo count($praxeis); ?> εγγραφές)
                </span>
            </div>
            <div class="action-bar-right">
                <?php if ($can_edit): ?>
                    <a href="praxi.php?action=add" class="btn btn-green">➕ Νέα Πράξη</a>
                <?php endif; ?>
                <a href="../tools/assign_praxi.php" class="btn btn-primary">📋 Ανάθεση Πράξεων</a>
                <a href="praxi_sch.php" class="btn btn-yellow">📊 Εκπ/κοί & Σχολεία ανά Πράξη</a>
                <a href="ektaktoi_list.php" class="btn btn-red">↩️ Επιστροφή</a>
            </div>
        </div>

        <!-- DataTable Container -->
        <div class="crud-card">
            <div class="crud-card-body" style="padding: 16px;">
                <table id="praxiTable" class="display responsive nowrap w-full cell-border stripe hover">
                    <thead>
                        <tr>
                            <th style="width: 45px;">Α/Α</th>
                            <th>Όνομα Πράξης</th>
                            <th style="width: 100px;">Τύπος</th>
                            <th>Υ.Α.</th>
                            <th>Απόφαση Δ/ντη</th>
                            <th style="width: 70px;">Εκπ/κοί</th>
                            <th style="width: 140px; text-align: center;">Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($praxeis as $p): ?>
                            <?php 
                            $p_id = $p['id'];
                            $t_count = intval($p['teacher_count'] ?? 0);
                            ?>
                            <tr>
                                <td class="font-mono text-center font-bold text-gray-600">
                                    <?php echo $p_id; ?>
                                </td>
                                <td>
                                    <a href="praxi.php?action=view&id=<?php echo $p_id; ?>" class="font-bold text-blue-700 hover:underline">
                                        <?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php 
                                    $t_label = get_praxi_type_label($p['type'] ?? '', $anapl_praxeis);
                                    if (($p['type'] ?? '') === 'ΚΡΑΤ'):
                                    ?>
                                        <span style="display:inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; background: #ecfdf5; color: #065f46; font-weight: 600; border: 1px solid #a7f3d0;">
                                            <?php echo htmlspecialchars($t_label, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php elseif (($p['type'] ?? '') === 'ΕΣΠΑ'): ?>
                                        <span style="display:inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; background: #eff6ff; color: #1d4ed8; font-weight: 600; border: 1px solid #bfdbfe;">
                                            <?php echo htmlspecialchars($t_label, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php elseif (!empty($p['type']) && $p['type'] !== 'NULL'): ?>
                                        <span style="display:inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; background: #f1f5f9; color: #475569; font-weight: 600; border: 1px solid #cbd5e1;">
                                            <?php echo htmlspecialchars($t_label, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">Χωρίς τύπο</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($p['ya'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($p['apofasi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($t_count > 0): ?>
                                        <a href="ektaktoi_list.php?praxi=<?php echo $p_id; ?>" class="badge-teachers" title="Προβολή αναπληρωτών">
                                            👥 <?php echo $t_count; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">0</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <!-- View Button -->
                                    <a href="praxi.php?action=view&id=<?php echo $p_id; ?>" class="btn-action btn-action-view" title="Προβολή">
                                        👁️
                                    </a>

                                    <!-- Edit Button -->
                                    <?php if ($can_edit): ?>
                                        <a href="praxi.php?action=edit&id=<?php echo $p_id; ?>" class="btn-action btn-action-edit" title="Επεξεργασία">
                                            ✏️
                                        </a>
                                    <?php endif; ?>

                                    <!-- Delete Button -->
                                    <?php if ($can_delete): ?>
                                        <?php if ($p_id == 1): ?>
                                            <button class="btn-action" style="opacity: 0.35; cursor: not-allowed;" title="Η πρώτη εγγραφή (Καμία) δεν διαγράφεται" disabled>
                                                🔒
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" action="praxi.php" style="display:inline;" onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε την πράξη «<?php echo addslashes($p['name']); ?>»;');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $p_id; ?>">
                                                <button type="submit" class="btn-action btn-action-del" title="Διαγραφή">
                                                    🗑️
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DataTable Initialization -->
        <script type="text/javascript">
            $(document).ready(function() {
                $('#praxiTable').DataTable({
                    language: {
                        url: '../js/datatables/greek.json'
                    },
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Όλες"]],
                    order: [[0, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: [6] } // Disable sorting on actions column
                    ]
                });
            });
        </script>

        <!-- Explanatory Information & Legend (as in original praxi.php) -->
        <div class="crud-card" style="margin-top: 24px;">
            <div class="crud-card-header" style="background: linear-gradient(135deg, #475569 0%, #334155 100%);">
                <h2>ℹ️ Επεξήγηση Πεδίων & Οδηγίες</h2>
            </div>
            <div class="crud-card-body">
                <table class="imagetable stable" style="width: 100%; border: 1px solid #cbd5e1;">
                    <thead>
                        <tr>
                            <th style="width: 250px;">Πεδίο</th>
                            <th>Περιγραφή</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Όνομα πράξης</strong></td>
                            <td>Να είναι περιγραφικό & σύντομο π.χ. Ολοήμερο Β', Παράλληλη Γ' κλπ.</td>
                        </tr>
                        <tr>
                            <td><strong>Τύπος πράξης</strong></td>
                            <td>Κατηγορία πράξης αναπληρωτών (Χωρίς τύπο, Κρατικού, ΕΣΠΑ).</td>
                        </tr>
                        <tr>
                            <td><strong>Υπουργική Απόφαση</strong></td>
                            <td>Αριθμός και ημερομηνία της Υ.Α. πρόσληψης.</td>
                        </tr>
                        <tr>
                            <td><strong>Α.Δ.Α. Υ.Α.</strong></td>
                            <td>Να αναγράφεται μόνο ο ΑΔΑ της Υ.Α. (π.χ. 6ΠΜΦ4653ΠΣ-4ΝΠ) για αυτόματη σύνδεση με το portal της Διαύγειας.</td>
                        </tr>
                        <tr>
                            <td><strong>Απόφαση Δ/ντη</strong></td>
                            <td>Απόφαση τοποθέτησης από τον Διευθυντή Π.Ε.</td>
                        </tr>
                        <tr>
                            <td><strong>Α.Δ.Α. Απόφασης</strong></td>
                            <td>Μόνο ο ΑΔΑ της απόφασης τοποθέτησης.</td>
                        </tr>

                    </tbody>
                </table>
                <div style="margin-top: 14px; padding: 10px 14px; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; color: #991b1b; font-size: 0.9rem;">
                    <strong>⚠️ ΣΗΜΕΙΩΣΗ:</strong> Η πρώτη γραμμή (ID=1 «Καμία») δεν επιτρέπεται να διαγραφεί, καθώς λειτουργεί ως προεπιλεγμένη τιμή σε αναπληρωτές χωρίς πράξη. Διαγραφή πράξης μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή και εφόσον δεν υπάρχουν συνδεδεμένοι αναπληρωτές.
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
<?php
$mysqli->close();
?>
