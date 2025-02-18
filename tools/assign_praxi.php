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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ανάθεση Πράξεων</title>
    <link href="../css/style.css" rel="stylesheet" type="text/css">
    <link href="../css/select2.min.css" rel="stylesheet" />
    <script src="../js/jquery.js"></script>
    <script src="../js/select2.min.js"></script>
    <?php
    // include all datatables related files
    require_once('../js/datatables/includes.html');
    ?>
</head>
<body>
    <h2>Ανάθεση Πράξεων σε Εκπαιδευτικούς</h2>
    <div style="margin-bottom: 10px;">
        <button id="selectAll" class="btn-blue">Επιλογή Όλων</button>
        <button id="deselectAll" class="btn-red">Αποεπιλογή Όλων</button>
    </div>
    <table id="ektaktoiTable" id='mytbl' class='imagetable tablesorter' border='2'>
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

    <select id="praxiSelect" style="width: 200px;">
        <?php foreach ($praxi_options as $option): ?>
            <option value="<?= $option['id'] ?>"><?= $option['name'] ?></option>
        <?php endforeach; ?>
    </select>

    <button id="assignPraxi">Ανάθεση Πράξης</button>

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

            $('#assignPraxi').click(function() {
                var selectedIds = [];
                $('.select-row:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                var praxiId = $('#praxiSelect').val();
                console.log(selectedIds);
                console.log(praxiId);

                if (selectedIds.length > 0 && praxiId) {
                    $.ajax({
                        url: 'assign_praxi_action.php',
                        method: 'POST',
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
    <br><INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../employee/ektaktoi_list.php'">
</body>
</html> 