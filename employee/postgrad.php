<?php
// Generic CRUD page created for Proteas
////////////////////////////////////////
// CRUD OPTIONS
////////////////////////////////////////
// Table name to generate CRUD for
$table = 'postgrad';
// Columns to be displayed on list view
$table_list_columns = array('category','title','idryma','gnhsiothta','synafeia');
// Columns to be hidden on edit view
$hide_edit_columns = array('id', 'afm');
// Columns to be skipped on add view
$skip_add_columns  = array('id', 'updated');
// Page display name
$page_name = "Μεταπτυχιακοί Τίτλοι";
// Search column (WHERE column = $_GET['column'])
$search_column = 'afm';
////////////////////////////////////////

// Demand authorization                
require "../tools/class.login.php";
$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
  header("Location: ../tools/login.php");
}
header('Content-type: text/html; charset=utf-8');
require_once "../config.php";
require_once "../include/functions.php";

// Connect to the database
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Get table columns with comments
$query = "
    SELECT COLUMN_NAME, COLUMN_COMMENT, DATA_TYPE, COLUMN_TYPE 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME = '$table'
";
$result = $mysqli->query($query);
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        
        // Filter the columns to exclude those in $skip_add_columns
        $filtered_columns = array_filter($columns, function($column) use ($skip_add_columns) {
            return !in_array($column['COLUMN_NAME'], $skip_add_columns);
        });
        
        // Get column names and values from the filtered columns
        $fields = implode(", ", array_column($filtered_columns, 'COLUMN_NAME'));
        $values = [];
        foreach ($filtered_columns as $column) {
            $column_name = $column['COLUMN_NAME'];
            $values[] = $mysqli->real_escape_string($_POST[$column_name]);
        }
        
        $values_str = implode("', '", $values);
        $query = "INSERT INTO $table ($fields) VALUES ('$values_str')";
        // echo $query;
        $mysqli->query($query);
        // Redirect to list view after successful creation
        $afm_value = $_POST[$search_column];
        $redirect_url = "?$search_column=$afm_value";
        header("Location: postgrad.php$redirect_url");
        exit;
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $updates = [];
        foreach ($columns as $column) {
            if ($column['COLUMN_NAME'] != 'id') {
                $columnName = $column['COLUMN_NAME'];
                
                // Special handling for 'updated' column - set to current timestamp
                if ($columnName == 'updated') {
                    $updates[] = "$columnName = NOW()";
                    continue;
                }
                
                $value = $_POST[$columnName];
                 
                // Check if the column is a checkbox (assuming 'tinyint' is used for checkboxes)
                if ($column['DATA_TYPE'] === 'tinyint') {
                    $value = isset($_POST[$columnName]) ? 1 : 0;
                } else {
                    $value = $mysqli->real_escape_string($value);
                }
                 
                $updates[] = "$columnName = '$value'";
            }
        }
        $query = "UPDATE $table SET " . implode(", ", $updates) . " WHERE id = $id";
        $mysqli->query($query);
        // Redirect to list view after successful update
        $afm_value = $_POST[$search_column];
        $redirect_url = "?$search_column=$afm_value";
        header("Location: postgrad.php$redirect_url");
        exit;
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        // Get afm before deleting to redirect properly
        $result = $mysqli->query("SELECT $search_column FROM $table WHERE id = $id");
        $row = $result->fetch_assoc();
        $afm_value = $row[$search_column];
        $query = "DELETE FROM $table WHERE id = $id";
        $mysqli->query($query);
        // Redirect to list view after successful deletion
        $redirect_url = "?$search_column=$afm_value";
        header("Location: postgrad.php$redirect_url");
        exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.min.js"></script>
    <title><?php echo $page_name; ?></title>
    <style>
        /* Postgrad page styling - matching employee.php */
        body {
            padding: 20px;
        }
        
        /* Main header styling */
        .imagetable th {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 50%, #2A8B9A 100%) !important;
            color: white;
            font-size: 1.125rem;
            font-weight: 700;
            padding: 14px 16px;
            text-transform: none;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        
        /* Table row styling */
        .imagetable tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .imagetable tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* Label cells styling - first column */
        .imagetable td:first-child {
            background: linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%);
            font-weight: 600;
            color: #1e40af;
            padding: 12px 16px;
            border-right: 2px solid #bae6fd;
            width: 25%;
            vertical-align: top;
        }
        
        /* Data cells styling - second column */
        .imagetable td:nth-child(2) {
            padding: 12px 16px;
            color: #374151;
            vertical-align: top;
            background: #ffffff;
        }
        
        /* Alternate row styling for visual separation */
        .imagetable tbody tr:nth-child(even) td:first-child {
            background: linear-gradient(90deg, #e0f7fa 0%, #b2ebf2 100%);
        }
        
        /* Hover effect for rows */
        .imagetable tbody tr:hover td:nth-child(2) {
            background-color: #f8fafc;
        }
        
        /* Link styling */
        .imagetable a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .imagetable a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        /* Form inputs */
        .imagetable input[type="text"],
        .imagetable input[type="date"],
        .imagetable input[type="submit"],
        .imagetable textarea,
        .imagetable select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        
        .imagetable textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .imagetable input[type="text"]:focus,
        .imagetable input[type="date"]:focus,
        .imagetable textarea:focus,
        .imagetable select:focus {
            outline: none;
            border-color: #4FC5D6;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.1);
        }
        
        /* Buttons styling */
        .imagetable button,
        .imagetable input[type="submit"],
        .imagetable input[type="button"] {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
            width: auto;
        }
        
        .imagetable button:hover,
        .imagetable input[type="submit"]:hover,
        .imagetable input[type="button"]:hover {
            background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 197, 214, 0.4);
        }
        
        .btn-link {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
        }
        
        .btn-link a {
            color: white !important;
        }
        
        .btn-red {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
        }
        
        .btn-yellow {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        }
        
        /* Checkbox styling */
        .imagetable input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #4FC5D6;
            cursor: pointer;
            box-sizing: border-box;
        }
        
        /* Modal-specific styling */
        #postgrad-modal .imagetable {
            width: 100%;
            margin: 0;
        }
        
        #postgrad-modal h1,
        #postgrad-modal h2 {
            color: #0f3f66;
            margin-top: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .imagetable td:first-child {
                width: 30%;
            }
        }
    </style>
</head>
<body>

<h1><?php echo $page_name; ?></h1>

<?php
// Fetch single record for editing
$edit_record = null;
if (isset($_GET['edit'])):
    $edit_id = $_GET['edit'];
    $result = $mysqli->query("SELECT * FROM $table WHERE id = $edit_id");
    $edit_record = $result->fetch_assoc();

?>
<!-- Edit Form -->
    <h2>Επεξεργασία εγγραφής με ID: <?php echo $edit_record['id']; ?></h2>
    <table border="1" class="imagetable tablesorter">
        <form method="POST">
            <?php foreach ($columns as $column):
                $is_disabled = in_array($column['COLUMN_NAME'], $hide_edit_columns) ? 'disabled' : '';
                
                $input = '';
                switch ($column['DATA_TYPE']) {
                    case 'varchar':
                    case 'int':
                        $input = "<input type='text' name='". $column['COLUMN_NAME'] ."' value='". htmlspecialchars($edit_record[$column['COLUMN_NAME']], ENT_QUOTES) ."' $is_disabled>";
                        break;
                    case 'date':
                        $input = "<input type='date' name='". $column['COLUMN_NAME'] ."' value='". $edit_record[$column['COLUMN_NAME']] ."'>";
                        break;
                    case 'tinyint':
                        $is_checked = $edit_record[$column['COLUMN_NAME']] == 1 ? 'checked' : '';
                        $input = "<input type='checkbox' name='". $column['COLUMN_NAME'] ."' ". $is_checked ."/>";
                        break;
                    case 'text':
                        $input = "<textarea name='". $column['COLUMN_NAME'] ."' rows='4' cols='80'>".htmlspecialchars($edit_record[$column['COLUMN_NAME']], ENT_QUOTES)."</textarea>";
                        break;
                    case 'enum':
                        // Extracting the enum values from COLUMN_TYPE
                        preg_match("/^enum\((.*)\)$/", $column['COLUMN_TYPE'], $matches);
                        $enum_values = str_getcsv($matches[1], ',', "'");
                        $input = "<select name='". $column['COLUMN_NAME'] ."'>";
                        foreach ($enum_values as $value) {
                            $is_selected = $edit_record[$column['COLUMN_NAME']] == $value ? 'selected' : '';
                            $input .= "<option value='$value' $is_selected>$value</option>";
                        }
                        $input .= "</select>";
                        break;
                    case 'timestamp':
                        $input = "<p>" . date("d-m-Y, H:i:s",strtotime($edit_record[$column['COLUMN_NAME']])). "</p>";
                    default:
                        # code...
                        break;
                }
                ?>
                <tr>
                <td><label><?php echo $column['COLUMN_COMMENT'] ?: $column['COLUMN_NAME']; ?>:</label></td>
                <td><?php echo $input; ?></td>
                </tr>
            <?php endforeach; ?>
            <tr><td>
            <button type="submit" name="update">Ενημέρωση</button>
            &nbsp;
            <button class="btn-link btn-yellow"><a href="?<?php echo $search_column; ?>=<?php echo $edit_record[$search_column]; ?>">Λίστα</a></button>
            </td><td></td></tr>
            <input type="hidden" name="<?php echo $search_column ?>" value="<?php echo $edit_record[$search_column]; ?>">
            <input type="hidden" name="id" value="<?php echo $edit_record['id']; ?>">
        </form>
    </table>


<?php elseif (isset($_GET['add']) && isset($_GET[$search_column])): ?>


<!-- Create Form -->
<h2>Νέα εγγραφή</h2>
<table border="1" class="imagetable tablesorter">
<form method="POST">
    <input type="hidden" name="<?php echo $search_column; ?>" value="<?php echo $_GET[$search_column]; ?>">
    <?php foreach ($columns as $column): 
        if (in_array($column['COLUMN_NAME'],$skip_add_columns)) continue;
        $input = '';
        $is_disabled = in_array($column['COLUMN_NAME'], $hide_edit_columns) ? 'disabled' : '';
        $edit_record[$search_column] = isset($_GET[$search_column]) ? $_GET[$search_column] : '';
        switch ($column['DATA_TYPE']) {
            case 'varchar':
            case 'int':
                $input = "<input type='text' name='". $column['COLUMN_NAME'] ."' value='". htmlspecialchars($edit_record[$column['COLUMN_NAME']] ?? '', ENT_QUOTES) ."' $is_disabled>";
                break;
            case 'date':
                $input = "<input type='date' name='". $column['COLUMN_NAME'] ."' value='". ($edit_record[$column['COLUMN_NAME']] ?? '') ."'>";
                break;
            case 'tinyint':
                $is_checked = ($edit_record[$column['COLUMN_NAME']] ?? 0) == 1 ? 'checked' : '';
                $input = "<input type='checkbox' name='". $column['COLUMN_NAME'] ."' ". $is_checked ."/>";
                break;
            case 'text':
                $input = "<textarea name='". $column['COLUMN_NAME'] ."' rows='4' cols='80'>".htmlspecialchars($edit_record[$column['COLUMN_NAME']] ?? '', ENT_QUOTES)."</textarea>";
                break;
            case 'enum':
                // Extracting the enum values from COLUMN_TYPE
                preg_match("/^enum\((.*)\)$/", $column['COLUMN_TYPE'], $matches);
                $enum_values = str_getcsv($matches[1], ',', "'");
                $input = "<select name='". $column['COLUMN_NAME'] ."'>";
                foreach ($enum_values as $value) {
                    $is_selected = ($edit_record[$column['COLUMN_NAME']] ?? '') == $value ? 'selected' : '';
                    $input .= "<option value='$value' $is_selected>$value</option>";
                }
                $input .= "</select>";
                break;
            default:
                # code...
                break;
        }
        
        
        ?>
        <tr>
        <td><label><?php echo $column['COLUMN_COMMENT'] ?: $column['COLUMN_NAME']; ?>:</label></td>
        <td><?php echo $input; ?></td>
        </tr>
    <?php endforeach; ?>
    <tr><td>
    <button type="submit" name="create">Δημιουργία</button> &nbsp;
    <button class="btn-link btn-yellow"><a href="?<?php echo $search_column; ?>=<?php echo $edit_record[$search_column]; ?>">Λίστα</a></button>
    </td><td></td></tr>
    

</table>
</form>

<?php elseif (isset($_GET[$search_column])):
    // Fetch all records (if search_column == 'all')
    if ($_GET[$search_column] == 'all'){
        $query = "SELECT * FROM $table ";    
    } else {
    // Fetch records filtered by search_column
        $query = "SELECT * FROM $table WHERE $search_column = '".$mysqli->real_escape_string($_GET[$search_column])."'";
    }
    // echo $query;
    $result = $mysqli->query($query);
    $records = [];
    if (mysqli_num_rows($result) == 0){
        echo "<h2>Δε βρέθηκαν εγγραφές</h2>";
        echo "<br><button class='btn-link'><a href='postgrad.php?add=1&$search_column=".$_GET[$search_column]."'>Προσθήκη</a></button>";
        $mysqli->close();
        echo "</body></html>";
        die();
    }
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
?>
    <!-- Records List -->
    <h2>Λίστα εγγραφών</h2>
    <?php
    // Get employee ID from AFM to create link to employee page
    $afm_value = $_GET[$search_column];
    $emp_query = "SELECT id FROM employee WHERE afm like '%".$mysqli->real_escape_string($afm_value)."%' LIMIT 1";
    $ektaktoi_query = "SELECT id FROM ektaktoi WHERE afm like '%".$mysqli->real_escape_string($afm_value)."%' LIMIT 1";
    $ektaktoi_result = $mysqli->query($ektaktoi_query);
    if ($ektaktoi_result && mysqli_num_rows($ektaktoi_result) > 0) {
        $ektaktoi_row = $ektaktoi_result->fetch_assoc();
        $ektaktoi_id = $ektaktoi_row['id'];
        $profile_link = "<p><button class='btn-link'><a href='ektaktoi.php?id=$ektaktoi_id&op=view'>← Επιστροφή στην καρτέλα εκπαιδευτικού</a></button></p>";
    } else {
        $emp_query = "SELECT id FROM employee WHERE afm like '%".$mysqli->real_escape_string($afm_value)."%' LIMIT 1";
        $emp_result = $mysqli->query($emp_query);
        if ($emp_result && mysqli_num_rows($emp_result) > 0) {
            $emp_row = $emp_result->fetch_assoc();
            $emp_id = $emp_row['id'];
            $profile_link = "<p><button class='btn-link'><a href='employee.php?id=$emp_id&op=view'>← Επιστροφή στην καρτέλα εκπαιδευτικού</a></button></p>";
        }
    }
    echo $profile_link;
    ?>
    <table border="1" class="imagetable tablesorter" style="width:100%;">
        <thead>
        <tr>
            <?php foreach ($columns as $column): 
                if (!in_array($column['COLUMN_NAME'],$table_list_columns)) continue;
                ?>
                <th><?php echo $column['COLUMN_COMMENT'] ?: $column['COLUMN_NAME']; ?></th>
            <?php endforeach; ?>
            <th>Ενέργειες</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($records as $record): ?>
            <tr>
                <?php foreach ($columns as $column): 
                    if (!in_array($column['COLUMN_NAME'],$table_list_columns)) continue;
                    ?>
                    <td><?php if ($column['DATA_TYPE'] == 'tinyint') { 
                            $is_checked = $record[$column['COLUMN_NAME']] == 1 ? 'checked' : '';
                            echo "<input type='checkbox' name='". $column['COLUMN_NAME'] ."' ". $is_checked ." disabled/>";
                        } else {
                            echo htmlspecialchars($record[$column['COLUMN_NAME']] ?? '');
                        }
                    ?></td>
                <?php endforeach; ?>
                <td>
                    <button class="btn-link"><a href="postgrad.php?edit=<?php echo $record['id']; ?>">Επεξεργασία</a></button>
                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete();">
                        <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                        <button class="btn-red" type="submit" name="delete">Διαγραφή</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr><td>
        <button class="btn-link"><a href="postgrad.php?add=1&<?php echo $search_column; ?>=<?php echo $record[$search_column]; ?>">Προσθήκη</a></button>
        </td><td colspan="<?php echo count($table_list_columns) + 1; ?>"></td></tr>
        </tfoot>
    </table>
<?php else: ?>
    <h1>Δεν υπάρχουν εγγραφές για εμφάνιση</h1>
<?php endif; ?>

<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/jquery.tablesorter.min.js"></script>
<script>
function confirmDelete() {
    return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή την εγγραφή;');
}

$(document).ready(function() {
    $('table.tablesorter').tablesorter({widgets: ['zebra']});
});
</script>
</body>
</html>

<?php
$mysqli->close();
?>
