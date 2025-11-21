<?php
header('Content-type: text/html; charset=utf-8');
require_once"../config.php";
require_once"../include/functions.php";
?>    
<html>
    <head>
        <?php 
        $root_path = '../';
        $page_title = 'Στατιστικά Μονίμων / Αναπληρωτών';
        require '../etc/head.php'; 
        ?>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../js/jquery.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <style>
            .stat-card {
                background: white;
                border-radius: 12px;
                padding: 24px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            .stat-number {
                font-size: 2.5rem;
                font-weight: 700;
                color: #1e40af;
                margin: 8px 0;
            }
            .stat-label {
                color: #6b7280;
                font-size: 0.875rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .chart-container {
                position: relative;
                height: 350px;
                margin-top: 20px;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 16px;
                margin-top: 20px;
            }
            .info-item {
                background: #f9fafb;
                padding: 12px 16px;
                border-radius: 8px;
                border-left: 4px solid #3b82f6;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .info-item strong {
                color: #1f2937;
            }
            .info-item .value {
                font-size: 1.25rem;
                font-weight: 600;
                color: #3b82f6;
            }
            @media (max-width: 768px) {
                .chart-container {
                    height: 300px;
                }
                .stat-number {
                    font-size: 2rem;
                }
            }
        </style>
    </head>

    <?php
    require "../tools/class.login.php";
    $log = new logmein();
    if ($log->logincheck($_SESSION['loggedin']) == false) {
        header("Location: ../tools/login.php");
    }
    $usrlvl = $_SESSION['userlevel'];
    // status: 1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    
    // init variables
    $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ',$mysqlconnection);
    $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ',$mysqlconnection);
    $se_forea = getSchoolID('Απόσπαση σε φορέα',$mysqlconnection);

    $query = "SELECT count( * ) FROM employee WHERE status!=2 AND thesi=5";
    $result = mysqli_query($mysqlconnection, $query);
    $idiwtikoi = mysqli_result($result, 0);
    
    $query = "SELECT count( * ) FROM employee WHERE status!=2 AND sx_organikhs NOT IN ($allo_pyspe, $allo_pyspe) AND thesi!=5";
    $result = mysqli_query($mysqlconnection, $query);
    $monimoi_her_total = mysqli_result($result, 0);
    
    $query = "SELECT count( * ) FROM ektaktoi";
    $result = mysqli_query($mysqlconnection, $query);
    $anapl_total = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=1 AND status!=2";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_diath = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=$allo_pyspe AND status!=2 AND sx_yphrethshs NOT IN ($allo_pyspe, $allo_pysde)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_apoallopispe = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=$allo_pysde AND status!=2 AND sx_yphrethshs NOT IN ($allo_pyspe, $allo_pysde)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_apoallopisde = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_yphrethshs=$allo_pyspe AND status!=2 AND sx_organikhs NOT IN ($allo_pyspe, $allo_pysde)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_seallopispe = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_yphrethshs=$se_forea AND status!=2 AND sx_organikhs NOT IN ($allo_pyspe, $allo_pysde)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_seforea = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE STATUS=3";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_seadeia = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE status!=2 AND (sx_organikhs=$allo_pyspe OR sx_organikhs=$allo_pysde) AND sx_yphrethshs NOT IN ($allo_pyspe, $allo_pysde)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_alloy = mysqli_result($result, 0);

    // Prepare data for permanent teachers chart
    $monimoi_data = array();
    $monimoi_labels = array();
    $query = "SELECT COUNT( * ) , k.perigrafh, k.onoma FROM employee e 
                JOIN klados k 
                ON k.id = e.klados 
                WHERE status!=2 AND sx_organikhs NOT IN ($allo_pyspe, $allo_pysde) AND thesi!=5
                GROUP BY klados
                ORDER BY COUNT( * ) DESC";
    $result_mon = mysqli_query($mysqlconnection, $query);
    while ($row = mysqli_fetch_array($result_mon, MYSQLI_NUM)) {
        $monimoi_labels[] = $row[1] . ' (' . $row[2] . ')';
        $monimoi_data[] = $row[0];
    }
    if ($idiwtikoi > 0) {
        $monimoi_labels[] = 'Ιδιωτικοί εκπ/κοί';
        $monimoi_data[] = $idiwtikoi;
    }

    // Prepare data for substitute teachers chart
    $anapl_data = array();
    $anapl_labels = array();
    $anapl_types = array();
    $query = "SELECT COUNT( * ) , k.perigrafh, k.onoma, ek.type
                FROM ektaktoi e
                JOIN klados k ON k.id = e.klados
                JOIN ektaktoi_types ek ON e.type = ek.id
                GROUP BY klados, e.type
                ORDER BY COUNT( * ) DESC";
    $result_anapl = mysqli_query($mysqlconnection, $query);
    while ($row = mysqli_fetch_array($result_anapl, MYSQLI_NUM)) {
        $label = $row[3] . ' - ' . $row[1];
        $anapl_labels[] = $label;
        $anapl_data[] = $row[0];
        if (!in_array($row[3], $anapl_types)) {
            $anapl_types[] = $row[3];
        }
    }

    // Prepare data for schools chart
    $sx_arr = array();
    $query = "SELECT count(*) FROM school WHERE type = 1";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Δημοτικά (Σύνολο)'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 0";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Δημ. Ενεργά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 1 AND type2 = 0";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Δημ. Ανενεργά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 2";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Δημ. Ειδικά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 1";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Δημ. Ιδιωτικά'] = $res;

    $query = "SELECT count(*) FROM school WHERE type = 2";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Νηπιαγωγεία (Σύνολο)'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 0";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Νηπιαγωγεία'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 1 AND type2 = 0";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Νηπ. Ανενεργά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 2";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Νηπ. Ειδικά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 1";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['Νηπ. Ιδιωτικά'] = $res;

    // Prepare data for employee status chart
    $status_labels = array();
    $status_data = array();
    $status_labels[] = 'Υπηρετούν στο ΠΥΣΠΕ Ηρακλείου και έχουν οργανική σε άλλο ΠΥΣΠΕ/ΠΥΣΔΕ';
    $status_data[] = $mon_alloy;
    $status_labels[] = 'Απόσπασμένοι από άλλο ΠΥΣΠΕ';
    $status_data[] = $mon_apoallopispe;
    $status_labels[] = 'Απόσπασμένοι/με διάθεση από άλλο ΠΥΣΔΕ';
    $status_data[] = $mon_apoallopisde;
    $status_labels[] = 'Διάθεση ΠΥΣΠΕ';
    $status_data[] = $mon_diath;
    $status_labels[] = 'Με απόσπαση σε άλλο ΠΥΣΠΕ';
    $status_data[] = $mon_seallopispe;
    $status_labels[] = 'Με απόσπαση σε φορέα';
    $status_data[] = $mon_seforea;
    $status_labels[] = 'Σε άδεια';
    $status_data[] = $mon_seadeia;
    
    // Filter out zero values for status
    $filtered_status_labels = array();
    $filtered_status_data = array();
    for ($i = 0; $i < count($status_data); $i++) {
        if ($status_data[$i] > 0) {
            $filtered_status_labels[] = $status_labels[$i];
            $filtered_status_data[] = $status_data[$i];
        }
    }

    echo "<body class='p-4 md:p-6 lg:p-8'>";
    require '../etc/menu.php';
    
    echo "<div class='max-w-7xl mx-auto mt-6'>";
    echo "<h2 class='text-3xl font-bold text-gray-800 mb-6'>Στατιστικά</h2>";
    
    // Summary Cards
    echo "<div class='grid grid-cols-1 md:grid-cols-3 gap-6 mb-8'>";
    echo "<div class='stat-card'>";
    echo "<div class='stat-label'>Μόνιμοι Εκπαιδευτικοί</div>";
    echo "<div class='stat-number'>" . number_format($monimoi_her_total) . "</div>";
    echo "<div class='text-sm text-gray-600'>Συνολικός αριθμός μόνιμων εκπαιδευτικών</div>";
    echo "</div>";
    echo "<div class='stat-card'>";
    echo "<div class='stat-label'>Αναπληρωτές / Ωρομίσθιοι</div>";
    echo "<div class='stat-number'>" . number_format($anapl_total) . "</div>";
    echo "<div class='text-sm text-gray-600'>Συνολικός αριθμός αναπληρωτών</div>";
    echo "</div>";
    echo "<div class='stat-card'>";
    $total_schools = array_sum($sx_arr);
    echo "<div class='stat-label'>Σχολικές Μονάδες</div>";
    echo "<div class='stat-number'>" . number_format($total_schools) . "</div>";
    echo "<div class='text-sm text-gray-600'>Συνολικός αριθμός σχολείων</div>";
    echo "</div>";
    echo "</div>";
    
    // Permanent Teachers Section
    echo "<div class='grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8'>";
    echo "<div class='stat-card'>";
    echo "<h3 class='text-xl font-semibold text-gray-800 mb-4'>Μόνιμοι εκπαιδευτικοί (με οργανική στη Δ/νση " . getParam('dnsh', $mysqlconnection) . ")</h3>";
    if (count($monimoi_labels) > 0) {
        echo "<div class='chart-container'>";
        echo "<canvas id='monimoiChart'></canvas>";
        echo "</div>";
    } else {
        echo "<p class='text-gray-500 text-center py-8'>Δεν υπάρχουν δεδομένα</p>";
    }
    echo "</div>";
    
    // Employee Status Section
    echo "<div class='stat-card'>";
    echo "<h3 class='text-xl font-semibold text-gray-800 mb-4'>Κατανομή Κατάστασης Εργαζομένων</h3>";
    if (count($filtered_status_data) > 0) {
        echo "<div class='chart-container'>";
        echo "<canvas id='statusChart'></canvas>";
        echo "</div>";
    } else {
        echo "<p class='text-gray-500 text-center py-8'>Δεν υπάρχουν δεδομένα</p>";
    }
    echo "</div>";
    echo "</div>";
    
    // Status Info Grid
    if (count($filtered_status_labels) > 0) {
        echo "<div class='mb-8'>";
        echo "<h3 class='text-xl font-semibold text-gray-800 mb-4'>Λεπτομέρειες Κατάστασης</h3>";
        echo "<div class='info-grid'>";
        for ($i = 0; $i < count($filtered_status_labels); $i++) {
            echo "<div class='info-item'>";
            echo "<strong>" . $filtered_status_labels[$i] . "</strong>";
            echo "<span class='value'>" . number_format($filtered_status_data[$i]) . "</span>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";
    }
    
    // Substitute Teachers Section
    echo "<div class='stat-card mb-8'>";
    echo "<h3 class='text-xl font-semibold text-gray-800 mb-4'>Αναπληρωτές / Ωρομίσθιοι εκπαιδευτικοί</h3>";
    if (count($anapl_labels) > 0) {
        echo "<div class='chart-container'>";
        echo "<canvas id='anaplChart'></canvas>";
        echo "</div>";
    } else {
        echo "<p class='text-gray-500 text-center py-8'>Δεν υπάρχουν δεδομένα</p>";
    }
    echo "</div>";
    
    // Schools Section
    echo "<div class='stat-card mb-8'>";
    echo "<h3 class='text-xl font-semibold text-gray-800 mb-4'>Σχολικές Μονάδες</h3>";
    $sx_labels = array_keys($sx_arr);
    $sx_values = array_values($sx_arr);
    $filtered_sx_labels = array();
    $filtered_sx_values = array();
    for ($i = 0; $i < count($sx_values); $i++) {
        if ($sx_values[$i] > 0) {
            $filtered_sx_labels[] = $sx_labels[$i];
            $filtered_sx_values[] = $sx_values[$i];
        }
    }
    if (count($filtered_sx_values) > 0) {
        echo "<div class='chart-container'>";
        echo "<canvas id='schoolsChart'></canvas>";
        echo "</div>";
        echo "<div class='mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3'>";
        for ($i = 0; $i < count($filtered_sx_labels); $i++) {
            echo "<div class='bg-gray-50 p-3 rounded-lg text-center'>";
            echo "<div class='font-semibold text-gray-800'>" . $filtered_sx_labels[$i] . "</div>";
            echo "<div class='text-2xl font-bold text-blue-600 mt-1'>" . number_format($filtered_sx_values[$i]) . "</div>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p class='text-gray-500 text-center py-8'>Δεν υπάρχουν δεδομένα</p>";
    }
    echo "</div>";
    
    // Back Button
    echo "<div class='text-center mb-8'>";
    echo "<button onclick=\"parent.location='../index.php'\" class='bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-200 shadow-lg hover:shadow-xl'>Επιστροφή</button>";
    echo "</div>";
    
    echo "</div>"; // max-w-7xl
    
    // JavaScript for Charts
    echo "<script>";
    
    // Color palette
    $colors = array(
        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
        '#14b8a6', '#a855f7', '#f43f5e', '#0ea5e9', '#22c55e'
    );
    
    // Permanent Teachers Chart
    if (count($monimoi_labels) > 0) {
        echo "const monimoiCtx = document.getElementById('monimoiChart').getContext('2d');";
        echo "new Chart(monimoiCtx, {";
        echo "  type: 'pie',";
        echo "  data: {";
        echo "    labels: " . json_encode($monimoi_labels, JSON_UNESCAPED_UNICODE) . ",";
        echo "    datasets: [{";
        echo "      data: " . json_encode($monimoi_data) . ",";
        echo "      backgroundColor: " . json_encode(array_slice($colors, 0, count($monimoi_data))) . ",";
        echo "      borderWidth: 2,";
        echo "      borderColor: '#fff'";
        echo "    }]";
        echo "  },";
        echo "  options: {";
        echo "    responsive: true,";
        echo "    maintainAspectRatio: false,";
        echo "    plugins: {";
        echo "      legend: {";
        echo "        position: 'right',";
        echo "        labels: {";
        echo "          font: { size: 12 },";
        echo "          padding: 15";
        echo "        }";
        echo "      },";
        echo "      tooltip: {";
        echo "        callbacks: {";
        echo "          label: function(context) {";
        echo "            let label = context.label || '';";
        echo "            let value = context.parsed || 0;";
        echo "            let total = context.dataset.data.reduce((a, b) => a + b, 0);";
        echo "            let percentage = ((value / total) * 100).toFixed(1);";
        echo "            return label + ': ' + value + ' (' + percentage + '%)';";
        echo "          }";
        echo "        }";
        echo "      }";
        echo "    }";
        echo "  }";
        echo "});";
    }
    
    // Status Chart
    if (count($filtered_status_data) > 0) {
        echo "const statusCtx = document.getElementById('statusChart').getContext('2d');";
        echo "new Chart(statusCtx, {";
        echo "  type: 'pie',";
        echo "  data: {";
        echo "    labels: " . json_encode($filtered_status_labels, JSON_UNESCAPED_UNICODE) . ",";
        echo "    datasets: [{";
        echo "      data: " . json_encode($filtered_status_data) . ",";
        echo "      backgroundColor: " . json_encode(array_slice($colors, 0, count($filtered_status_data))) . ",";
        echo "      borderWidth: 2,";
        echo "      borderColor: '#fff'";
        echo "    }]";
        echo "  },";
        echo "  options: {";
        echo "    responsive: true,";
        echo "    maintainAspectRatio: false,";
        echo "    plugins: {";
        echo "      legend: {";
        echo "        position: 'right',";
        echo "        labels: {";
        echo "          font: { size: 11 },";
        echo "          padding: 12";
        echo "        }";
        echo "      },";
        echo "      tooltip: {";
        echo "        callbacks: {";
        echo "          label: function(context) {";
        echo "            let label = context.label || '';";
        echo "            let value = context.parsed || 0;";
        echo "            let total = context.dataset.data.reduce((a, b) => a + b, 0);";
        echo "            let percentage = ((value / total) * 100).toFixed(1);";
        echo "            return label + ': ' + value + ' (' + percentage + '%)';";
        echo "          }";
        echo "        }";
        echo "      }";
        echo "    }";
        echo "  }";
        echo "});";
    }
    
    // Substitute Teachers Chart
    if (count($anapl_labels) > 0) {
        echo "const anaplCtx = document.getElementById('anaplChart').getContext('2d');";
        echo "new Chart(anaplCtx, {";
        echo "  type: 'pie',";
        echo "  data: {";
        echo "    labels: " . json_encode($anapl_labels, JSON_UNESCAPED_UNICODE) . ",";
        echo "    datasets: [{";
        echo "      data: " . json_encode($anapl_data) . ",";
        echo "      backgroundColor: " . json_encode(array_slice($colors, 0, count($anapl_data))) . ",";
        echo "      borderWidth: 2,";
        echo "      borderColor: '#fff'";
        echo "    }]";
        echo "  },";
        echo "  options: {";
        echo "    responsive: true,";
        echo "    maintainAspectRatio: false,";
        echo "    plugins: {";
        echo "      legend: {";
        echo "        position: 'right',";
        echo "        labels: {";
        echo "          font: { size: 11 },";
        echo "          padding: 12";
        echo "        }";
        echo "      },";
        echo "      tooltip: {";
        echo "        callbacks: {";
        echo "          label: function(context) {";
        echo "            let label = context.label || '';";
        echo "            let value = context.parsed || 0;";
        echo "            let total = context.dataset.data.reduce((a, b) => a + b, 0);";
        echo "            let percentage = ((value / total) * 100).toFixed(1);";
        echo "            return label + ': ' + value + ' (' + percentage + '%)';";
        echo "          }";
        echo "        }";
        echo "      }";
        echo "    }";
        echo "  }";
        echo "});";
    }
    
    // Schools Chart
    if (count($filtered_sx_values) > 0) {
        echo "const schoolsCtx = document.getElementById('schoolsChart').getContext('2d');";
        echo "new Chart(schoolsCtx, {";
        echo "  type: 'pie',";
        echo "  data: {";
        echo "    labels: " . json_encode($filtered_sx_labels, JSON_UNESCAPED_UNICODE) . ",";
        echo "    datasets: [{";
        echo "      data: " . json_encode($filtered_sx_values) . ",";
        echo "      backgroundColor: " . json_encode(array_slice($colors, 0, count($filtered_sx_values))) . ",";
        echo "      borderWidth: 2,";
        echo "      borderColor: '#fff'";
        echo "    }]";
        echo "  },";
        echo "  options: {";
        echo "    responsive: true,";
        echo "    maintainAspectRatio: false,";
        echo "    plugins: {";
        echo "      legend: {";
        echo "        position: 'right',";
        echo "        labels: {";
        echo "          font: { size: 11 },";
        echo "          padding: 12";
        echo "        }";
        echo "      },";
        echo "      tooltip: {";
        echo "        callbacks: {";
        echo "          label: function(context) {";
        echo "            let label = context.label || '';";
        echo "            let value = context.parsed || 0;";
        echo "            let total = context.dataset.data.reduce((a, b) => a + b, 0);";
        echo "            let percentage = ((value / total) * 100).toFixed(1);";
        echo "            return label + ': ' + value + ' (' + percentage + '%)';";
        echo "          }";
        echo "        }";
        echo "      }";
        echo "    }";
        echo "  }";
        echo "});";
    }
    
    echo "</script>";
    echo "</body>";
    echo "</html>";

    mysqli_close($mysqlconnection);
    ?>
