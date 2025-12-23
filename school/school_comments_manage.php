<?php
header('Content-type: text/html; charset=utf-8'); 
require_once "../config.php";
require_once "../include/functions.php";
require "../tools/class.login.php";

$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit;
}
$can_view_comments = ($_SESSION['userlevel'] == 0 || ($_SESSION['user'] ?? '') === 'gram-pispe');
if (!$can_view_comments) {
    die('Σφάλμα: Δεν έχετε τα απαραίτητα δικαιώματα για αυτή τη σελίδα...');
}
if (!isset($_GET['sch'])) {
    die('Σφάλμα: Δε δόθηκε Α/Α/ σχολείου.');
}
$sch = intval($_GET['sch']);
$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
$school_name = getSchool($sch, $mysqlconnection);

$school_name = getSchool($sch, $mysqlconnection);

// Get current user ID for default
$session_user = $_SESSION['user'];
$q = "SELECT userid FROM logon WHERE username = '$session_user'";
$r = mysqli_query($mysqlconnection, $q);
$userid = 0;
if ($row = mysqli_fetch_assoc($r)) {
    $userid = $row['userid'];
}

$add_mode = isset($_GET['add']);
$edit_mode = isset($_GET['edit']);
$delete_mode = isset($_GET['delete']);
$mode = $add_mode ? 'add' : ($edit_mode ? 'edit' : ($delete_mode ? 'delete' : null));
$comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$form_data = ['comment' => '', 'action' => '', 'done' => 0, 'done_at' => ''];

if ($mode) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $comment = trim($_POST['comment'] ?? '');
        $action = trim($_POST['action'] ?? '');
        $done = isset($_POST['done']) ? 1 : 0;
        $done_at = trim($_POST['done_at'] ?? '');

        if ($mode === 'add') {
            if (!empty($comment)) {
                $insert_sql = "INSERT INTO school_comments (school_id, comment, action, done, done_at, added_by, added_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmt = mysqli_prepare($mysqlconnection, $insert_sql);
                mysqli_stmt_bind_param($stmt, "issssi", $sch, $comment, $action, $done, $done_at, $userid);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: school_status.php?org=$sch");
                    exit;
                }
            }
        } elseif ($mode === 'edit' && $comment_id > 0) {
            // Verify comment belongs to school
            $check_sql = "SELECT id FROM school_comments WHERE id = ? AND school_id = ?";
            $check_stmt = mysqli_prepare($mysqlconnection, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "ii", $comment_id, $sch);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            if (mysqli_num_rows($check_result) > 0) {
                if (!empty($comment)) {
                    $update_sql = "UPDATE school_comments SET comment = ?, action = ?, done = ?, done_at = ? WHERE id = ?";
                    $stmt = mysqli_prepare($mysqlconnection, $update_sql);
                    mysqli_stmt_bind_param($stmt, "ssssi", $comment, $action, $done, $done_at, $comment_id);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header("Location: school_status.php?org=$sch");
                        exit;
                    }
                }
            }
            mysqli_stmt_close($check_stmt);
        }
    } elseif ($mode === 'delete' && $comment_id > 0) {
        // Verify comment belongs to school
        $check_sql = "SELECT id FROM school_comments WHERE id = ? AND school_id = ?";
        $check_stmt = mysqli_prepare($mysqlconnection, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ii", $comment_id, $sch);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        if (mysqli_num_rows($check_result) > 0) {
            $delete_sql = "DELETE FROM school_comments WHERE id = ?";
            $stmt = mysqli_prepare($mysqlconnection, $delete_sql);
            mysqli_stmt_bind_param($stmt, "i", $comment_id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: school_comments_manage.php?sch=$sch");
                exit;
            }
        }
        mysqli_stmt_close($check_stmt);
    }

    if ($mode === 'edit' && $comment_id > 0) {
        $select_sql = "SELECT comment, action, done, done_at FROM school_comments WHERE id = ? AND school_id = ?";
        $stmt = mysqli_prepare($mysqlconnection, $select_sql);
        mysqli_stmt_bind_param($stmt, "ii", $comment_id, $sch);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $form_data = [
                'comment' => $row['comment'],
                'action' => $row['action'],
                'done' => (int)$row['done'],
                'done_at' => $row['done_at']
            ];
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // Fetch comments for listing
    $comments_sql = "SELECT sc.*, l.username AS added_by_username FROM school_comments sc LEFT JOIN logon l ON sc.added_by = l.userid WHERE sc.school_id = $sch ORDER BY sc.added_at DESC, sc.id DESC";
    $comments_rs = mysqli_query($mysqlconnection, $comments_sql);
}

?>

<!DOCTYPE html>
<html>
<head>
    <?php
    $root_path = '../';
    $page_title = 'Διαχείριση Σχολίων - ' . $school_name;
    require '../etc/head.php';
    ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="../js/datatables/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="../js/datatables/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="../js/datatables/fixedheader/fixedHeader.dataTables.css">
    <style>
        /* Reuse styles from school_status.php */
        .info-section {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        .info-section:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }
        .info-section-header {
            background: linear-gradient(135deg, #4f8188 0%, #30b7cb 100%);
            color: #ffffff;
            padding: 16px 24px;
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        .info-section-content {
            padding: 24px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1rem;
            color: #111827;
            font-weight: 500;
            word-break: break-word;
        }
        .action-buttons {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .action-buttons input[type="button"] {
            padding: 10px 24px;
            background: #667eea;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .action-buttons input[type="button"]:hover {
            background: #764ba2;
            transform: translateY(-1px);
        }
        #comments-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            font-size: 0.95rem;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }
        #comments-table th,
        #comments-table td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.7);
            text-align: left;
        }
        #comments-table thead {
            background: linear-gradient(135deg,rgb(113, 153, 209),rgb(89, 105, 140));
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        #comments-table thead th {
            color: #ffffff;
            font-size: 0.9rem;
            text-shadow: 0 2px 6px rgba(15, 23, 42, 0.45);
        }
        #comments-table tbody tr:nth-child(odd) {
            background: #f8fafc;
        }
        #comments-table tbody tr:last-child td {
            border-bottom: none;
        }
        .done-yes {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        .done-no {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 16px;
            align-items: start;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            color: #374151;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-group input[type="checkbox"] {
            width: auto;
        }
        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }
        .form-actions input[type="submit"],
        .form-actions input[type="button"] {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .form-actions input[type="submit"] {
            background: #10b981;
            color: white;
        }
        .form-actions input[type="submit"]:hover {
            background: #059669;
        }
        .form-actions input[type="button"] {
            background: #6b7280;
            color: white;
        }
        .form-actions input[type="button"]:hover {
            background: #4b5563;
        }
        .btn-red {
            background: #dc2626;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .btn-red:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        .top, .bottom {
            padding: 0px !important;
            /* margin-top: 5px; */
            /* padding-top: 30px; */
        }
    </style>
</head>
<body>
<?php require '../etc/menu.php'; ?>
<div style="margin: 0 auto; padding: 20px;">
<?php if ($mode): ?>
    <h2><?php echo $mode === 'add' ? 'Προσθήκη' : 'Επεξεργασία'; ?> Σχολίου για: <?php echo htmlspecialchars($school_name); ?></h2>
    <form method="POST" action="">
        <input type="hidden" name="sch" value="<?php echo $sch; ?>">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $comment_id; ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="comment">Σχόλιο *</label>
                <textarea name="comment" id="comment" required><?php echo htmlspecialchars($form_data['comment']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="action">Ενέργεια</label>
                <input type="text" name="action" id="action" value="<?php echo htmlspecialchars($form_data['action']); ?>">
            </div>
            <div class="form-group">
                <label for="done">
                    <input type="checkbox" name="done" id="done" <?php echo $form_data['done'] ? 'checked' : ''; ?> value="1">
                    Ολοκληρωμένο
                </label>
            </div>
            <div class="form-group">
                <label for="done_at">Ημ/νία Ολοκλήρωσης</label>
                <input type="date" name="done_at" id="done_at" value="<?php echo $form_data['done_at'] && $form_data['done_at'] !== '0000-00-00 00:00:00' ? date('Y-m-d', strtotime($form_data['done_at'])) : ''; ?>">
            </div>
            <div class="form-actions">
                <input type="submit" value="Αποθήκευση">
                <input type="button" value="Ακύρωση" onClick="parent.location='school_comments_manage.php?sch=<?php echo $sch; ?>'"">
            </div>
        </div>
    </form>
<?php else: ?>
    <div class="info-section">
        <div class="info-section-header">Διαχείριση Σχολίων για: <?php echo htmlspecialchars($school_name); ?></div>
        <div class="info-section-content">
            <div class="action-buttons">
                <INPUT TYPE='button' VALUE='Προσθήκη σχολίου' onClick="parent.location = 'school_comments_manage.php?sch=<?php echo $sch; ?>&add=1'">
                <INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='school_status.php?org=<?php echo $sch; ?>'">
            </div>
            <?php if ($comments_rs && mysqli_num_rows($comments_rs) > 0): ?>
                <div style="margin-bottom: 15px;">
                    <label for="done-filter">Φίλτρο κατάστασης:</label>
                    <select id="done-filter" class="form-control" style="display: inline-block; width: auto; padding: 5px; margin-left: 10px;">
                        <option value="">Όλα</option>
                        <option value="1">Ολοκληρωμένα</option>
                        <option value="0">Εκκρεμή</option>
                    </select>
                </div>
                <table id="comments-table">
                    <thead>
                        <tr>
                            <th>A/A</th>
                            <th>Σχολιο</th>
                            <th>Ενεργεια</th>
                            <th>Χρηστης</th>
                            <th>Υποβληθηκε</th>
                            <th>Ολοκληρωμενο</th>
                            <th>Ημ/νια Ολοκληρωσης</th>
                            <th>Ενεργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($comments_rs)): ?>
                            <?php
                            $comment = htmlspecialchars($row['comment']);
                            $action = htmlspecialchars($row['action']);
                            $addedBy = $row['added_by_username'] ? htmlspecialchars($row['added_by_username']) : '';
                            $addedAt = $row['added_at'] ? date("d-m-Y H:i", strtotime($row['added_at'])) : '';
                            $done = (int)$row['done'] === 1;
                            $doneLabel = $done ? '<span class="done-yes">ΝΑΙ</span>' : '<span class="done-no">ΟΧΙ</span>';
                            $doneAt = ($row['done_at'] && $row['done_at'] !== '0000-00-00 00:00:00') ? date("d-m-Y", strtotime($row['done_at'])) : '';
                            $doneValue = $done ? '1' : '0';
                            ?>
                            <tr data-done="<?php echo $doneValue; ?>">
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $comment; ?></td>
                                <td><?php echo $action; ?></td>
                                <td><?php echo $addedBy; ?></td>
                                <td><?php echo $addedAt; ?></td>
                                <td><?php echo $doneLabel; ?></td>
                                <td><?php echo $doneAt; ?></td>
                                <td>
                                    <INPUT TYPE='button' VALUE='Επεξεργασία' onClick="parent.location = 'school_comments_manage.php?sch=<?php echo $sch; ?>&edit=1&id=<?php echo $row['id']; ?>'">
                                    <INPUT TYPE='button' class='btn-red' VALUE='Διαγραφη' onClick="if(confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το σχόλιο;')) { parent.location = 'school_comments_manage.php?sch=<?php echo $sch; ?>&delete=1&id=<?php echo $row['id']; ?>'; }">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Δεν υπάρχουν σχόλια για το συγκεκριμένο σχολείο.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
</div>
<!-- DataTables JS -->
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../js/datatables/fixedheader/dataTables.fixedHeader.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#comments-table').DataTable({
        "language": {
            "url": "../js/datatables/greek.json"
        },
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "pageLength": 25,
        "order": [[0, 'desc']], // Order by A/A column by default
        "columnDefs": [
            { "orderable": false, "targets": [5, 7] } // Disable ordering for Ολοκληρωμενο and Ενεργειες columns
        ]
    });

    // Add filter for done column
    $('#done-filter').on('change', function() {
        var filterValue = $(this).val();
        
        if (filterValue === '') {
            // Show all rows
            table.columns(5).search('').draw();
        } else if (filterValue === '1') {
            // Show only completed
            table.columns(5).search('ΝΑΙ').draw();
        } else if (filterValue === '0') {
            // Show only pending
            table.columns(5).search('ΟΧΙ').draw();
        }
    });
});
</script>
</body>
</html>