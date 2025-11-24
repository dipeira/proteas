<?php
require_once '../config.php';
require_once '../include/functions.php';
require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
require '../etc/menu.php';

// Connect to the database
$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

// Fetch ektaktoi with praxi NULL or 0
$query = "SELECT id, name, surname, klados, hm_anal FROM ektaktoi WHERE praxi IN (NULL, 0, 1)";
$result = mysqli_query($mysqlconnection, $query);

// Fetch praxi options
$praxi_query = "SELECT id, name FROM praxi";
$praxi_result = mysqli_query($mysqlconnection, $praxi_query);
$praxi_options = [];
while ($row = mysqli_fetch_assoc($praxi_result)) {
    $praxi_options[] = $row;
}

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Ανάθεση εκπαιδευτικών σε πράξεις</title>
    <link href="../css/style.css" rel="stylesheet" type="text/css">
    <link href="../css/select2.min.css" rel="stylesheet" />
    <script src="../js/jquery.js"></script>
    <script src="../js/select2.min.js"></script>
    <?php
    // include all datatables related files
    require_once('../js/datatables/includes.html');
    ?>
    <style>
        /* Page layout */
        .assign-praxi-container {
            margin: 0 auto;
            padding: 20px;
            max-width: 1400px;
        }
        
        /* Action buttons area */
        .action-buttons-area {
            display: flex;
            gap: 12px;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background: linear-gradient(90deg, #f0f9ff 0%, #e0f7fa 100%);
            border-radius: 8px;
            border: 1px solid #bae6fd;
            flex-wrap: wrap;
        }
        
        .action-buttons-area label {
            font-weight: 600;
            color: #1f2937;
            margin-right: 8px;
        }
        
        /* Button styling */
        button.btn-blue,
        .btn-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: white !important;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }
        
        button.btn-blue:hover,
        .btn-blue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.4);
        }
        
        button#assignPraxi {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%) !important;
            color: white !important;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
        }
        
        button#assignPraxi:hover {
            background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 197, 214, 0.4);
        }
        
        /* Select2 styling */
        .select2-container {
            min-width: 250px;
        }
        
        .select2-container--default .select2-selection--single {
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            height: 42px !important;
            padding: 4px 8px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            color: #1f2937;
            font-weight: 500;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 8px;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #4FC5D6 !important;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.2) !important;
        }
        
        /* Checkbox styling */
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #4FC5D6;
        }
        
        .imagetable th input[type="checkbox"] {
            margin: 0;
        }
        
        /* DataTables styling adjustments */
        .dataTables_wrapper {
            margin-top: 20px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
        }
        
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px 12px;
        }
        
        /* Table header checkbox alignment */
        .imagetable th:first-child {
            text-align: center;
            width: 50px;
        }
        
        .imagetable td:first-child {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="assign-praxi-container">
        <div class="page-header">
            <h2>Ανάθεση εκπαιδευτικών σε πράξεις</h2>
        </div>
        
        <div class="action-buttons-area">
            <button id="selectAll" class="btn-blue">✅ Επιλογή Όλων</button>
            <button id="deselectAll" class="btn-red">❌ Αποεπιλογή Όλων</button>
            <div style="flex: 1;"></div>
            <label for="praxiSelect">Πράξη:</label>
            <select id="praxiSelect" style="width: 250px;">
                <?php foreach ($praxi_options as $option): ?>
                    <option value="<?= $option['id'] ?>"><?= $option['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <button id="assignPraxi" type="button">📝 Ανάθεση σε πράξη</button>
        </div>
        
        <div style="display: flex; justify-content: center;">
            <table id="ektaktoiTable" class='imagetable' border='2'>
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAllCheckbox"></th>
                <th>ID</th>
                <th>Επώνυμο</th>
                <th>Όνομα</th>
                <th>Κλάδος</th>
                <th>Ημ. Ανάληψης</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><input type="checkbox" class="select-row" value="<?= $row['id'] ?>"></td>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['surname'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= getKlados($row['klados'], $mysqlconnection) ?></td>
                    <td><?= $row['hm_anal'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <INPUT TYPE='button' class='btn-red' VALUE='← Επιστροφή' onClick="parent.location='../employee/ektaktoi_list.php'">
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#ektaktoiTable').DataTable({
                "language": {
                    "sProcessing":   "Επεξεργασία...",
                    "sLengthMenu":   "Δείξε _MENU_ εγγραφές",
                    "sZeroRecords":  "Δεν βρέθηκαν εγγραφές που να ταιριάζουν",
                    "sInfo":         "Δείχνοντας _START_ έως _END_ από _TOTAL_ εγγραφές",
                    "sInfoEmpty":    "Δείχνοντας 0 έως 0 από 0 εγγραφές",
                    "sInfoFiltered": "(φιλτραρισμένες από _MAX_ συνολικά εγγραφές)",
                    "sInfoPostFix":  "",
                    "sSearch":       "Αναζήτηση:",
                    "sUrl":          "",
                    "oPaginate": {
                        "sFirst":    "Πρώτη",
                        "sPrevious": "Προηγούμενη",
                        "sNext":     "Επόμενη",
                        "sLast":     "Τελευταία"
                    }
                },
                "order": [[ 2, "asc" ]], // Sort by surname by default
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Όλες"]],
                "pageLength": 25
            });
            
            $('#praxiSelect').select2();

            // Handle shift-click selection
            var lastChecked = null;
            var $checkboxes = $('.select-row');

            $checkboxes.click(function(e) {
                if (!lastChecked) {
                    lastChecked = this;
                    return;
                }

                if (e.shiftKey) {
                    var start = $checkboxes.index(this);
                    var end = $checkboxes.index(lastChecked);

                    $checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
                        .prop('checked', lastChecked.checked);
                }

                lastChecked = this;
            });

            // Select All button (visible rows only)
            $('#selectAll').click(function() {
                table.page.len(-1).draw(); // Show all records
                $('.select-row').prop('checked', true);
            });

            // Deselect All button
            $('#deselectAll').click(function() {
                $('.select-row').prop('checked', false);
            });

            // Header checkbox to select/deselect visible rows
            $('#selectAllCheckbox').change(function() {
                var checked = this.checked;
                table.page.len(-1).draw(); // Show all records
                $('.select-row').prop('checked', checked);
            });

            $('#assignPraxi').click(function(e) {
                e.preventDefault();
                var selectedIds = [];
                $('.select-row:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                var praxiId = $('#praxiSelect').val();
                // console.log(selectedIds);
                // console.log(praxiId);

                if (selectedIds.length > 0 && praxiId) {
                    $.ajax({
                        url: 'assign_praxi_action.php',
                        type: 'POST',
                        data: { ids: selectedIds, praxi_id: praxiId },
                        success: function(response) {
                            if (!response) {
                                alert('Empty response from server.');
                                return;
                            }
                            try {
                                var jsonResponse = JSON.parse(response);
                                if (jsonResponse.success) {
                                    alert('Η ανάθεση πράξης ολοκληρώθηκε με επιτυχία!');
                                    location.reload();
                                } else {
                                    alert('Σφάλμα: ' + jsonResponse.message);
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                alert('Σφάλμα κατά την επεξεργασία της απάντησης από τον διακομιστή.');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('AJAX Error:', textStatus, errorThrown);
                            alert('Σφάλμα κατά την αποστολή των δεδομένων. Παρακαλώ δοκιμάστε ξανά.');
                        }
                    });
                } else {
                    alert('Παρακαλώ επιλέξτε εκπαιδευτικούς και πράξη.');
                }
            });
        });
    </script>
</body>
</html> 