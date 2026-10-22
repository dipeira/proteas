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

// Definition of protected core system parameters
$core_params = [
    'sxol_etos' => [
        'title' => 'Σχολικό Έτος',
        'descr' => 'Σχολικό έτος σε μορφή 6 ψηφίων (π.χ. 202627). Πρέπει να ενημερώνεται κάθε 1η Σεπτεμβρίου.',
        'placeholder' => 'π.χ. 202627'
    ],
    'head_title' => [
        'title' => 'Τίτλος Προϊσταμένου/Διευθυντή',
        'descr' => 'Ο επίσημος τίτλος που τυπώνεται στις βεβαιώσεις και στις αποφάσεις (π.χ. Ο Δ/ντής Π.Ε. Ηρακλείου).',
        'placeholder' => 'π.χ. Ο Δ/ντής Π.Ε. Ηρακλείου'
    ],
    'head_name' => [
        'title' => 'Ονοματεπώνυμο Διευθυντή',
        'descr' => 'Το ονοματεπώνυμο του/της Διευθυντή/ντριας (π.χ. Μπελαδάκης Εμμανουήλ).',
        'placeholder' => 'π.χ. Μπελαδάκης Εμμανουήλ'
    ],
    'endofyear' => [
        'title' => 'Ημερομηνία Έκδοσης Βεβαιώσεων Αναπληρωτών',
        'descr' => 'Ημερομηνία έκδοσης βεβαιώσεων προϋπηρεσίας αναπληρωτών στο τέλος της χρονιάς (μορφή ΗΗ-ΜΜ-ΕΕΕΕ).',
        'placeholder' => 'π.χ. 21-06-2027'
    ],
    'endofyear2' => [
        'title' => 'Τελευταία Ημέρα Εργασίας Αναπληρωτών',
        'descr' => 'Τελευταία ημέρα σύμβασης / υπηρεσίας αναπληρωτών για τις βεβαιώσεις (μορφή ΗΗ-ΜΜ-ΕΕΕΕ).',
        'placeholder' => 'π.χ. 21-06-2027'
    ],
    'protapol' => [
        'title' => 'Πρωτόκολλο Απόλυσης',
        'descr' => 'Αριθμός πρωτοκόλλου απόλυσης αναπληρωτών για τη λήξη διδακτικού έτους.',
        'placeholder' => 'π.χ. 99999'
    ],
    'yp_wr' => [
        'title' => 'Υποχρεωτικό Ωράριο Βαθμίδας',
        'descr' => 'Βασικό εβδομαδιαίο υποχρεωτικό διδακτικό ωράριο βαθμίδας (π.χ. 24).',
        'placeholder' => 'π.χ. 24'
    ],
    'foreas' => [
        'title' => 'Επίσημη Ονομασία Φορέα',
        'descr' => 'Ονομασία της Διεύθυνσης / Φορέα για τίτλους, έγγραφα και αποστολή email.',
        'placeholder' => 'π.χ. Δ/νση Π.Ε. Ηρακλείου'
    ],
    'email' => [
        'title' => 'Email Αποστολέα Ειδοποιήσεων',
        'descr' => 'Διεύθυνση email αποστολέα για αυτόματες ειδοποιήσεις και αποφάσεις του συστήματος.',
        'placeholder' => 'π.χ. no-reply@dipeira.gov.gr'
    ],
    'perioxh' => [
        'title' => 'Περιοχή Διεύθυνσης',
        'descr' => 'Ονομασία περιοχής διεύθυνσης για αναφορές και εισαγωγή αναπληρωτών (π.χ. ΗΡΑΚΛΕΙΟΥ (Π.Ε.)).',
        'placeholder' => 'π.χ. ΗΡΑΚΛΕΙΟΥ (Π.Ε.)'
    ],
    'dnsh' => [
        'title' => 'Συντομογραφία Διεύθυνσης',
        'descr' => 'Σύντομος τίτλος διεύθυνσης που χρησιμοποιείται σε τίτλους και στατιστικά.',
        'placeholder' => 'π.χ. Π.Ε. Ηρακλείου'
    ]
];

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['is_ajax']);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Save / Update Parameter
    if ($action === 'save') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $value = isset($_POST['value']) ? (string)$_POST['value'] : '';
        $descr = isset($_POST['descr']) ? (string)$_POST['descr'] : '';

        if ($id <= 0) {
            $err = 'Μη έγκυρο αναγνωριστικό παραμέτρου.';
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_message'] = $err;
            $_SESSION['flash_type'] = 'danger';
            header('Location: params.php');
            exit();
        }

        try {
            $stmt = $db->prepare('UPDATE params SET value = :value, descr = :descr WHERE id = :id');
            $stmt->execute([
                ':value' => $value,
                ':descr' => $descr,
                ':id' => $id
            ]);

            // Fetch updated row
            $stmt = $db->prepare('SELECT id, name, value, descr FROM params WHERE id = ?');
            $stmt->execute([$id]);
            $updatedItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($is_ajax) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Η παράμετρος αποθηκεύτηκε επιτυχώς!',
                    'data' => $updatedItem
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_message'] = 'Η παράμετρος αποθηκεύτηκε επιτυχώς!';
            $_SESSION['flash_type'] = 'success';
            header('Location: params.php');
            exit();
        } catch (PDOException $e) {
            $err = 'Σφάλμα κατά την αποθήκευση: ' . $e->getMessage();
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_message'] = $err;
            $_SESSION['flash_type'] = 'danger';
            header('Location: params.php');
            exit();
        }
    }

    // Add New Parameter
    if ($action === 'add') {
        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
        $value = isset($_POST['value']) ? (string)$_POST['value'] : '';
        $descr = isset($_POST['descr']) ? (string)$_POST['descr'] : '';

        // Validation: name format
        if (!preg_match('/^[a-zA-Z0-9_]{2,50}$/', $name)) {
            $err = 'Το όνομα της παραμέτρου πρέπει να περιέχει μόνο λατινικούς χαρακτήρες, αριθμούς και κάτω παύλες (2-50 χαρακτήρες).';
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_message'] = $err;
            $_SESSION['flash_type'] = 'danger';
            header('Location: params.php');
            exit();
        }

        // Check if name already exists
        $stmt = $db->prepare('SELECT COUNT(*) FROM params WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) {
            $err = "Υπάρχει ήδη παράμετρος με το όνομα '$name'. Επιλέξτε διαφορετικό όνομα.";
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_message'] = $err;
            $_SESSION['flash_type'] = 'danger';
            header('Location: params.php');
            exit();
        }

        try {
            $stmt = $db->prepare('INSERT INTO params (name, value, descr) VALUES (:name, :value, :descr)');
            $stmt->execute([
                ':name' => $name,
                ':value' => $value,
                ':descr' => $descr
            ]);
            $newId = $db->lastInsertId();

            $stmt = $db->prepare('SELECT id, name, value, descr FROM params WHERE id = ?');
            $stmt->execute([$newId]);
            $newItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($is_ajax) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Η νέα παράμετρος προστέθηκε επιτυχώς!',
                    'data' => $newItem
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_message'] = 'Η νέα παράμετρος προστέθηκε επιτυχώς!';
            $_SESSION['flash_type'] = 'success';
            header('Location: params.php');
            exit();
        } catch (PDOException $e) {
            $err = 'Σφάλμα κατά την προσθήκη: ' . $e->getMessage();
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_message'] = $err;
            $_SESSION['flash_type'] = 'danger';
            header('Location: params.php');
            exit();
        }
    }

    // Delete Parameter
    if ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $stmt = $db->prepare('SELECT name FROM params WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $err = 'Η παράμετρος δεν βρέθηκε.';
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_message'] = $err;
            $_SESSION['flash_type'] = 'danger';
            header('Location: params.php');
            exit();
        }

        if (array_key_exists($row['name'], $core_params)) {
            $err = "Δεν επιτρέπεται η διαγραφή της βασικής παραμέτρου '{$row['name']}'.";
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_message'] = $err;
            $_SESSION['flash_type'] = 'danger';
            header('Location: params.php');
            exit();
        }

        try {
            $stmt = $db->prepare('DELETE FROM params WHERE id = ?');
            $stmt->execute([$id]);

            if ($is_ajax) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Η παράμετρος διαγράφηκε επιτυχώς!'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $_SESSION['flash_message'] = 'Η παράμετρος διαγράφηκε επιτυχώς!';
            $_SESSION['flash_type'] = 'success';
            header('Location: params.php');
            exit();
        } catch (PDOException $e) {
            $err = 'Σφάλμα κατά τη διαγραφή: ' . $e->getMessage();
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $err], JSON_UNESCAPED_UNICODE);
                exit();
            }
            $_SESSION['flash_message'] = $err;
            $_SESSION['flash_type'] = 'danger';
            header('Location: params.php');
            exit();
        }
    }
}

// Fetch all parameters from database
$stmt = $db->query('SELECT id, name, value, descr FROM params ORDER BY id ASC');
$paramsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash message
$flash_message = $_SESSION['flash_message'] ?? null;
$flash_type = $_SESSION['flash_type'] ?? 'info';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <?php
    $root_path = '../';
    $page_title = 'Διαχείριση Παραμέτρων';
    require '../etc/head.php';
    ?>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <style>
        /* Smooth transitions & custom tweaks */
        .param-row {
            transition: background-color 0.2s ease, transform 0.15s ease;
        }
        .param-row:hover {
            background-color: #f8fafc;
        }
        .param-row.row-updated {
            animation: highlightUpdated 1.8s ease-out;
        }
        @keyframes highlightUpdated {
            0% { background-color: #dcfce7; }
            50% { background-color: #f0fdf4; }
            100% { background-color: transparent; }
        }
        .code-chip {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .val-preview {
            white-space: pre-wrap;
            word-break: break-word;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.85rem;
            line-height: 1.45;
            max-height: 110px;
            overflow-y: auto;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            color: #1e293b;
        }
        .badge-core {
            background-color: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 2px 8px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .badge-custom {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 2px 8px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        /* Modal styling */
        .modal-backdrop {
            background-color: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
        }
        /* Custom scrollbars */
        .val-preview::-webkit-scrollbar,
        textarea::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .val-preview::-webkit-scrollbar-thumb,
        textarea::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .val-preview::-webkit-scrollbar-thumb:hover,
        textarea::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">
<?php require '../etc/menu.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Breadcrumb / Page Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-600 shadow-sm shrink-0">
                    <!-- Sliders / Controls Icon -->
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Διαχείριση Παραμέτρων</h1>
                        <span id="paramTotalBadge" class="bg-indigo-100 text-indigo-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-indigo-200">
                            <?= count($paramsList); ?> παράμετροι
                        </span>
                    </div>
                    <p class="text-slate-500 text-sm mt-1">
                        Κεντρική διαμόρφωση παραμέτρων συστήματος, τίτλων εγγράφων, ημερομηνιών και επιλογών λειτουργίας του Πρωτέα.
                    </p>
                </div>
            </div>

            <!-- Action Buttons Toolbar -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm transition duration-150 ease-in-out cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Νέα Παράμετρος</span>
                </button>

                <a href="../tools/test_email.php" target="_blank" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-medium text-sm px-3.5 py-2.5 rounded-xl shadow-sm transition duration-150 ease-in-out" title="Έλεγχος αποστολής δοκιμαστικού email">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Δοκιμή Email</span>
                </a>

                <a href="../index.php" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-medium text-sm px-3.5 py-2.5 rounded-xl border border-slate-200 transition duration-150 ease-in-out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Επιστροφή</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Message Notification -->
    <?php if ($flash_message): ?>
        <div id="flashAlert" class="mb-6 p-4 rounded-xl border flex items-center justify-between gap-3 shadow-sm <?= $flash_type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($flash_type === 'danger' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-blue-50 border-blue-200 text-blue-800'); ?>">
            <div class="flex items-center gap-3">
                <?php if ($flash_type === 'success'): ?>
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                <?php else: ?>
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                <?php endif; ?>
                <span class="text-sm font-medium"><?= htmlspecialchars($flash_message); ?></span>
            </div>
            <button type="button" onclick="document.getElementById('flashAlert').remove()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Search Input -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="searchInput" oninput="filterTable()" placeholder="Αναζήτηση παραμέτρου κατά όνομα, τιμή ή περιγραφή..." class="w-full pl-10 pr-9 py-2.5 bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150">
                <button type="button" id="clearSearchBtn" onclick="clearSearch()" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Filter Buttons & Count -->
            <div class="flex items-center gap-2">
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-medium">
                    <button type="button" onclick="setCategoryFilter('all')" id="filterBtnAll" class="px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-semibold transition">
                        Όλες
                    </button>
                    <button type="button" onclick="setCategoryFilter('core')" id="filterBtnCore" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                        Βασικές
                    </button>
                    <button type="button" onclick="setCategoryFilter('custom')" id="filterBtnCustom" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                        Προσαρμοσμένες
                    </button>
                </div>

                <div class="text-xs text-slate-500 font-medium whitespace-nowrap pl-2">
                    <span id="filteredCount"><?= count($paramsList); ?></span> από <?= count($paramsList); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="paramsTable">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-14 text-center">#</th>
                        <th class="py-3.5 px-4 w-52">Όνομα Παραμέτρου</th>
                        <th class="py-3.5 px-4 min-w-[280px]">Τιμή (Value)</th>
                        <th class="py-3.5 px-4 min-w-[240px]">Περιγραφή</th>
                        <th class="py-3.5 px-4 w-32 text-center">Ενέργειες</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (empty($paramsList)): ?>
                        <tr id="emptyTableRow">
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                Δεν βρέθηκαν παράμετροι.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($paramsList as $item): 
                            $isCore = array_key_exists($item['name'], $core_params);
                            $coreMeta = $isCore ? $core_params[$item['name']] : null;
                        ?>
                            <tr class="param-row" id="row-<?= $item['id']; ?>" data-id="<?= $item['id']; ?>" data-name="<?= htmlspecialchars($item['name']); ?>" data-is-core="<?= $isCore ? '1' : '0'; ?>">
                                <!-- ID Column -->
                                <td class="py-3.5 px-4 text-center text-xs text-slate-400 font-mono">
                                    <?= $item['id']; ?>
                                </td>

                                <!-- Name Column -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="flex flex-col items-start gap-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="code-chip font-bold">
                                                <?= htmlspecialchars($item['name']); ?>
                                            </span>
                                            <button type="button" onclick="copyToClipboard('<?= htmlspecialchars(addslashes($item['name'])); ?>', this)" class="text-slate-400 hover:text-slate-600 p-1 rounded hover:bg-slate-100 transition" title="Αντιγραφή ονόματος">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <?php if ($isCore): ?>
                                            <span class="badge-core">Βασικη</span>
                                        <?php else: ?>
                                            <span class="badge-custom">Προσαρμοσμενη</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Value Column (Text supported) -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="relative group">
                                        <div class="val-preview" id="val-display-<?= $item['id']; ?>"><?php 
                                            if ($item['value'] === '' || $item['value'] === null) {
                                                echo '<span class="text-slate-400 italic font-sans">(κενό)</span>';
                                            } else {
                                                echo htmlspecialchars($item['value']);
                                            }
                                        ?></div>

                                        <!-- Copy value button floating on hover -->
                                        <?php if ($item['value'] !== '' && $item['value'] !== null): ?>
                                            <div class="mt-1 flex items-center justify-between text-xs text-slate-400">
                                                <span><?= mb_strlen($item['value'], 'UTF-8'); ?> χαρακτήρες</span>
                                                <button type="button" onclick="copyParamValue(<?= $item['id']; ?>, this)" class="inline-flex items-center gap-1 text-slate-500 hover:text-indigo-600 font-medium transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>Αντιγραφή τιμής</span>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Description Column (Text supported) -->
                                <td class="py-3.5 px-4 align-top text-slate-600 text-sm">
                                    <div id="descr-display-<?= $item['id']; ?>" class="whitespace-pre-line leading-relaxed">
                                        <?php if (!empty($item['descr'])): ?>
                                            <?= htmlspecialchars($item['descr']); ?>
                                        <?php elseif ($isCore && !empty($coreMeta['descr'])): ?>
                                            <span class="text-slate-400 italic"><?= htmlspecialchars($coreMeta['descr']); ?></span>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">(Χωρίς περιγραφή)</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($isCore && !empty($coreMeta['title'])): ?>
                                        <div class="text-xs text-indigo-600 font-medium mt-1">
                                            <?= htmlspecialchars($coreMeta['title']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions Column -->
                                <td class="py-3.5 px-4 align-top text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Edit button -->
                                        <button type="button" onclick="openEditModal(<?= $item['id']; ?>)" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium text-xs rounded-lg border border-indigo-200/80 transition duration-150 cursor-pointer" title="Επεξεργασία παραμέτρου">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            <span>Επεξεργασία</span>
                                        </button>

                                        <!-- Delete button (only for custom non-core params) -->
                                        <?php if (!$isCore): ?>
                                            <button type="button" onclick="openDeleteModal(<?= $item['id']; ?>, '<?= htmlspecialchars(addslashes($item['name'])); ?>')" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition duration-150 cursor-pointer" title="Διαγραφή προσαρμοσμένης παραμέτρου">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        <?php else: ?>
                                            <span class="p-1.5 text-slate-300 cursor-not-allowed" title="Βασική παράμετρος συστήματος (δεν διαγράφεται)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- No search results notification -->
        <div id="noResultsMsg" class="hidden py-12 text-center text-slate-400">
            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p class="font-medium text-slate-600">Δεν βρέθηκαν παράμετροι που να ταιριάζουν στην αναζήτηση.</p>
            <button type="button" onclick="clearSearch()" class="mt-2 text-indigo-600 hover:text-indigo-800 text-xs font-semibold">
                Καθαρισμός αναζήτησης
            </button>
        </div>
    </div>

    <!-- Explanatory Guide Card -->
    <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <button type="button" onclick="toggleHelpSection()" class="w-full flex items-center justify-between p-5 text-left bg-slate-50/70 hover:bg-slate-50 transition border-b border-slate-200/80 cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-50 border border-amber-200/80 rounded-lg text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Οδηγός & Επεξήγηση Βασικών Παραμέτρων</h3>
                    <p class="text-xs text-slate-500">Πληροφορίες για το πώς επηρεάζουν οι παράμετροι τη λειτουργία του Πρωτέα</p>
                </div>
            </div>
            <svg id="helpToggleIcon" class="w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div id="helpContent" class="hidden p-6 bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <?php foreach ($core_params as $pName => $pInfo): ?>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="code-chip font-bold text-xs"><?= htmlspecialchars($pName); ?></span>
                            <span class="text-indigo-600 font-semibold text-[11px]"><?= htmlspecialchars($pInfo['title']); ?></span>
                        </div>
                        <p class="text-slate-600 leading-relaxed"><?= htmlspecialchars($pInfo['descr']); ?></p>
                        <?php if (!empty($pInfo['placeholder'])): ?>
                            <div class="mt-2 text-slate-400 font-mono text-[10px]">
                                Παράδειγμα: <?= htmlspecialchars($pInfo['placeholder']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200/70 rounded-xl text-xs text-blue-900 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>
                    <strong>Σημείωση:</strong> Τα πεδία <strong>value</strong> και <strong>description</strong> υποστηρίζουν πλέον κείμενο πλήρους μεγέθους (TEXT), επιτρέποντας την καταχώριση πολυγραμμικών κειμένων, υποδειγμάτων ή αναλυτικών οδηγιών.
                </span>
            </div>
        </div>
    </div>

</main>

<!-- Edit Parameter Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150 my-8">
        <form id="editParamForm" onsubmit="handleSaveParam(event)">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="editParamId" value="">
            <input type="hidden" name="is_ajax" value="1">

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 bg-slate-50 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Επεξεργασία Παραμέτρου</h3>
                        <p class="text-xs text-slate-500">Τροποποιήστε την τιμή και την περιγραφή της παραμέτρου</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/60 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <!-- Parameter Name (Read-only badge) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Όνομα Παραμέτρου</label>
                    <div class="flex items-center gap-2">
                        <span id="editParamNameBadge" class="code-chip font-bold text-sm px-3 py-1.5 bg-slate-100 border-slate-300 text-slate-800">
                            -
                        </span>
                        <span id="editParamCoreBadge" class="badge-core">Βασικη</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Το όνομα της παραμέτρου χρησιμοποιείται στον κώδικα και παραμένει σταθερό.</p>
                </div>

                <!-- Parameter Value (TEXT textarea) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="editParamValue" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Τιμή (Value) <span class="text-indigo-600">*</span>
                        </label>
                        <span id="editValLength" class="text-xs text-slate-400">0 χαρακτήρες</span>
                    </div>
                    <textarea name="value" id="editParamValue" rows="6" oninput="updateEditValCount()" class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150 leading-relaxed" placeholder="Εισάγετε τιμή παραμέτρου..."></textarea>
                    <p class="text-xs text-slate-500 mt-1">Υποστηρίζεται πλήρες κείμενο (TEXT) και πολλαπλές γραμμές.</p>
                </div>

                <!-- Parameter Description (TEXT textarea) -->
                <div>
                    <label for="editParamDescr" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
                        Περιγραφή (Description)
                    </label>
                    <textarea name="descr" id="editParamDescr" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150 leading-relaxed" placeholder="Σύντομη ή αναλυτική περιγραφή της παραμέτρου..."></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-200">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-200/60 rounded-xl transition cursor-pointer">
                    Ακύρωση
                </button>
                <button type="submit" id="saveParamBtn" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition duration-150 cursor-pointer">
                    <svg id="saveBtnIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span id="saveBtnText">Αποθήκευση</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Parameter Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150 my-8">
        <form id="addParamForm" onsubmit="handleAddParam(event)">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="is_ajax" value="1">

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 bg-slate-50 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Προσθήκη Νέας Παραμέτρου</h3>
                        <p class="text-xs text-slate-500">Δημιουργία νέας προσαρμοσμένης παραμέτρου στο σύστημα</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/60 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <!-- Name -->
                <div>
                    <label for="addParamName" class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">
                        Όνομα Παραμέτρου (Identifier) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="addParamName" required pattern="[a-zA-Z0-9_]{2,50}" placeholder="π.χ. custom_setting_name" class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150">
                    <p class="text-xs text-slate-500 mt-1">Επιτρέπονται λατινικοί χαρακτήρες, αριθμοί και underscore (_). Μήκος: 2 έως 50 χαρακτήρες.</p>
                </div>

                <!-- Value -->
                <div>
                    <label for="addParamValue" class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">
                        Τιμή (Value)
                    </label>
                    <textarea name="value" id="addParamValue" rows="4" class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150 leading-relaxed" placeholder="Εισάγετε τιμή (υποστηρίζεται TEXT)..."></textarea>
                </div>

                <!-- Description -->
                <div>
                    <label for="addParamDescr" class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">
                        Περιγραφή (Description)
                    </label>
                    <textarea name="descr" id="addParamDescr" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition duration-150 leading-relaxed" placeholder="Σύντομη περιγραφή της χρησιμότητας της παραμέτρου..."></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-200">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-200/60 rounded-xl transition cursor-pointer">
                    Ακύρωση
                </button>
                <button type="submit" id="addParamBtn" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-sm transition duration-150 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Δημιουργία</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <form id="deleteParamForm" onsubmit="handleDeleteParam(event)">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteParamId" value="">
            <input type="hidden" name="is_ajax" value="1">

            <div class="p-6 text-center">
                <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Διαγραφή Παραμέτρου</h3>
                <p class="text-sm text-slate-500 mb-2">
                    Είστε βέβαιοι ότι θέλετε να διαγράψετε την παράμετρο <strong id="deleteParamNameDisplay" class="font-mono text-slate-800"></strong>;
                </p>
                <p class="text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-2">
                    Προσοχή: Η ενέργεια αυτή δεν μπορεί να αναιρεθεί.
                </p>
            </div>

            <div class="flex items-center justify-center gap-3 px-6 py-4 bg-slate-50 border-t border-slate-200">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-200/60 rounded-xl transition cursor-pointer">
                    Ακύρωση
                </button>
                <button type="submit" id="confirmDeleteBtn" class="px-5 py-2 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 rounded-xl shadow-sm transition duration-150 cursor-pointer">
                    Διαγραφή
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Toast Container -->
<div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm pointer-events-none"></div>

<script>
// Parameters data in memory
const paramsData = <?= json_encode($paramsList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
const coreParamsKeys = <?= json_encode(array_keys($core_params)); ?>;
let activeCategoryFilter = 'all';

// Toast Notification Helper
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const isSuccess = type === 'success';

    toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium transition-all duration-300 transform translate-y-2 opacity-0 ${
        isSuccess ? 'bg-emerald-800 text-white border-emerald-900' : 'bg-rose-800 text-white border-rose-900'
    }`;

    toast.innerHTML = `
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            ${isSuccess 
                ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>'
                : '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>'
            }
        </svg>
        <span>${message}</span>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

// Copy to Clipboard Helper
function copyToClipboard(text, btnElement) {
    if (!navigator.clipboard) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    } else {
        navigator.clipboard.writeText(text);
    }

    if (btnElement) {
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = `
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
        `;
        setTimeout(() => {
            btnElement.innerHTML = originalHtml;
        }, 1600);
    }

    showToast('Αντιγράφηκε στο πρόχειρο!', 'success');
}

// Copy Parameter Value
function copyParamValue(id, btnElement) {
    const item = paramsData.find(p => p.id == id);
    if (item && item.value) {
        copyToClipboard(item.value, btnElement);
    }
}

// Filter and Search Logic
function filterTable() {
    const query = (document.getElementById('searchInput').value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('#paramsTable tbody tr.param-row');
    const clearBtn = document.getElementById('clearSearchBtn');
    
    if (clearBtn) {
        clearBtn.classList.toggle('hidden', query.length === 0);
    }

    let visibleCount = 0;

    rows.forEach(row => {
        const id = row.getAttribute('data-id');
        const item = paramsData.find(p => p.id == id);
        if (!item) return;

        const isCore = coreParamsKeys.includes(item.name);
        
        // Category filter check
        let matchesCategory = true;
        if (activeCategoryFilter === 'core' && !isCore) matchesCategory = false;
        if (activeCategoryFilter === 'custom' && isCore) matchesCategory = false;

        // Search text check
        let matchesQuery = true;
        if (query.length > 0) {
            const name = (item.name || '').toLowerCase();
            const val = (item.value || '').toLowerCase();
            const descr = (item.descr || '').toLowerCase();
            matchesQuery = name.includes(query) || val.includes(query) || descr.includes(query);
        }

        const shouldShow = matchesCategory && matchesQuery;
        row.style.display = shouldShow ? '' : 'none';
        if (shouldShow) visibleCount++;
    });

    document.getElementById('filteredCount').textContent = visibleCount;
    const noResultsMsg = document.getElementById('noResultsMsg');
    if (noResultsMsg) {
        noResultsMsg.classList.toggle('hidden', visibleCount > 0);
    }
}

function clearSearch() {
    const input = document.getElementById('searchInput');
    input.value = '';
    filterTable();
    input.focus();
}

function setCategoryFilter(category) {
    activeCategoryFilter = category;
    
    // Update button states
    ['all', 'core', 'custom'].forEach(cat => {
        const btn = document.getElementById('filterBtn' + cat.charAt(0).toUpperCase() + cat.slice(1));
        if (cat === category) {
            btn.className = 'px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-900 font-semibold transition';
        } else {
            btn.className = 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition';
        }
    });

    filterTable();
}

// Toggle Help Section
function toggleHelpSection() {
    const content = document.getElementById('helpContent');
    const icon = document.getElementById('helpToggleIcon');
    const isHidden = content.classList.contains('hidden');

    content.classList.toggle('hidden', !isHidden);
    icon.style.transform = isHidden ? 'rotate(180deg)' : '';
}

// Edit Modal Functions
function openEditModal(id) {
    const item = paramsData.find(p => p.id == id);
    if (!item) return;

    document.getElementById('editParamId').value = item.id;
    document.getElementById('editParamNameBadge').textContent = item.name;
    document.getElementById('editParamValue').value = item.value || '';
    document.getElementById('editParamDescr').value = item.descr || '';

    const isCore = coreParamsKeys.includes(item.name);
    const coreBadge = document.getElementById('editParamCoreBadge');
    if (isCore) {
        coreBadge.className = 'badge-core';
        coreBadge.textContent = 'Βασικη';
    } else {
        coreBadge.className = 'badge-custom';
        coreBadge.textContent = 'Προσαρμοσμενη';
    }

    updateEditValCount();
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editParamValue').focus();
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function updateEditValCount() {
    const val = document.getElementById('editParamValue').value;
    document.getElementById('editValLength').textContent = `${val.length} χαρακτήρες`;
}

// Handle AJAX Save
function handleSaveParam(event) {
    event.preventDefault();
    const form = document.getElementById('editParamForm');
    const formData = new FormData(form);
    const saveBtn = document.getElementById('saveParamBtn');
    const saveBtnText = document.getElementById('saveBtnText');

    saveBtn.disabled = true;
    saveBtnText.textContent = 'Αποθήκευση...';

    fetch('params.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtnText.textContent = 'Αποθήκευση';

        if (data.success) {
            closeEditModal();
            showToast(data.message, 'success');

            // Update in-memory data
            const item = paramsData.find(p => p.id == data.data.id);
            if (item) {
                item.value = data.data.value;
                item.descr = data.data.descr;
            }

            // Update DOM row
            const valBox = document.getElementById(`val-display-${data.data.id}`);
            if (valBox) {
                if (!data.data.value) {
                    valBox.innerHTML = '<span class="text-slate-400 italic font-sans">(κενό)</span>';
                } else {
                    valBox.textContent = data.data.value;
                }
            }

            const descrBox = document.getElementById(`descr-display-${data.data.id}`);
            if (descrBox) {
                descrBox.textContent = data.data.descr || '(Χωρίς περιγραφή)';
            }

            // Highlight updated row
            const row = document.getElementById(`row-${data.data.id}`);
            if (row) {
                row.classList.remove('row-updated');
                void row.offsetWidth; // trigger reflow
                row.classList.add('row-updated');
            }
        } else {
            showToast(data.message || 'Σφάλμα κατά την αποθήκευση.', 'danger');
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        saveBtnText.textContent = 'Αποθήκευση';
        showToast('Σφάλμα δικτύου κατά την αποθήκευση.', 'danger');
    });
}

// Add Modal Functions
function openAddModal() {
    document.getElementById('addParamForm').reset();
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('addParamName').focus();
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

function handleAddParam(event) {
    event.preventDefault();
    const form = document.getElementById('addParamForm');
    const formData = new FormData(form);
    const addBtn = document.getElementById('addParamBtn');

    addBtn.disabled = true;

    fetch('params.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        addBtn.disabled = false;
        if (data.success) {
            closeAddModal();
            // Reload page to display new row with all actions cleanly
            window.location.reload();
        } else {
            showToast(data.message || 'Σφάλμα κατά την προσθήκη.', 'danger');
        }
    })
    .catch(err => {
        addBtn.disabled = false;
        showToast('Σφάλμα δικτύου κατά την προσθήκη.', 'danger');
    });
}

// Delete Modal Functions
function openDeleteModal(id, name) {
    document.getElementById('deleteParamId').value = id;
    document.getElementById('deleteParamNameDisplay').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function handleDeleteParam(event) {
    event.preventDefault();
    const form = document.getElementById('deleteParamForm');
    const formData = new FormData(form);
    const delBtn = document.getElementById('confirmDeleteBtn');
    const id = document.getElementById('deleteParamId').value;

    delBtn.disabled = true;

    fetch('params.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        delBtn.disabled = false;
        if (data.success) {
            closeDeleteModal();
            showToast(data.message, 'success');

            // Remove from memory and DOM
            const idx = paramsData.findIndex(p => p.id == id);
            if (idx !== -1) paramsData.splice(idx, 1);

            const row = document.getElementById(`row-${id}`);
            if (row) {
                row.remove();
            }

            document.getElementById('paramTotalBadge').textContent = `${paramsData.length} παράμετροι`;
            filterTable();
        } else {
            showToast(data.message || 'Σφάλμα κατά τη διαγραφή.', 'danger');
        }
    })
    .catch(err => {
        delBtn.disabled = false;
        showToast('Σφάλμα δικτύου κατά τη διαγραφή.', 'danger');
    });
}

// Keyboard shortcuts (Escape closes modals)
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
        closeAddModal();
        closeDeleteModal();
    }
});
</script>

</body>
</html>
