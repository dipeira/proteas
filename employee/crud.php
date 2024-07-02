<?php
// CRUD OPTIONS
// Columns to be displayed on list view
$table_list_columns = array('afm','category','title','idryma');
$hide_edit_columns = array('id', 'afm');
// Table name to generate CRUD for
$table = 'postgrad';
$page_name = "Μεταπτυχιακοί Τίτλοι";
$search_column = 'afm';


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
        $fields = implode(", ", array_column($columns, 'COLUMN_NAME'));
        $values = implode("', '", array_map([$mysqli, 'real_escape_string'], array_values($_POST)));
        $query = "INSERT INTO $table ($fields) VALUES ('$values')";
        $mysqli->query($query);
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $updates = [];
        foreach ($columns as $column) {
            if ($column['COLUMN_NAME'] != 'id') {
                $updates[] = $column['COLUMN_NAME'] . " = '" . $mysqli->real_escape_string($_POST[$column['COLUMN_NAME']]) . "'";
            }
        }
        $query = "UPDATE $table SET " . implode(", ", $updates) . " WHERE id = $id";
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
            <button type="submit" name="update">Ενημέρωση</button>
            <input type="hidden" name="id" value="<?php echo $edit_record['id']; ?>">
        </form>
    </table>


<?php elseif (isset($_GET['add']) && isset($_GET[$search_column])): ?>


<!-- Create Form -->
<h2>Νέα εγγραφή</h2>
<table border="1" class="imagetable tablesorter">
<form method="POST">
    <?php foreach ($columns as $column): ?>
        <tr>
        <td style="width:25%;"><label><?php echo $column['COLUMN_COMMENT'] ?: $column['COLUMN_NAME']; ?>:</label></td>
        <td><input type="text" name="<?php echo $column['COLUMN_NAME']; ?>" style="width:90%;"><br></td>
        </tr>
    <?php endforeach; ?>
    <button type="submit" name="create">Δημιουργία</button>
</table>
</form>

<?php elseif (isset($_GET[$search_column])):
    // Fetch all records
    $query = "SELECT * FROM $table WHERE $search_column = '".$_GET[$search_column]."'";
    // echo $query;
    $result = $mysqli->query($query);
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
?>
<!-- Records List -->
<h2>Λίστα εγγραφών</h2>
<table border="1" class="imagetable tablesorter">
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
                <td><?php echo $record[$column['COLUMN_NAME']]; ?></td>
            <?php endforeach; ?>
            <td>
                <button><a href="?edit=<?php echo $record['id']; ?>">Επεξεργασία</a></button>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                    <button type="submit" name="delete">Διαγραφή</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
    <h1>Δεν υπάρχουν εγγραφές για εμφάνιση</h1>
<?php endif; ?>
</body>
</html>

<?php
$mysqli->close();
?>
