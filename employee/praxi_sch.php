<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"../config.php";
	require_once"../tools/functions.php";
?>	
  <html>
  <head>      
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <title>Εκπαιδευτικοί και Σχολεία ανά Πράξη</title>
        <link href="../css/select2.min.css" rel="stylesheet" />
       	<script type="text/javascript" src="../js/1.7.2.jquery.min.js"></script>
        <script src="../js/select2.min.js"></script>
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
        <script type="text/javascript">   
            $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
                        $(".praxi_select").select2();
		});
            
        </script>
    </head>
    <body>
    <?php include('../etc/menu.php'); ?>
<?php        
	// praxi_sch: Displays schools or Schools & ektaktoi for the selected praxi

        include("../tools/class.login.php");
        $log = new logmein();
        if($log->logincheck($_SESSION['loggedin']) == false){
            header("Location: ../tools/login.php");
        }
        $usrlvl = $_SESSION['userlevel'];

        $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
        mysqli_query($mysqlconnection, "SET NAMES 'greek'");
        mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
        
    echo "<h2>Εκπαιδευτικοί και Σχολεία ανά Πράξη</h2>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<form action='' method='POST' autocomplete='off'>";
    
    $sql = "select * from praxi";
    $result = mysqli_query($mysqlconnection, $sql);
    echo "<tr><td>Επιλογή Πράξεων:</td><td>";
    $cmb = "<select name=\"praxi[]\" class=\"praxi_select\" multiple=\"multiple\">";
    while ($row = mysqli_fetch_array($result)){
        if (isset($_POST['praxi']) && in_array($row['id'],$_POST['praxi']))
            $cmb .= "<option value=\"".$row['id']."\" selected>".$row['name']."</option>";
        else
            $cmb .= "<option value=\"".$row['id']."\">".$row['name']."</option>";
    }
    $cmb .= "</select>";
    echo $cmb;
    
    echo "</td></tr>";
    
    echo "<tr><td>Επιλογή:</td><td>";
    echo "<input type='radio' name='type' value='0' checked >Εμφάνιση μόνο Σχολείων<br>";
    echo "<input type='radio' name='type' value='1'>Εμφάνιση Εκπ/κών & Σχολείων<br>";
    echo "</td></tr>";
    
    echo "<tr><td colspan=2><input type='submit' value='Αναζήτηση'>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='ektaktoi_list.php'\"></td></tr>";
    echo "</table></form>";

	if(isset($_POST['praxi']))
	{
                foreach ($_REQUEST['praxi'] as $pr)
                    $praxeis[] = $pr;
                $i = 0;
                
                $all = $_POST['type'];
                
                foreach ($praxeis as $pr)
                    $praxinm[] = getNamefromTbl($mysqlconnection, praxi, $pr);

                $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
                mysqli_query($mysqlconnection, "SET NAMES 'greek'");
                mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
		if ($all)
                    $query = "select s.id as schid, e.id, e.surname, e.name, s.name as sch, p.name as praxi from ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id 
                        join school s on s.id = y.yphrethsh join praxi p on p.id = e.praxi where y.sxol_etos=$sxol_etos AND e.praxi in (" . implode(',',$_POST['praxi']) . ") ORDER BY SURNAME,NAME ASC";
                else
                    $query = "select distinct s.name as sch, s.id as schid from ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id 
                        join school s on s.id = y.yphrethsh where y.sxol_etos=$sxol_etos AND e.praxi in (" . implode(',',$_POST['praxi']) . ") ORDER BY S.NAME ASC";
                //echo $query;
		$result = mysqli_query($mysqlconnection, $query);
		
                ob_start();
                echo "<h2>Πράξη(-εις): ". implode(', ', $praxinm)."</h2>";
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
                if ($all)
                    echo "<thead><tr><th>Ονοματεπώνυμο</th><th>Σχολείο</th><th>Πράξη</th></tr></thead><tbody>";
                else
                    echo "<thead><tr><th>Σχολείο</th></tr></thead><tbody>";

            while ($row = mysqli_fetch_array($result))	
            {
		$id = $row['id'];
		$name = $row['name'];
		$surname = $row['surname'];
                $sch = $row['sch'];
                $schid = $row['schid'];
		$praxi = $row['praxi'];
		                
                if ($all)
                    echo "<tr><td><a href=\"ektaktoi.php?id=$id&op=view\">$surname $name</a></td><td><a href=\"../school/school_status.php?org=$schid\">$sch</a></td><td>$praxi</td></tr>";
                else
                    echo "<tr><td><a href=\"../school/school_status.php?org=$schid\">$sch</a></td></tr>";
                $i++;
            }
		echo "</tbody></table>";
    echo "<small><i>$i εγγραφές</i></small>";
    echo "<br><br>";

    mysqli_close($mysqlconnection);
                
    $page = ob_get_contents(); 
    $page = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $page);
		ob_end_flush();
			
		echo "<form action='../tools/2excel.php' method='post'>";
		echo "<input type='hidden' name = 'data' value='".  htmlspecialchars($page, ENT_QUOTES)."'>";
                echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "&nbsp;&nbsp;&nbsp;";
                echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_list.php'\">";
                echo "</form>";
	}
?>
<br><br>
</body>
</html>