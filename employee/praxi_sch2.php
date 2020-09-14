<?php
	header('Content-type: text/html; charset=utf-8'); 
	require_once"../config.php";
	require_once"../tools/functions.php";
?>	
  <html>
  <head>      
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <title>Πράξεις ανά Σχολείο</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript">   
      $(document).ready(function() { 
			  $("#mytbl").tablesorter({widgets: ['zebra']}); 
		  });    
    </script>
  </head>
  <body>
    <?php include('../etc/menu.php'); ?>
<?php        
	// praxi_sch2: Displays praxeis per school

    include("../tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false){
        header("Location: ../tools/login.php");
    }
    $usrlvl = $_SESSION['userlevel'];

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    
    $type = $_REQUEST['type'];
    switch ($type) {
      case '0':
        $wheretype = '';
        break;
      case '1':
        $wheretype = 'WHERE s.type = 1';
        break;
      case '2':
        $wheretype = 'WHERE s.type = 2';
        break;
    }

    echo "<h2>Πράξεις ανά Σχολείο</h2>";
    ?>
    <p>Τύπος Σχολείου:</p>
    <form id="school_type">
      <input type="radio" name="type" value="0" <?= $type == 0 ? 'checked' : ''?> onchange="this.form.submit()"> Όλα
      <input type="radio" name="type" value="1" <?= $type == 1 ? 'checked' : ''?> onchange="this.form.submit()"> Δ.Σ.
      <input type="radio" name="type" value="2" <?= $type == 2 ? 'checked' : ''?> onchange="this.form.submit()"> Νηπιαγωγεία
    </form>
    <?php
    $sql = "select DISTINCT s.id,s.code,s.name as sname,p.name as pname from school s join yphrethsh_ekt y on y.yphrethsh = s.id join ektaktoi e on e.id = y.emp_id join praxi p on e.praxi = p.id $wheretype ORDER BY s.name ASC";

    $result = mysqli_query($mysqlconnection, $sql);
    $schools = Array();

    while ($row = mysqli_fetch_array($result)) {
      $schools[$row['sname']]['schools'][] = $row['pname'];
      $schools[$row['sname']]['id'] = $row['id'];
      $schools[$row['sname']]['code'] = $row['code'];
    }
		
    ob_start();
    echo "<h2>Σχολεία</h2>";
    
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
    echo "<thead><tr><th>Κωδικός</th><th>Σχολείο</th><th>Πράξεις</th></tr></thead><tbody>";

    foreach ($schools as $key => $sch)
    {
      echo "<tr><td>".$sch['code']."</td><td><a href='../school/school_status.php?org=".$sch['id']."' target='_blank'>$key</a></td>";
      echo "<td>".implode("<br>",$sch['schools'])."</td>";
      echo "</tr>";
    }
		echo "</tbody></table>";
    echo "<br><br>";

    mysqli_close($mysqlconnection);
                
    $page = ob_get_contents(); 
    $page = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $page);
    $page = str_replace("'", '', $page);
		ob_end_flush();
			
		echo "<form action='../tools/2excel.php' method='post'>";
		echo "<input type='hidden' name = 'data' value='$page'>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_list.php'\">";
    echo "</form>";
	
?>
<br><br>
</body>
</html>