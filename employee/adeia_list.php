<?php
  // Start output buffering to prevent any accidental output
  ob_start();
  
  // Allow access if ajax parameter is set (jQuery load adds this) or if X-Requested-With header exists
  // This prevents accidental direct access while allowing AJAX loads
  $isAjax = isset($_GET['ajax']) || 
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
  
  if (!$isAjax && !isset($_GET['id'])) {
      // If not AJAX request and no ID, output nothing
      ob_end_clean();
      die('');
  }
  
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../include/functions.php";
  
  // Start session only if not already started
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  $usrlvl = isset($_SESSION['userlevel']) ? $_SESSION['userlevel'] : 0;
  
  // Clear any output that might have been generated
  ob_clean();
	
?>
<style>
    /* Modal-specific styling for adeia list */
    #adeia-modal {
        font-family: inherit;
        padding: 0;
    }
    
    #adeia-modal .adeia-header {
        background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 50%, #2A8B9A 100%);
        color: white;
        padding: 15px 20px;
        margin: -10px -10px 15px -10px;
        border-radius: 4px 4px 0 0;
        font-weight: 600;
        font-size: 1.1em;
    }
    
    #adeia-modal .adeia-content {
        padding: 0 10px;
        max-height: 500px;
        overflow-y: auto;
    }
    
    #adeia-modal .imagetable {
        width: 100%;
        margin: 10px 0;
        border-collapse: collapse;
    }
    
    #adeia-modal .imagetable th {
        background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
        color: white;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        border: 1px solid #2A8B9A;
    }
    
    #adeia-modal .imagetable td {
        padding: 8px 10px;
        border: 1px solid #d1d5db;
    }
    
    #adeia-modal .imagetable tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    
    #adeia-modal .imagetable tbody tr:hover {
        background-color: #e0f7fa;
    }
    
    #adeia-modal .imagetable a {
        color: #2563eb;
        text-decoration: none;
    }
    
    #adeia-modal .imagetable a:hover {
        text-decoration: underline;
    }
    
    #adeia-modal #tabs {
        margin-top: 15px;
        width: 100%;
    }
    
    #adeia-modal #tabs ul.ui-tabs-nav {
        background: #f0f9ff;
        border-bottom: 2px solid #4FC5D6;
        padding: 0;
        margin: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
    }
    
    #adeia-modal #tabs .ui-tabs-nav li {
        border: 1px solid #bae6fd;
        border-bottom: none;
        margin-right: 2px;
        margin-bottom: -1px;
        background: #e0f7fa;
        position: relative;
    }
    
    #adeia-modal #tabs .ui-tabs-nav li a {
        padding: 10px 18px;
        color: #1e40af;
        text-decoration: none;
        display: block;
        outline: none;
    }
    
    #adeia-modal #tabs .ui-tabs-nav li.ui-state-active,
    #adeia-modal #tabs .ui-tabs-nav li.ui-tabs-active {
        background: white;
        border-bottom: 1px solid white;
        margin-bottom: -1px;
        z-index: 1;
    }
    
    #adeia-modal #tabs .ui-tabs-nav li.ui-state-active a,
    #adeia-modal #tabs .ui-tabs-nav li.ui-tabs-active a {
        color: #2A8B9A;
        font-weight: 600;
    }
    
    #adeia-modal #tabs .ui-tabs-nav li.ui-state-hover {
        background: #b2ebf2;
    }
    
    #adeia-modal #tabs .ui-tabs-panel {
        padding: 15px;
        background: white;
        border: 1px solid #4FC5D6;
        border-top: none;
        display: block;
        min-height: 200px;
    }
    
    #adeia-modal #tabs .ui-tabs-hide {
        display: none !important;
    }
    
    #adeia-modal strong {
        color: #dc2626;
        font-weight: 700;
    }
    
    #adeia-modal .adeia-actions {
        text-align: center;
        margin: 15px 0;
    }
    
    #adeia-modal .adeia-actions a {
        display: inline-block;
        padding: 8px 15px;
        background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.2s;
        margin: 0 5px;
    }
    
    #adeia-modal .adeia-actions a:hover {
        background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
    }

    .imagetable td:first-child {
        width: 110px;
    }
    
    #adeia-modal .imagetable th:nth-child(2),
    #adeia-modal .imagetable td:nth-child(2) {
        width: 150px;
        max-width: 150px;
        min-width: 150px;
    }
    /* Narrow column for Αρ.Πρωτ. (Protocol Number) - 3rd column */
    #adeia-modal .imagetable th:nth-child(3),
    #adeia-modal .imagetable td:nth-child(3) {
        width: 70px;
        max-width: 70px;
        min-width: 70px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 8px 5px;
    }
    
    /* Action icon styling */
    #adeia-modal .imagetable tbody td:first-child {
        text-align: center;
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    #adeia-modal .imagetable tbody td:first-child a {
        display: inline-block;
        margin: 0 4px;
        transition: transform 0.2s;
        width: 20px;
        height: 20px;
        color: #374151;
    }
    
    #adeia-modal .imagetable tbody td:first-child a:hover {
        transform: scale(1.15);
    }
    
    #adeia-modal .imagetable tbody td:first-child a.action-icon.view:hover {
        color: #2563eb;
    }
    
    #adeia-modal .imagetable tbody td:first-child a.action-icon.edit:hover {
        color: #059669;
    }
    
    #adeia-modal .imagetable tbody td:first-child a.action-icon.delete:hover {
        color: #dc2626;
    }
    
    #adeia-modal .imagetable tbody td:first-child svg {
        width: 20px;
        height: 20px;
        display: block;
    }
</style>
<div class="adeia-content">
      <?php
        echo '<div class="adeia-header">Λίστα Αδειών</div>';
        
        $ypol = ypoloipo_adeiwn($_GET['id'], $mysqlconnection);
        if ($ypol[2] == 0)
            echo "<div style='padding: 10px 0; font-size: 1.05em;'>Υπόλοιπο κανονικών αδειών για το $ypol[0]: <strong>$ypol[1] ημέρες</strong></div>";
        else
            echo "<div style='padding: 10px 0; font-size: 1.05em;'>Υπόλοιπο κανονικών αδειών για το $ypol[0]: <strong>$ypol[1] ημέρες</strong> ($ypol[3] ημέρες από το $ypol[2])</div>";
        
        $query = "SELECT * from adeia where emp_id=".$_GET['id']." ORDER BY start";
        $result = mysqli_query($mysqlconnection, $query);
        $num=mysqli_num_rows($result);
        if (!$num)
        {
            echo "<div style='text-align: center; padding: 20px;'><p style='font-size: 1.1em; margin-bottom: 15px;'>Δε βρέθηκαν άδειες</p>";
            $emp_id = $_GET['id'];
            if ($usrlvl < 2 || $_SESSION['adeia'])
                echo "<div class='adeia-actions'><span title=\"Προσθήκη Άδειας\"><a href=\"adeia.php?emp=$emp_id&op=add\">Προσθήκη Άδειας<img style=\"border: 0pt none; vertical-align: middle; margin-left: 5px;\" src=\"../images/user_add.png\"/></a></span></div>";
            echo "</div>";
            mysqli_close($mysqlconnection);
            echo "</div>";
            exit;
        }

        // get adeia years
        $query = "SELECT DISTINCT YEAR(start) FROM adeia where emp_id=".$_GET['id'].' ORDER BY year(start) DESC';
        $result = mysqli_query($mysqlconnection, $query);
        while ($year = mysqli_fetch_array($result, MYSQLI_NUM))
            $year_arr[] = $year[0];
        
        // tabs
        echo "<div id='container' style='width: 100%;'>";
        echo "<div id='tabs'>";
        echo "<ul>";
        $tab_i = 1;
        foreach ($year_arr as $yr){
            echo "<li><a href='#tabs-$tab_i'>$yr</a></li>";
            $tab_i++;
        }
        echo "</ul>";
        
        $tab_i = 1;
        // year tab
        foreach ($year_arr as $yr)
        {		
            $i = $synolo_days = 0;
            echo "<div id='tabs-$tab_i'>";
            
            // year table
            echo "<table id=\"mytbl-$yr\" class=\"imagetable tablesorter\" border='1'>";	
            echo "<thead><tr>";
            echo "<th style='min-width: 30px;'>Ενέργεια</th><th>Τύπος</th><th>Αρ.Πρωτ.</th><th>Ημ.Αίτησης</th>";
            echo "<th>Ημέρες</th><th>Ημ.Έναρξης</th><th>Ημ.Λήξης</th>";
            echo "</tr></thead>";
            echo "<tbody>";
            
            $query = "SELECT * from adeia where year(start) = $yr AND emp_id=".$_GET['id']." ORDER BY start";
            $result = mysqli_query($mysqlconnection, $query);
            $num=mysqli_num_rows($result);
            while ($i<$num)
            {
                $id = mysqli_result($result, $i, "id");
                $emp_id = mysqli_result($result, $i, "emp_id");
                $type = mysqli_result($result, $i, "type");
                $prot = mysqli_result($result, $i, "prot");
                $date = mysqli_result($result, $i, "date");
                $days = mysqli_result($result, $i, "days");
                $start = mysqli_result($result, $i, "start");
                $finish = mysqli_result($result, $i, "finish");

                $query1 = "select type from adeia_type where id=$type";
                $result1 = mysqli_query($mysqlconnection, $query1);
                $typewrd = mysqli_result($result1, 0, "type");
                echo "<tr><td>";
                echo "<span title=\"Προβολή\"><a href=\"adeia.php?adeia=$id&op=view\" class=\"action-icon view\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M10 12a2 2 0 100-4 2 2 0 000 4z\"></path><path fill-rule=\"evenodd\" d=\"M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z\" clip-rule=\"evenodd\"></path></svg></a></span>";
                if ($usrlvl < 2 || $_SESSION['adeia']) {
                    echo "<span title=\"Επεξεργασία\"><a href=\"adeia.php?adeia=$id&op=edit\" class=\"action-icon edit\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z\"></path></svg></a></span>";
                    $deleteUrl = htmlspecialchars("adeia.php?adeia=$id&op=delete", ENT_QUOTES);
                    echo "<span title=\"Διαγραφή\"><a href=\"javascript:void(0);\" onclick=\"confirmDelete('$deleteUrl')\" class=\"action-icon delete\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z\" clip-rule=\"evenodd\"></path></svg></a></span>";
                }
                echo "</td>";
                echo "<td><a href='adeia.php?adeia=$id&op=view'>$typewrd</a></td><td>$prot</td><td>".date('d-m-Y',strtotime($date))."</td><td>$days</td><td>".date('d-m-Y',strtotime($start))."</td><td>".date('d-m-Y',strtotime($finish))."</td></tr>";
                $i++;
                $synolo_days += $days;
            }

            echo "</tbody>";
            echo "<tfoot><tr>";
            if ($usrlvl < 2 || $_SESSION['adeia'])
                echo "<td colspan=4 style='text-align: left; padding: 10px;'><span title=\"Προσθήκη Άδειας\"><a href=\"adeia.php?emp=$emp_id&op=add\" style='color: #2563eb; font-weight: 600;'>Προσθήκη Άδειας<img style=\"border: 0pt none; vertical-align: middle; margin-left: 5px;\" src=\"../images/user_add.png\"/></a></span></td>";
            else
                echo "<td colspan=4></td>";
            echo "<td colspan=3 style='text-align: right; padding: 10px; font-weight: 600; color: #1e40af;'>Σύνολο αδειών έτους: $synolo_days</td></tr></tfoot>";
            echo "</table>";
            
            
            $tab_i++;
            echo "</div>"; //year tab div
        }
        echo "</div>"; //all tabs div
        echo "</div>"; //tabs container
        echo "</div>"; //adeia-content div

	mysqli_close($mysqlconnection);
?>
