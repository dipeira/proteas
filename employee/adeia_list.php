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

    #adeia-modal .imagetable th:first-child,
    #adeia-modal .imagetable td:first-child {
        width: 100px;
        min-width: 100px;
        max-width: 100px;
        text-align: center;
        white-space: nowrap;
        padding: 6px 4px;
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
    #adeia-modal .imagetable td:first-child a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        margin: 0 2px;
        width: 26px;
        height: 26px;
        border-radius: 5px;
        transition: all 0.2s ease;
    }
    
    #adeia-modal .imagetable td:first-child a:hover {
        transform: scale(1.1);
    }
    
    #adeia-modal .imagetable td:first-child .icon-view {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
    }
    
    #adeia-modal .imagetable td:first-child .icon-edit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    #adeia-modal .imagetable td:first-child .icon-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    #adeia-modal .imagetable td:first-child .icon-view:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
    }
    
    #adeia-modal .imagetable td:first-child .icon-edit:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
    }
    
    #adeia-modal .imagetable td:first-child .icon-delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
    }
    
    #adeia-modal .imagetable td:first-child svg {
        width: 16px;
        height: 16px;
        fill: currentColor;
    }
    
    /* Add button styling */
    #adeia-modal .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95em;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        white-space: nowrap;
        min-width: fit-content;
    }
    
    #adeia-modal .btn-add:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        color: white;
        text-decoration: none;
    }
    
    #adeia-modal .btn-add svg {
        width: 18px;
        height: 18px;
        fill: currentColor;
    }
    
    /* Footer row styling */
    #adeia-modal .imagetable tfoot td {
        font-size: 1em;
        padding: 12px 10px;
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
            if ($usrlvl < 2 || $_SESSION['adeia']) {
                $addIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>';
                echo "<div class='adeia-actions'><a href=\"adeia.php?emp=$emp_id&op=add\" class=\"btn-add\">$addIcon Προσθήκη Άδειας</a></div>";
            }
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
            echo "<table id=\"mytbl-$yr\" class=\"imagetable\" border='1'>";	
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
                $viewIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>';
                $editIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>';
                $deleteIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>';
                
                echo "<tr><td>";
                echo "<a href='adeia.php?adeia=$id&op=view' class='icon-view' title='Προβολή'>$viewIcon</a>";
                if ($usrlvl < 2 || $_SESSION['adeia']) {
                    echo "<a href='adeia.php?adeia=$id&op=edit' class='icon-edit' title='Επεξεργασία'>$editIcon</a>";
                    $deleteUrl = htmlspecialchars("adeia.php?adeia=$id&op=delete", ENT_QUOTES);
                    echo "<a href=\"javascript:void(0);\" onclick=\"confirmDelete('$deleteUrl')\" class='icon-delete' title='Διαγραφή'>$deleteIcon</a>";
                }
                echo "</td>";
                echo "<td><a href='adeia.php?adeia=$id&op=view'>$typewrd</a></td><td>$prot</td><td>".date('d-m-Y',strtotime($date))."</td><td>$days</td><td>".date('d-m-Y',strtotime($start))."</td><td>".date('d-m-Y',strtotime($finish))."</td></tr>";
                $i++;
                $synolo_days += $days;
            }

            echo "</tbody>";
            echo "<tfoot><tr>";
            if ($usrlvl < 2 || $_SESSION['adeia']) {
                $addIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>';
                echo "<td colspan=4 style='text-align: left; padding: 12px;'><a href=\"adeia.php?emp=$emp_id&op=add\" class=\"btn-add\">$addIcon Προσθήκη Άδειας</a></td>";
            } else {
                echo "<td colspan=4></td>";
            }
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
