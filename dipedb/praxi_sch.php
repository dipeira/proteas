<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"config.php";
	require_once"functions.php";
?>	
  <html>
  <head>      
        <LINK href="style.css" rel="stylesheet" type="text/css">
        <title>������������� ��� ������� ��� �����</title>
        <link href="css/select2.min.css" rel="stylesheet" />
       	<script type="text/javascript" src="js/1.7.2.jquery.min.js"></script>
        <script src="js/select2.min.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
        <script type="text/javascript">   
            $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
                        $(".praxi_select").select2();
		});
            
        </script>
        
<?php        
	// praxi_sch: Displays schools or Schools & ektaktoi for the selected praxi

        include("tools/class.login.php");
        $log = new logmein();
        if($log->logincheck($_SESSION['loggedin']) == false){
            header("Location: tools/login_check.php");
        }
        $usrlvl = $_SESSION['userlevel'];

        $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
        
        echo "<html><head><h2>������������� ��� ������� ��� �����</h2></head><body>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<form action='' method='POST' autocomplete='off'>";
        
        $sql = "select * from praxi";
        $result = mysql_query($sql, $mysqlconnection);
        echo "<tr><td>������� �������:</td><td>";
        $cmb = "<select name=\"praxi[]\" class=\"praxi_select\" multiple=\"multiple\">";
        while ($row = mysql_fetch_array($result)){
            if (in_array($row['id'],$_POST['praxi']))
                $cmb .= "<option value=\"".$row['id']."\" selected>".$row['name']."</option>";
            else
                $cmb .= "<option value=\"".$row['id']."\">".$row['name']."</option>";
        }
        $cmb .= "</select>";
        echo $cmb;
        
        echo "</td></tr>";
        
        echo "<tr><td>�������:</td><td>";
        echo "<input type='radio' name='type' value='0' checked >�������� ���� ��������<br>";
        echo "<input type='radio' name='type' value='1'>�������� ���/��� & ��������<br>";
        echo "</td></tr>";
        
        echo "<tr><td colspan=2><input type='submit' value='���������'>";
        echo "&nbsp;&nbsp;&nbsp;";
        echo "<INPUT TYPE='button' VALUE='���������' onClick=\"parent.location='ektaktoi_list.php'\"></td></tr>";
        echo "</table></form>";

	if(isset($_POST['praxi']))
	{
                foreach ($_REQUEST['praxi'] as $pr)
                    $praxeis[] = $pr;
                $i = 0;
                
                $all = $_POST['type'];
                
                foreach ($praxeis as $pr)
                    $praxinm[] = getNamefromTbl($mysqlconnection, praxi, $pr);

            	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
		if ($all)
                    $query = "select s.id as schid, e.id, e.surname, e.name, s.name as sch, p.name as praxi from ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id 
                        join school s on s.id = y.yphrethsh join praxi p on p.id = e.praxi where e.praxi in (" . implode(',',$_POST['praxi']) . ") ORDER BY SURNAME,NAME ASC";
                else
                    $query = "select distinct s.name as sch, s.id as schid from ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id 
                        join school s on s.id = y.yphrethsh where e.praxi in (" . implode(',',$_POST['praxi']) . ") ORDER BY S.NAME ASC";
                //echo $query;
		$result = mysql_query($query, $mysqlconnection);
		
                ob_start();
                echo "<h2>�����(-���): ". implode(', ', $praxinm)."</h2>";
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
                if ($all)
                    echo "<thead><tr><th>�������������</th><th>�������</th><th>�����</th></tr></thead><tbody>";
                else
                    echo "<thead><tr><th>�������</th></tr></thead><tbody>";

            while ($row = mysql_fetch_array($result))	
            {
		$id = $row['id'];
		$name = $row['name'];
		$surname = $row['surname'];
                $sch = $row['sch'];
                $schid = $row['schid'];
		$praxi = $row['praxi'];
		                
                if ($all)
                    echo "<tr><td><a href=\"ektaktoi.php?id=$id&op=view\">$surname $name</a></td><td><a href=\"school_status.php?org=$schid\">$sch</a></td><td>$praxi</td></tr>";
                else
                    echo "<tr><td><a href=\"school_status.php?org=$schid\">$sch</a></td></tr>";
                $i++;
            }
		echo "</tbody></table>";
                echo "<small><i>$i ��������</i></small>";
                echo "<br><br>";

		mysql_close();
                
                $page = ob_get_contents(); 
                $page = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $page);
		ob_end_flush();
			
		echo "<form action='2excel.php' method='post'>";
		echo "<input type='hidden' name = 'data' value='".  htmlspecialchars($page, ENT_QUOTES)."'>";
                echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>������� ��� excel</BUTTON>";
                echo "&nbsp;&nbsp;&nbsp;";
                echo "<INPUT TYPE='button' VALUE='���������' onClick=\"parent.location='ektaktoi_list.php'\">";
                echo "</form>";
	}
?>
<br><br>
</html>