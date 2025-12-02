<?php
  // Start output buffering to prevent any accidental output
  ob_start();
  
  // Allow access if ajax parameter is set (jQuery load adds this) or if X-Requested-With header exists
  $isAjax = isset($_GET['ajax']) || 
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
  
  if (!$isAjax && !isset($_GET['id'])) {
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
    /* Modal-specific styling for ekt adeia list */
    #ekt-adeia-modal {
        font-family: inherit;
        padding: 0;
    }
    
    #ekt-adeia-modal .adeia-header {
        background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 50%, #2A8B9A 100%);
        color: white;
        padding: 15px 20px;
        margin: -10px -10px 15px -10px;
        border-radius: 4px 4px 0 0;
        font-weight: 600;
        font-size: 1.1em;
    }
    
    #ekt-adeia-modal .adeia-content {
        padding: 0 10px;
        max-height: 500px;
        overflow-y: auto;
    }
    
    #ekt-adeia-modal .imagetable {
        width: 100%;
        margin: 10px 0;
        border-collapse: collapse;
    }
    
    #ekt-adeia-modal .imagetable th {
        background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
        color: white;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        border: 1px solid #2A8B9A;
    }
    
    #ekt-adeia-modal .imagetable td {
        padding: 8px 10px;
        border: 1px solid #d1d5db;
    }
    
    #ekt-adeia-modal .imagetable tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    
    #ekt-adeia-modal .imagetable tbody tr:hover {
        background-color: #e0f7fa;
    }
    
    #ekt-adeia-modal .imagetable a {
        color: #2563eb;
        text-decoration: none;
    }
    
    #ekt-adeia-modal .imagetable a:hover {
        text-decoration: underline;
    }
    
    #ekt-adeia-modal strong {
        color: #dc2626;
        font-weight: 700;
    }
    
    #ekt-adeia-modal .adeia-actions {
        text-align: center;
        margin: 15px 0;
    }
    
    #ekt-adeia-modal .adeia-actions a {
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
    
    #ekt-adeia-modal .adeia-actions a:hover {
        background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
    }
    
    .imagetable td:first-child {
        width: 110px;
    }
    
    #ekt-adeia-modal .imagetable th:nth-child(2),
    #ekt-adeia-modal .imagetable td:nth-child(2) {
        width: 150px;
        max-width: 150px;
        min-width: 150px;
    }
    
    /* Narrow column for Αρ.Πρωτ. (Protocol Number) - 3rd column */
    #ekt-adeia-modal .imagetable th:nth-child(3),
    #ekt-adeia-modal .imagetable td:nth-child(3) {
        width: 70px;
        max-width: 70px;
        min-width: 70px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 8px 5px;
    }
</style>
<div class="adeia-content">
      <?php
        $i = 0;
        if (isset($_GET['sxol_etos'])){
            $sxol_etos = $_GET['sxol_etos'];
        } else {
            $sxol_etos = getParam('sxol_etos', $mysqlconnection);
        }
        
        echo '<div class="adeia-header">Λίστα Αδειών Αναπληρωτή</div>';
        
        $query = "SELECT * from adeia_ekt where emp_id=".$_GET['id']." AND sxoletos = $sxol_etos";
        $result = mysqli_query($mysqlconnection, $query);
        $num=mysqli_num_rows($result);
        if (!$num)
        {
            echo "<div style='text-align: center; padding: 20px;'><p style='font-size: 1.1em; margin-bottom: 15px;'>Δε βρέθηκαν άδειες</p>";
            $emp_id = $_GET['id'];
            if ($usrlvl < 2 || (isset($_SESSION['adeia']) && $_SESSION['adeia']))
                echo "<div class='adeia-actions'><span title=\"Προσθήκη Άδειας\"><a href=\"ekt_adeia.php?emp=$emp_id&op=add&sxol_etos=$sxol_etos\">Προσθήκη Άδειας<img style=\"border: 0pt none; vertical-align: middle; margin-left: 5px;\" src=\"../images/user_add.png\"/></a></span></div>";
            echo "</div>";
            mysqli_close($mysqlconnection);
            echo "</div>";
            exit;
        }
                    
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border='1'>";	
        echo "<thead><tr>";
        echo "<th style='min-width: 30px;'>Ενέργεια</th><th>Τύπος</th><th>Αρ.Πρωτ.</th><th>Ημ.Αίτησης</th><th>Ημέρες</th><th>Ημ.Έναρξης</th><th>Ημ.Λήξης</th>";
        echo "</tr></thead>";
        echo "<tbody>";
        
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
          $comments = mysqli_result($result, $i, "comments");
          $sxol_etos = mysqli_result($result, $i, "sxoletos");
                              
          $query1 = "select type from adeia_ekt_type where id=$type";
          $result1 = mysqli_query($mysqlconnection, $query1);
          $typewrd = mysqli_result($result1, 0, "type");
          $viewlink = "<span title='Προβολή'><a href='ekt_adeia.php?adeia=$id&op=view&sxol_etos=$sxol_etos'><img style='border: 0pt none;' src='../images/view_action.png'></a></span>&nbsp;";
          echo "<tr><td>";
          echo "$viewlink<span title='Επεξεργασία'><a href='ekt_adeia.php?adeia=$id&op=edit&sxol_etos=$sxol_etos'><img style='border: 0pt none;' src='../images/edit_action.png'></a></span>&nbsp;";
          if ($usrlvl < 2 || (isset($_SESSION['adeia']) && $_SESSION['adeia'])) {
            $deleteUrl = htmlspecialchars("ekt_adeia.php?adeia=$id&op=delete&sxol_etos=$sxol_etos", ENT_QUOTES);
            echo "<span title=\"Διαγραφή\"><a href=\"javascript:void(0);\" onclick=\"confirmDelete('$deleteUrl')\"><img style=\"border: 0pt none;\" src=\"../images/delete_action.png\"/></a></span>";
          }
          echo "</td><td><a href='ekt_adeia.php?adeia=$id&op=view&sxol_etos=$sxol_etos'>$typewrd</a></td><td>$prot</td><td>".date('d-m-Y',strtotime($date))."</td><td>$days</td><td>".date('d-m-Y',strtotime($start))."</td><td>".date('d-m-Y',strtotime($finish))."</td></tr>";
          $i++;
        }

        echo "</tbody>";
        echo "<tfoot><tr>";
        // add absence only on current year
        if (($usrlvl < 2 || (isset($_SESSION['adeia']) && $_SESSION['adeia'])) && $sxol_etos == getParam('sxol_etos',$mysqlconnection)) {
            echo "<td colspan=7 style='text-align: left; padding: 10px;'><span title=\"Προσθήκη Άδειας\"><a href=\"ekt_adeia.php?emp=$emp_id&op=add&sxol_etos=$sxol_etos\" style='color: #2563eb; font-weight: 600;'>Προσθήκη Άδειας<img style=\"border: 0pt none; vertical-align: middle; margin-left: 5px;\" src=\"../images/user_add.png\"/></a></span></td>";
        } else {
            echo "<td colspan=7></td>";
        }
        echo "</tr></tfoot>";
        echo "</table>";
        echo "</div>"; //adeia-content div

        mysqli_close($mysqlconnection);
?>
