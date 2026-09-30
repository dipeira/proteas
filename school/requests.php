<?php
header('Content-type: text/html; charset=utf-8'); 
require_once "../config.php";
require_once "../include/functions.php";

require "../tools/class.login.php";
$log = new logmein();
if ($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit;
}

if (!$_SESSION['requests']) {
    echo "<h3>Σφάλμα: Δεν έχετε δικαίωμα προβολής αιτημάτων σχολείων...</h3>";
    die("<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">");
}

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
$conn->set_charset("utf8");

// request status filter
if (isset($_GET['status'])) {
    $stat = $_GET['status'];
    $_SESSION['status'] = $_GET['status'];
} else {
    $stat = isset($_SESSION['status']) ? $_SESSION['status'] : 0;
}

if ($stat == 2) {
    $status = 0;
} elseif ($stat == 1) {
    $status = 1;
}
$stat_radio = ($stat == 2) ? 2 : (int)$stat;

$query = "SELECT id, school, school_name, request, comment, done, submitted, sxol_etos FROM school_requests WHERE sxol_etos = $sxol_etos AND hidden = 0 ";
$query .= isset($status) ? "AND done = $status " : '';
$query .= "ORDER BY submitted DESC";

$result = $conn->query($query);
$requests = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <?php 
    $root_path = '../';
    $page_title = 'Διαχείριση Αιτημάτων';
    require '../etc/head.php'; 
    ?>
    <link href="../css/style.css" rel="stylesheet" type="text/css">
    
    <!-- DataTables CSS & Buttons CSS -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css">
    
    <!-- jQuery & DataTables JS -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.print.min.js"></script>

    <style>
        body {
            padding: 20px;
        }
        
        .page-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin: 20px 0 30px 0;
        }
        
        .filter-form {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .filter-form p {
            margin: 0 0 12px 0;
            font-weight: 600;
            color: #374151;
            font-size: 0.9375rem;
        }
        
        .filter-form input[type="radio"] {
            margin-right: 8px;
            margin-left: 16px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #4FC5D6;
        }
        
        .filter-form input[type="radio"]:first-of-type {
            margin-left: 0;
        }
        
        .filter-form label {
            margin-right: 20px;
            cursor: pointer;
            font-size: 0.9375rem;
            color: #374151;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin: 24px 0;
        }
        
        #mytbl {
            width: 100% !important;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        
        #mytbl th {
            background: #f8fafc;
            color: #374151;
            font-weight: 600;
            padding: 12px 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        #mytbl td {
            padding: 10px;
            vertical-align: top;
        }
        
        .badge-done {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .badge-pending {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .dt-buttons {
            margin-bottom: 15px;
        }
        
        .dt-button {
            border-radius: 6px !important;
            padding: 6px 14px !important;
            font-weight: 500 !important;
            margin-right: 6px !important;
        }
        
        .no-results {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin: 24px 0;
        }
        
        .no-results h3 {
            color: #6b7280;
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
    </style>
</head>
<body>
<?php require '../etc/menu.php'; ?>
<div class="page-container">
    <div class="page-header">
        <h2>Διαχείριση Αιτημάτων Σχολείων</h2>
    </div>
    
    <div class="filter-form">
        <form id="request_status" method="GET">
            <p>Εμφάνιση αιτημάτων:</p>
            <input type="radio" name="status" id="status_all" value="0" <?= $stat_radio == 0 ? 'checked' : ''?> onchange="this.form.submit()">
            <label for="status_all">Όλα</label>
            <input type="radio" name="status" id="status_done" value="1" <?= $stat_radio == 1 ? 'checked' : ''?> onchange="this.form.submit()">
            <label for="status_done">Διεκπεραιωμένα</label>
            <input type="radio" name="status" id="status_pending" value="2" <?= $stat_radio == 2 ? 'checked' : ''?> onchange="this.form.submit()">
            <label for="status_pending">Μη Διεκπεραιωμένα</label>
        </form>
    </div>
    
    <?php if (count($requests) > 0): ?>
        <div class="table-container">
            <table id="mytbl" class="display cell-border">
                <thead>
                    <tr>
                        <th style="width: 50px;">A/A</th>
                        <th style="width: 200px;">Σχολείο</th>
                        <th>Αίτημα</th>
                        <th>Σχόλιο Δ/νσης</th>
                        <th style="width: 100px;">Διεκπεραίωση</th>
                        <th style="width: 140px;">Ημ/νία υποβολής</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $row): 
                        $isDone = (int)$row['done'] === 1;
                        $doneBadge = $isDone 
                            ? "<span class='badge-done'>Ναι</span>" 
                            : "<span class='badge-pending'>Όχι</span>";
                        $submittedFormatted = !empty($row['submitted']) && $row['submitted'] !== '0000-00-00 00:00:00' 
                            ? date('d-m-Y H:i', strtotime($row['submitted'])) 
                            : '-';
                        $submittedTimestamp = !empty($row['submitted']) ? strtotime($row['submitted']) : 0;
                    ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>
                            <td>
                                <a href="school_status.php?org=<?= (int)$row['school'] ?>#requests" target="_blank">
                                    <?= htmlspecialchars($row['school_name']) ?>
                                </a>
                            </td>
                            <td><?= nl2br(htmlspecialchars($row['request'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['comment'] ?? '')) ?></td>
                            <td data-filter="<?= $isDone ? 'Ναι' : 'Όχι' ?>" data-sort="<?= $isDone ? '1' : '0' ?>" style="text-align: center;">
                                <?= $doneBadge ?>
                            </td>
                            <td data-order="<?= $submittedTimestamp ?>">
                                <?= $submittedFormatted ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-results">
            <h3>Δε βρέθηκαν αιτήματα</h3>
        </div>
    <?php endif; ?>
    
    <div class="button-container">
        <INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#mytbl').DataTable({
        language: {
            url: '../js/datatables/greek.json'
        },
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'Όλα']
        ],
        pageLength: 25,
        order: [[5, 'desc']], // Ταξινόμηση κατά ημερομηνία υποβολής (φθίνουσα)
        dom: 'Bfrtlip',
        buttons: [
            {
                extend: 'excel',
                text: 'Εξαγωγή σε Excel',
                className: 'btn-green',
                title: 'Αιτήματα Σχολείων',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5],
                    format: {
                        body: function(data, row, column, node) {
                            if (column === 4) {
                                return $(node).find('.badge-done').length ? 'Ναι' : 'Όχι';
                            }
                            return $(node).text().trim();
                        }
                    }
                }
            },
            {
                extend: 'pdf',
                text: 'Εξαγωγή σε PDF',
                className: 'btn-red',
                title: 'Αιτήματα Σχολείων',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5],
                    format: {
                        body: function(data, row, column, node) {
                            if (column === 4) {
                                return $(node).find('.badge-done').length ? 'Ναι' : 'Όχι';
                            }
                            return $(node).text().trim();
                        }
                    }
                }
            },
            {
                extend: 'print',
                text: 'Εκτύπωση',
                title: 'Αιτήματα Σχολείων',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }
        ]
    });
});
</script>

</body>
</html>
