<?php
header('Content-type: text/html; charset=utf-8');
require '../config.php';

require "../tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}

// check if super-user
if ($_SESSION['userlevel']<>0) {
    header("Location: ../index.php");
}

require_once '../include/functions.php';
require_once '../include/functions_general.php';

// Initialize database connection
try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete advisor
    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $stmt = $db->prepare("DELETE FROM symvouloi_epist WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: symvouloi_epist.php");
        exit();
    }
    // Import from CSV
    if (isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        $processed = 0;
        $imported = 0;
        $skipped_dup = 0;
        $skipped_invalid = 0;
        $skipped_invalid_afm = 0;
        if (($handle = fopen($file, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle, 1000, ",");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $processed++;
                $klados_code = $data[0];
                $afm = trim($data[1]);
                $eponymo = $data[2];
                $onoma = $data[3];
                $emp_id = !empty($data[4]) ? $data[4] : NULL;
                $sch_ids = !empty($data[5]) ? $data[5] : NULL;

                // Validate and pad AFM: must be numeric, pad with leading zeros to 9 digits
                if (!is_numeric($afm) || strlen($afm) > 9) {
                    $skipped_invalid_afm++;
                    continue;
                }
                $afm = str_pad($afm, 9, '0', STR_PAD_LEFT);

                // Find klados id from perigrafh
                $stmt = $db->prepare("SELECT id FROM klados WHERE perigrafh = ?");
                $stmt->execute([$klados_code]);
                $klados_row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$klados_row) {
                    $skipped_invalid++;
                    continue;
                }
                $klados = $klados_row['id'];

                // Check for duplicate AFM
                $stmt = $db->prepare("SELECT id FROM symvouloi_epist WHERE afm = ?");
                $stmt->execute([$afm]);
                if ($stmt->fetch()) {
                    $skipped_dup++;
                    continue;
                }

                // Insert new record
                $stmt = $db->prepare("INSERT INTO symvouloi_epist (klados, afm, eponymo, onoma, emp_id, sch_ids) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$klados, $afm, $eponymo, $onoma, $emp_id, $sch_ids]);
                $imported++;
            }
            fclose($handle);
        }
        $message = "Επεξεργάστηκαν $processed γραμμές. Εισήχθησαν $imported, παραλείφθηκαν $skipped_dup διπλότυπα, $skipped_invalid άκυροι κλάδοι, $skipped_invalid_afm άκυρα ΑΦΜ.";
        $_SESSION['import_message'] = $message;
        header("Location: symvouloi_epist.php");
        exit();
    }
    $klados = $_POST['klados'];
    $afm = $_POST['afm'];
    $eponymo = $_POST['eponymo'];
    $onoma = $_POST['onoma'];
    $emp_id = !empty($_POST['emp_id']) ? $_POST['emp_id'] : NULL;
    $sch_ids = isset($_POST['sch_ids']) ? implode(',', $_POST['sch_ids']) : NULL;

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update existing advisor
        $id = $_POST['id'];
        $stmt = $db->prepare("UPDATE symvouloi_epist SET klados = ?, afm = ?, eponymo = ?, onoma = ?, emp_id = ?, sch_ids = ? WHERE id = ?");
        $stmt->execute([$klados, $afm, $eponymo, $onoma, $emp_id, $sch_ids, $id]);
    } else {
        // Insert new advisor
        $stmt = $db->prepare("INSERT INTO symvouloi_epist (klados, afm, eponymo, onoma, emp_id, sch_ids) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$klados, $afm, $eponymo, $onoma, $emp_id, $sch_ids]);
    }
    header("Location: symvouloi_epist.php"); // Redirect to avoid form resubmission
    exit();
}

// Fetch advisors for display
$advisors = $db->query("SELECT * FROM symvouloi_epist")->fetchAll(PDO::FETCH_ASSOC);

// Fetch schools for select2
$schools = $db->query("SELECT id, name FROM school WHERE anenergo = 0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch klados for select from 'klados' table
$klados_options_db = $db->query("SELECT * FROM klados ORDER BY perigrafh")->fetchAll(PDO::FETCH_ASSOC);
$klados_options = [];
foreach ($klados_options_db as $klados_row) {
    $klados_options[$klados_row['id']] = $klados_row['perigrafh']." (".$klados_row['onoma'].")";
}

$editing_advisor = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM symvouloi_epist WHERE id = ?");
    $stmt->execute([$id]);
    $editing_advisor = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editing_advisor && $editing_advisor['sch_ids']) {
        $editing_advisor['sch_ids'] = explode(',', $editing_advisor['sch_ids']);
    } else {
        $editing_advisor['sch_ids'] = [];
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <?php
    $root_path = '../';
    $page_title = 'Σύμβουλοι Επιστημονικής Ευθύνης';
    require '../etc/head.php';
    ?>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link href="../css/select2.min.css" rel="stylesheet" />
    <script src="../js/jquery.js"></script>
    <script src="../js/select2.min.js"></script>
    <style>
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        form div {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="number"], select, .select2-container {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
        }
        .select2-container {
            width: 100% !important;
        }
        button {
            grid-column: 1 / -1;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
            width: 200px;
            margin: 0 auto;
        }
        button:hover {
            background-color: #0056b3;
        }
        .actions button {
            padding: 5px 10px;
            width: auto;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .actions a {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }
        .actions .edit {
            background-color: #ffc107;
        }
        .actions .delete {
            background-color: #dc3545;
        }
        .import-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }
        .import-form div {
            display: flex;
            flex-direction: column;
        }
        .import-form input[type="file"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        .import-form button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
            width: 200px;
            margin: 0 auto;
        }
        .import-form button:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
</head><body>
<?php require '../etc/menu.php'; ?>
    <div class="container">
        <h1>Διαχείριση Συμβούλων Επιστημονικής Ευθύνης</h1>
        <?php if (isset($_SESSION['import_message'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['import_message']); ?></div>
        <?php unset($_SESSION['import_message']); ?>
        <?php endif; ?>

        <h2>Καταχωρημένοι Σύμβουλοι</h2>
        <table>
            <thead>
                <tr>
                    <th>Κλάδος</th>
                    <th>ΑΦΜ</th>
                    <th>Επώνυμο</th>
                    <th>Όνομα</th>
                    <th>EMP ID</th>
                    <th>Σχολεία Ευθύνης</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($advisors as $advisor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($klados_options[$advisor['klados']] ?? $advisor['klados']); ?></td>
                        <td><?php echo htmlspecialchars($advisor['afm']); ?></td>
                        <td><?php echo htmlspecialchars($advisor['eponymo']); ?></td>
                        <td><?php echo htmlspecialchars($advisor['onoma']); ?></td>
                        <td><?php echo htmlspecialchars($advisor['emp_id'] ?? '-'); ?></td>
                        <td>
                            <?php
                                if (!empty($advisor['sch_ids'])) {
                                    $sch_names = [];
                                    foreach (explode(',', $advisor['sch_ids']) as $sch_id) {
                                        $found_school = array_filter($schools, fn($s) => $s['id'] == $sch_id);
                                        if (!empty($found_school)) {
                                            $sch_names[] = htmlspecialchars(reset($found_school)['name']);
                                        }
                                    }
                                    echo implode(', ', $sch_names);
                                } else {
                                    echo 'Όλα';
                                }
                            ?>
                        </td>
                        <td class="actions">
                            <a href="symvouloi_epist.php?edit=<?php echo $advisor['id']; ?>" class="edit btn btn-yellow">Επεξεργασία</a>
                            <form method="POST" action="symvouloi_epist.php" style="display:inline-block;">
                                <input type="hidden" name="delete_id" value="<?php echo $advisor['id']; ?>">
                                <button type="submit" class="delete btn btn-red" onclick="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον σύμβουλο;');">Διαγραφή</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($advisors)): ?>
                    <tr>
                        <td colspan="7">Δεν υπάρχουν καταχωρημένοι σύμβουλοι.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <h2><?php echo $editing_advisor ? 'Επεξεργασία Συμβούλου' : 'Προσθήκη Νέου Συμβούλου'; ?></h2>
        <form method="POST" action="symvouloi_epist.php">
            <?php if ($editing_advisor): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editing_advisor['id']); ?>">
            <?php endif; ?>
            <div>
                <label for="klados">Κλάδος:</label>
                <select id="klados" name="klados" required class="select2-klados">
                    <option value="">Επιλέξτε Κλάδο</option>
                    <?php foreach ($klados_options as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($editing_advisor && $editing_advisor['klados'] == $value) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="afm">ΑΦΜ:</label>
                <input type="text" id="afm" name="afm" value="<?php echo htmlspecialchars($editing_advisor['afm'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="eponymo">Επώνυμο:</label>
                <input type="text" id="eponymo" name="eponymo" value="<?php echo htmlspecialchars($editing_advisor['eponymo'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="onoma">Όνομα:</label>
                <input type="text" id="onoma" name="onoma" value="<?php echo htmlspecialchars($editing_advisor['onoma'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="emp_id">EMP ID (Προαιρετικό):</label>
                <input type="number" id="emp_id" name="emp_id" value="<?php echo htmlspecialchars($editing_advisor['emp_id'] ?? ''); ?>">
            </div>
            <div>
                <label for="sch_ids">Σχολεία Ευθύνης (Όλα αν κενό):</label>
                <select id="sch_ids" name="sch_ids[]" multiple="multiple">
                    <?php foreach ($schools as $school): ?>
                        <option value="<?php echo htmlspecialchars($school['id']); ?>"
                            <?php echo ($editing_advisor && in_array($school['id'], $editing_advisor['sch_ids'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($school['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class='btn btn-green'><?php echo $editing_advisor ? 'Ενημέρωση Συμβούλου' : 'Προσθήκη Συμβούλου'; ?></button>
        </form>

        <h2>Εισαγωγή από CSV</h2>
        <form method="POST" action="symvouloi_epist.php" enctype="multipart/form-data" class="import-form">
            <div>
                <label for="csv_file">Επιλέξτε CSV αρχείο:</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                <p>CSV format: κλάδος (π.χ. ΠΕ70), ΑΦΜ, επώνυμο, όνομα, Α/Α υπαλλήλου (προαιρετικό), Α/Α σχολείων (διαχωρισμένα με κόμμα - προαιρετικό)</p>
            </div>
            <button type="submit" class='btn btn-blue'>Εισαγωγή CSV</button>
        </form>
    </div>

    <form>
    <INPUT TYPE='button' class='btn btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'" style="padding: 5px 10px; font-size: 0.9rem; width: 125px; height: 45px;">
    </form>

    <script>
        $(document).ready(function() {
            $('#sch_ids').select2({
                placeholder: "Επιλέξτε Σχολεία",
                allowClear: true
            });
            $('.select2-klados').select2({
                placeholder: "Επιλέξτε Κλάδο",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
</body>
</html>
