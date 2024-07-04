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
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $updates = [];
        foreach ($columns as $column) {
            if ($column['COLUMN_NAME'] != 'id') {
                $columnName = $column['COLUMN_NAME'];
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
        echo "<h3>Η εγγραφή ενημερώθηκε επιτυχώς!</h3>";
        $mysqli->query($query);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $query = "DELETE FROM $table WHERE id = $id";
        $mysqli->query($query);
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <title><?php echo $page_name; ?></title>
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
                        $input = "<input type='text' name='". $column['COLUMN_NAME'] ."' value='". $edit_record[$column['COLUMN_NAME']] ."' style='width:90%;' $is_disabled>";
                        break;
                    case 'date':
                        $input = "<input type='date' name='". $column['COLUMN_NAME'] ."' value='". $edit_record[$column['COLUMN_NAME']] ."' style='width:90%;'>";
                        break;
                    case 'tinyint':
                        $is_checked = $edit_record[$column['COLUMN_NAME']] == 1 ? 'checked' : '';
                        $input = "<input type='checkbox' name='". $column['COLUMN_NAME'] ."' ". $is_checked ."/>";
                        break;
                    case 'text':
                        $input = "<textarea name='". $column['COLUMN_NAME'] ."' rows='1' cols='80'>".$edit_record[$column['COLUMN_NAME']]."</textarea>";
                        break;
                    case 'enum':
                        // Extracting the enum values from COLUMN_TYPE
                        preg_match("/^enum\((.*)\)$/", $column['COLUMN_TYPE'], $matches);
                        $enum_values = str_getcsv($matches[1], ',', "'");
                        $input = "<select name='". $column['COLUMN_NAME'] ."' style='width:90%;'>";
                        foreach ($enum_values as $value) {
                            $is_selected = $edit_record[$column['COLUMN_NAME']] == $value ? 'selected' : '';
                            $input .= "<option value='$value' $is_selected>$value</option>";
                        }
                        $input .= "</select>";
                        break;
                    case 'timestamp':
                        $input = "<p style='width:90%;' >" . date("d-m-Y, H:i:s",strtotime($edit_record[$column['COLUMN_NAME']])). "</p>";
                    default:
                        # code...
                        break;
                }
                ?>
                <tr>
                <td style="width:25%;"><label><?php echo $column['COLUMN_COMMENT'] ?: $column['COLUMN_NAME']; ?>:</label></td>
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
                $input = "<input type='text' name='". $column['COLUMN_NAME'] ."' value='". $edit_record[$column['COLUMN_NAME']] ."' style='width:90%;' $is_disabled>";
                break;
            case 'date':
                $input = "<input type='date' name='". $column['COLUMN_NAME'] ."' value='". $edit_record[$column['COLUMN_NAME']] ."' style='width:90%;'>";
                break;
            case 'tinyint':
                $is_checked = $edit_record[$column['COLUMN_NAME']] == 1 ? 'checked' : '';
                $input = "<input type='checkbox' name='". $column['COLUMN_NAME'] ."' ". $is_checked ."/>";
                break;
            case 'text':
                $input = "<textarea name='". $column['COLUMN_NAME'] ."' rows='1' cols='80'>".$edit_record[$column['COLUMN_NAME']]."</textarea>";
                break;
            case 'enum':
                // Extracting the enum values from COLUMN_TYPE
                preg_match("/^enum\((.*)\)$/", $column['COLUMN_TYPE'], $matches);
                $enum_values = str_getcsv($matches[1], ',', "'");
                $input = "<select name='". $column['COLUMN_NAME'] ."' style='width:90%;'>";
                foreach ($enum_values as $value) {
                    $is_selected = $edit_record[$column['COLUMN_NAME']] == $value ? 'selected' : '';
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
        <td style="width:25%;"><label><?php echo $column['COLUMN_COMMENT'] ?: $column['COLUMN_NAME']; ?>:</label></td>
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
        $query = "SELECT * FROM $table WHERE $search_column = '".$_GET[$search_column]."'";
    }
    // echo $query;
    $result = $mysqli->query($query);
    $records = [];
    if (mysqli_num_rows($result) == 0){
        echo "<h2>Δε βρέθηκαν εγγραφές</h2>";
        echo "<br><button class='btn-link'><a href='?add=1&$search_column=".$_GET[$search_column]."'>Προσθήκη</a></button>";
        die();
    }
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
?>
    <!-- Records List -->
    <h2>Λίστα εγγραφών</h2>
    <table border="1" class="imagetable tablesorter" style="width:100%;">
        <tr>
            <?php foreach ($columns as $column): 
                if (!in_array($column['COLUMN_NAME'],$table_list_columns)) continue;
                ?>
                <th><?php echo $column['COLUMN_COMMENT'] ?: $column['COLUMN_NAME']; ?></th>
            <?php endforeach; ?>
            <th>Ενέργειες</th>
        </tr>
        <?php foreach ($records as $record): ?>
            <tr>
                <?php foreach ($columns as $column): 
                    if (!in_array($column['COLUMN_NAME'],$table_list_columns)) continue;
                    ?>
                    <td><?php if ($column['DATA_TYPE'] == 'tinyint') { 
                            $is_checked = $record[$column['COLUMN_NAME']] == 1 ? 'checked' : '';
                            echo "<input type='checkbox' name='". $column['COLUMN_NAME'] ."' ". $is_checked ."/>";
                        } else {
                            echo $record[$column['COLUMN_NAME']] ;
                        }
                    ?></td>
                <?php endforeach; ?>
                <td>
                    <button class="btn-link"><a href="?edit=<?php echo $record['id']; ?>">Επεξεργασία</a></button>
                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete();">
                        <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                        <button class="btn-red" type="submit" name="delete">Διαγραφή</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr><td>
        <button class="btn-link"><a href="?add=1&<?php echo $search_column; ?>=<?php echo $record[$search_column]; ?>">Προσθήκη</a></button>
        </td><td colspan=<?php echo count($table_list_columns); ?>></td></tr>
    </table>
<?php else: ?>
    <h1>Δεν υπάρχουν εγγραφές για εμφάνιση</h1>
<?php endif; ?>
</body>
</html>

<script>
function confirmDelete() {
    return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή την εγγραφή;');
}
</script>

<?php
$mysqli->close();
?>
