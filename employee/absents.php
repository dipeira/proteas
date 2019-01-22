<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"../config.php";
  require_once "../tools/functions.php";
  //define("L_LANG", "el_GR"); Needs fixing
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'greek'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
  
  // Demand authorization                
  include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: ../tools/login.php");
  }
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>���/��� ��� ���������� �� �����</title>
	
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="../js/stickytable.js"></script>
	<script type="text/javascript">	
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
            $("#mytbl").stickyTableHeaders();
        });
	</script>
  </head>
  <body> 
  <?php include('../etc/menu.php'); ?>
    <center>
        <h2>���/��� ��� ���������� �� �����</h2>
      <?php

            // set timeout to 90 secs
            set_time_limit(90);
            //�� �����
            //old queries:
            //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND status=3";
            //$query0 = "SELECT * from adeia WHERE emp_id='$id' AND start<'$today' AND finish>'$today'";
            $today = date("Y-m-d");
            //$query = "SELECT * FROM adeia ad JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND start<'$today' AND finish>'$today'";
            //$query = "SELECT * FROM adeia ad JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND start<'$today' AND finish>'$today' AND status=3";
            $query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE ((start<'$today' AND finish>'$today') OR status=3) ORDER BY finish DESC";
            //echo $query;
            $result = mysqli_query($mysqlconnection, $query);
            $num = mysqli_num_rows($result);
            if ($num)
            {
            echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                echo "<thead><tr>";
                echo "<th>�������</th>";
                echo "<th>�����</th>";
                echo "<th>������</th>";
                echo "<th>�����</th>";
                echo "<th>��/��� ����������</th>";
                echo "<th>������</th>";
                echo "</tr></thead>\n<tbody>";
                $apontes = array();
            while ($row = mysqli_fetch_array($result))
                {
                        $flag = $absent = 0;

                        $id = $row['emap_id'];
                        $adeia_id = $row['id'];
                        $type = $row['type'];
                        $name = $row['name'];
                        $surname = $row['surname'];
                        $klados_id = $row['klados'];
                        $klados = getKlados($klados_id,$mysqlconnection);
                        $comments = $row['comments'];
                        $comm = $comments;
                        $today = date("Y-m-d");
                        $return = $row['fininsh'];
                        $start = $row['start'];
                        $status = $row['status'];
                        // if return date exists, check if absent and print - else continue.

                        if ($start<$today && $return>$today)
                        {
                            $flag = $absent = 1;
                            $apontes[] = $id;
                        }
                        else
                        {
                                //$flag=1;
                                $ret="";
                                if (!in_array($id,$apontes))
                                        $flag = 1;
                                $apontes[] = $id;
                                $comments = "��� ����������.<br>���� ������� ��������� \"�� �����\"<br>";
                        }
                            $ret = date("d-m-Y", strtotime($return));


                        //echo "OK: $i - $start>$today>$return - fl:$flag<br>";
                        if ($flag)
                        {
                            $query1 = "select type from adeia_type where id=$type";
                            $result1 = mysqli_query($mysqlconnection, $query1);
                            $rs = mysqli_fetch_array($result1);
                            $typewrd = $rs['type'];
                            if ($absent && $status<>3)
                                $comments = "<blink>�������� ������� ��� ��������� ��� <br>���/��� �� \"�� �����\"</blink><br>$comm";
                            //if ($absent)
                            //    continue;
                            echo "<tr>";
                            echo "<td><a href=\"employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$typewrd</td><td><a href='adeia.php?adeia=$adeia_id&op=view'>$ret</a></td><td>$comments</td>\n";
                            echo "</tr>";
                        }

                }
                echo "</tbody></table>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
            }
                    

?>
		</center>
		</body>
		</html>
