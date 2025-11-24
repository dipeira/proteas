<?php
    header('Content-type: text/html; charset=utf-8'); 
    Require_once "config.php";
    Require_once "include/functions.php";
        
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

  require "tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {   
    header("Location: tools/login.php");
}
else {
    $logged = 1;
}        
  $usrlvl = $_SESSION['userlevel'];
  
  // Get statistics data (same as stats.php)
  // init variables
  $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ',$mysqlconnection);
  $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ',$mysqlconnection);
  $se_forea = getSchoolID('Απόσπαση σε φορέα',$mysqlconnection);

  $query = "SELECT count( * ) FROM employee WHERE status!=2 AND sx_organikhs NOT IN ($allo_pyspe, $allo_pysde) AND thesi!=5";
  $result = mysqli_query($mysqlconnection, $query);
  $monimoi_her_total = mysqli_result($result, 0);
  
  $query = "SELECT count( * ) FROM ektaktoi";
  $result = mysqli_query($mysqlconnection, $query);
  $anapl_total = mysqli_result($result, 0);
  
  // Prepare data for schools
  $sx_arr = array();
  $query = "SELECT count(*) FROM school WHERE type = 1";
  $res = mysqli_query($mysqlconnection, $query);
  $res = mysqli_result($res, 0);
  $sx_arr['Δημοτικά (Σύνολο)'] = $res;
  $query = "SELECT count(*) FROM school WHERE type = 2";
  $res = mysqli_query($mysqlconnection, $query);
  $res = mysqli_result($res, 0);
  $sx_arr['Νηπιαγωγεία (Σύνολο)'] = $res;
  $total_schools = array_sum($sx_arr);

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

  // Prepare data for substitute teachers chart
  $anapl_data = array();
  $anapl_labels = array();
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
  }

  // Handle employee search redirect
  if (isset($_POST['surname']) && strlen($_POST['surname'])>0) {
      if (isset($_POST['pinakas']) && $_POST['pinakas']==1) {
          $surn = explode(' ', $_POST['surname'])[0];
          $url = "employee/ektaktoi_list.php?surname=".urlencode($surn);
          echo "<script>window.location = '$url'</script>";
          exit;
      } else {
          $surn = explode(' ', $_POST['surname'])[0];
          $url = "employee/monimoi_list.php?surname=".urlencode($surn);
          echo "<script>window.location = '$url'</script>";
          exit;
      }
  }
?>
<html>
  <head>
    <?php 
    $root_path = '';
    $page_title = 'Πρωτέας';
    require 'etc/head.php'; 
    ?>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type='text/javascript' src='js/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="css/jquery_notification.css" type="text/css" rel="stylesheet"/>
    <script type="text/javascript" src="js/jquery_notification_v.1.js"></script>
    <script type="text/javascript" src="js/common.js"></script>
    <style>
        body {
            padding: 20px;
            background-color: #f9fafb;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin: 20px 0 30px 0;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }
        
        .user-info {
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            padding: 10px 16px;
            border-radius: 8px;
            display: inline-block;
            margin: 12px 0;
            font-size: 0.875rem;
            color: #1e40af;
            font-weight: 500;
            border: 1px solid #4FC5D6;
        }
        
        /* Search Box Styling */
        .search-box {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .search-group {
            flex: 1;
            min-width: 250px;
        }
        
        .search-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .search-group input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .search-group input[type="text"]:focus {
            outline: none;
            border-color: #4FC5D6;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.1);
        }
        
        .search-group small {
            display: block;
            margin-top: 6px;
            color: #6b7280;
            font-size: 0.8125rem;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
            height: 42px;
        }
        
        .search-btn:hover {
            background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 197, 214, 0.4);
        }
        
        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: inherit;
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
        
        .stat-card .text-sm {
            color: #9ca3af;
            font-size: 0.875rem;
            margin-top: 8px;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        
        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
        }
        
        .chart-container h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }
        
        .chart-wrapper {
            position: relative;
            height: 400px;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-wrapper {
                height: 300px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-group {
                width: 100%;
            }
        }
    </style>
    <script type="text/javascript">        
      $().ready(function() {
          $("#surname").autocomplete("employee/get_name.php", {
            width: 260,
            matchContains: true,
            selectFirst: false
          });
          $("#surname").result(function(event, data, formatted) {
            if (data){
              $("#pinakas").val(data[1]);
            }
          });
      });         
    </script>
    
  </head>
  <body> 
    <?php require 'etc/menu.php'; ?>
    
    <?php
      // notify admin to delete init.php if it exists
      if ($usrlvl == 0) {
          if (file_exists('init.php')) {
              notify("ΠΡΟΣΟΧΗ: Παρακαλώ διαγράψτε το αρχείο <b>init.php</b> για λόγους ασφαλείας!</p>", 'error');
          }
      }
    ?>
    
    <div class="dashboard-container">
        <div class="page-header">
            <h1>Πρωτέας</h1>
            <?php if ($logged) { 
              $se = getParam('sxol_etos', $mysqlconnection);
              $sx_etos = substr($se, 0, 4).'-'.substr($se, 4, 2);
              echo "<div class='user-info'>👤 Ενεργός Χρήστης: <strong>".$_SESSION['user']."</strong> &nbsp;&nbsp; 📅 Σχολ.Έτος: <strong>$sx_etos</strong></div>";
            } ?>
        </div>
        
        <!-- Search Box -->
        <div class="search-box">
            <form method="POST" action="index.php" class="search-form">
                <div class="search-group">
                    <label for="surname">Αναζήτηση Εκπαιδευτικού</label>
                    <input type="text" name="surname" id="surname" placeholder="Εισάγετε επώνυμο..." />
                    <input type="hidden" name="pinakas" id="pinakas" />
                    <small>Ψάχνει σε μόνιμους & αναπληρωτές</small>
                </div>
                <button type="submit" class="search-btn" style="margin-bottom: 28px;">🔍 Αναζήτηση</button>
            </form>
        </div>
        
        <!-- Stat Cards -->
        <div class="cards-grid">
            <a href="employee/monimoi_list.php" class="stat-card">
                <div class="stat-label">Μονιμοι Εκπαιδευτικοι</div>
                <div class="stat-number"><?php echo number_format($monimoi_her_total); ?></div>
                <div class="text-sm">Συνολικός αριθμός μόνιμων εκπαιδευτικών</div>
            </a>
            
            <a href="employee/ektaktoi_list.php" class="stat-card">
                <div class="stat-label">Αναπληρωτες / Ωρομισθιοι</div>
                <div class="stat-number"><?php echo number_format($anapl_total); ?></div>
                <div class="text-sm">Συνολικός αριθμός αναπληρωτών</div>
            </a>
            
            <a href="school/school.php" class="stat-card">
                <div class="stat-label">Σχολικες Μοναδες</div>
                <div class="stat-number"><?php echo number_format($total_schools); ?></div>
                <div class="text-sm">Συνολικός αριθμός σχολείων</div>
            </a>
        </div>
        
        <!-- Charts -->
        <div class="charts-grid">
            <?php if (count($monimoi_labels) > 0) { ?>
            <div class="chart-container">
                <h3>Μόνιμοι εκπαιδευτικοί (με οργανική στη Δ/νση <?php echo getParam('dnsh', $mysqlconnection); ?>)</h3>
                <div class="chart-wrapper">
                    <canvas id="monimoiChart"></canvas>
                </div>
            </div>
            <?php } ?>
            
            <?php if (count($anapl_labels) > 0) { ?>
            <div class="chart-container">
                <h3>Αναπληρωτές / Ωρομίσθιοι εκπαιδευτικοί</h3>
                <div class="chart-wrapper">
                    <canvas id="anaplChart"></canvas>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <!-- Chart.js Scripts -->
    <script>
        // Color palette
        const colors = [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
            '#14b8a6', '#a855f7', '#f43f5e', '#0ea5e9', '#22c55e'
        ];
        
        <?php if (count($monimoi_labels) > 0) { ?>
        // Permanent Teachers Chart
        const monimoiCtx = document.getElementById('monimoiChart').getContext('2d');
        new Chart(monimoiCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($monimoi_labels, JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    data: <?php echo json_encode($monimoi_data); ?>,
                    backgroundColor: colors.slice(0, <?php echo count($monimoi_data); ?>),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 12 },
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        <?php } ?>
        
        <?php if (count($anapl_labels) > 0) { ?>
        // Substitute Teachers Chart
        const anaplCtx = document.getElementById('anaplChart').getContext('2d');
        new Chart(anaplCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($anapl_labels, JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    data: <?php echo json_encode($anapl_data); ?>,
                    backgroundColor: colors.slice(0, <?php echo count($anapl_data); ?>),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 11 },
                            padding: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        <?php } ?>
    </script>
    
</body>
</html>
<?php
    mysqli_close($mysqlconnection);
?>
