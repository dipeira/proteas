<?php
    header('Content-type: text/html; charset=utf-8'); 
    require_once"../config.php";
    require "../tools/class.login.php";
    
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false) {
        header("Location: ../tools/login.php");
    }
?>
<!DOCTYPE html>
<html>
<head>
    <?php 
    $root_path = '../';
    $page_title = 'Σχετικά';
    require '../etc/head.php'; 
    ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .info-section {
            background: white;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .info-section h3 {
            margin-top: 0;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
            font-size: 1rem;
            font-weight: 600;
        }
        .info-section p {
            margin: 8px 0;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #374151;
        }
        .tech-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 8px;
            margin: 12px 0;
            padding: 0;
            list-style: none;
        }
        .tech-list li {
            padding: 8px 12px;
            background: #f9fafb;
            border-radius: 4px;
            border-left: 3px solid #3b82f6;
            font-size: 0.8125rem;
            transition: background-color 0.2s;
        }
        .tech-list li:hover {
            background: #f3f4f6;
        }
        .tech-list a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .tech-list a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .contact-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #eff6ff;
            border-radius: 6px;
            margin: 12px 0;
        }
        .contact-info a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .contact-info a:hover {
            text-decoration: underline;
        }
        .github-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #1f2937;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: background-color 0.2s;
        }
        .github-link:hover {
            background: #111827;
        }
        .github-link img {
            width: 20px;
            height: 20px;
        }
        .btn-red {
            background: #ef4444;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-red:hover {
            background: #dc2626;
        }
        .button-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .highlight {
            color: #3b82f6;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .info-section {
                padding: 10px;
            }
            .tech-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="p-4 md:p-6 lg:p-8">
    <?php require '../etc/menu.php'; ?>
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Σχετικά</h2>
        
        <div class="info-section">
            <h3>📋 Περιγραφή</h3>
            <p>
                Το project <span class="highlight">Πρωτέας</span> σχεδιάστηκε και αναπτύσσεται από τον εκπαιδευτικό Πληροφορικής Δ.Ε. ΠΕ20 Βαγγέλη Ζαχαριουδάκη 
                από το σχολικό έτος 2011-2012 έως σήμερα, για λογαριασμό της Δ/νσης Πρωτοβάθμιας Εκπαίδευσης Ν.Ηρακλείου.
            </p>
            <p>
                Η ανάπτυξη γίνεται εξ'ολοκλήρου με <a href="http://www.gnu.org/philosophy/free-sw.html" target="_blank" class="text-blue-600 hover:underline">λογισμικό ανοιχτού κώδικα (open-source)</a>.
            </p>
        </div>

        <div class="info-section">
            <h3>⚙️ Βασικές Τεχνολογίες</h3>
            <p style="margin-bottom: 8px;">Τα εργαλεία που χρησιμοποιούνται είναι:</p>
            <div class="tech-list">
                <li><a href="http://www.php.net/" target="_blank">PHP</a> - Γλώσσα προγραμματισμού</li>
                <li><a href="http://www.mysql.com/" target="_blank">MySQL</a> - Βάση δεδομένων</li>
                <li><a href="http://www.apache.org/" target="_blank">Apache</a> - Web Server</li>
            </div>
        </div>

        <div class="info-section">
            <h3>📚 Βιβλιοθήκες & Εργαλεία</h3>
            <ul class="tech-list">
                <li><a href="https://jquery.com/" target="_blank">jQuery</a></li>
                <li><a href="https://datatables.net/" target="_blank">DataTables</a></li>
                <li><a href="http://www.triconsole.com/php/calendar_datepicker.php" target="_blank">PHP Calendar Date Picker</a></li>
                <li><a href="https://select2.org/" target="_blank">Select2</a></li>
                <li><a href="https://github.com/jmosbech/StickyTableHeaders" target="_blank">StickyTableHeaders</a></li>
                <li><a href="https://www.linux.com/news/quickly-put-data-mysql-web-drasticgrid" target="_blank">DrasticGrid</a></li>
                <li><a href="https://github.com/PHPOffice/PHPExcel" target="_blank">PHPExcel</a></li>
                <li><a href="https://github.com/PHPOffice/PHPWord" target="_blank">PHPWord</a></li>
                <li><a href="https://swiftmailer.symfony.com/" target="_blank">SwiftMailer</a></li>
                <li><a href="http://www.emirplicanic.com/php/simple-phpmysql-authentication-class" target="_blank">Simple PHP/MySQL authentication/login class</a></li>
                <li><a href="https://code.tutsplus.com/tutorials/how-to-paginate-data-with-php--net-2928" target="_blank">Paginator</a></li>
            </ul>
        </div>

        <div class="info-section">
            <h3>📧 Επικοινωνία</h3>
            <div class="contact-info">
                <span>✉️ Επικοινωνία με το δημιουργό:</span>
                <a href="mailto:sugarv@sch.gr">sugarv@sch.gr</a>
            </div>
            <div style="margin-top: 12px;">
                <a href='https://github.com/dipeira/proteas' target="_blank" class="github-link">
                    <img src="../images/github.png" alt="GitHub">
                    <span>Ο Πρωτέας στο GitHub</span>
                </a>
            </div>
        </div>

        <div class="button-group">
            <button type="button" class="btn-red" onClick="parent.location='../index.php'">← Επιστροφή</button>
        </div>

        <?php
        print_latest_commits(15);
        ?>
    </div>
</body>
</html>
