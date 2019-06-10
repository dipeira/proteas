<?php
    header('Content-type: text/html; charset=iso8859-7'); 
    //require_once "../tools/functions.php";
?>
<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <!--
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    -->
    <title>������� ���������� / ������������</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript" src="../js/stickytable.js"></script>
    <script type="text/javascript">    
        $(document).ready(function() { 
            $(".tablesorter").tablesorter({widgets: ['zebra']}); 
            $(".tablesorter").stickyTableHeaders();
        });  
    </script>
  </head>

<?php
    require_once"../config.php";
    require_once"../tools/functions.php";
    session_start();

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");

    echo "<body>";
    require '../etc/menu.php';

    $req_type = $_GET['type'] ? $_GET['type'] : 0;
    echo "<h3>������� ���������� / ������������</h3>";
    echo "<p>�������� �������� ���� ��������:</p>";
    echo $req_type == 0 ? "<b>�������� �������</b><br>" : "<a href='report_head.php?type=0'>�������� �������</a><br>";
    echo $req_type == 1 ? "<b>�����������</b><br>" : "<a href='report_head.php?type=1'>�����������</a><br>";
    echo $req_type == 2 ? '<b>������ �������</b><br>' : "<a href='report_head.php?type=2'>������ �������</a><br>";
    echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
    
    function print_table($result, $num, $mysqlconnection, $mon = true){
      $i=0;
      echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
      echo "<thead>";
      echo "<tr><th>���.</th>";
      echo "<th>��������</th>";
      echo "<th>����.</th>";
      echo "<th>����</th>";
      echo "<th>�������</th>";
      echo "<th>�����</th>";
      echo "<th>������</th>";
      echo "<th>��������</th>";
      echo "</tr>";
      echo "</thead>\n<tbody>\n";

      while ($i < $num)
      {        
          $sid = mysqli_result($result, $i, "sid");
          $sname = mysqli_result($result, $i, "sname");
          $code = mysqli_result($result, $i, "code");
          $leitoyrg = get_leitoyrgikothta($sid, $mysqlconnection);
          $id = mysqli_result($result, $i, "id");
          $name = mysqli_result($result, $i, "name");
          $surname = mysqli_result($result, $i, "surname");
          $thesi = $leitoyrg > 3 ? 
            mysqli_result($result, $i, "thesi") == 2 ? '����������/-�����' : '�������������/-�����' :
            '��/���/-�';
          $klados = getKlados(mysqli_result($result, $i, "klados"),$mysqlconnection);
          $tel = $mon ? mysqli_result($result, $i, "tel") : mysqli_result($result, $i, "stathero") . ' / ' . mysqli_result($result, $i, "kinhto");

          echo "<tr>";
          echo "<td>$code</td>";
          echo "<td><a href='../school/school_status.php?org=$sid' target='_blank'>$sname</a></td>";
          echo "<td>$leitoyrg</td>";
          echo "<td>$thesi</td>";
          $link = $mon ? "../employee/employee.php?id=$id&op=view" : "../employee/ektaktoi.php?id=$id&op=view";
          echo "<td><a href=$link target='_blank'>$surname</td>";
          echo "<td>$name</td>";
          echo "<td>$klados</td>";
          echo "<td>$tel</td>";
          echo "</tr>\n";
          $i++;                        
      }
      echo "</tbody></table>";
    }
    if ($req_type == 1){
      $type = 2;
      $type2 = 0;
    } else {
      $type = 1;
      $type2 = $req_type;
    }
    
    $query = "SELECT s.id as sid, s.code,s.name AS sname, e.* from school s JOIN employee e ON s.id = e.sx_yphrethshs 
    WHERE s.type2 = $type2 AND s.type = $type AND e.thesi IN (1,2) AND status IN (1,3)";
    $query2 = "SELECT s.id as sid, s.code,s.name AS sname, e.* from school s JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
    WHERE s.type2 = $type2 AND s.type = $type AND e.thesi = 1 AND status IN (1,3)";

    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);

    echo "<center>";
    
    ob_start();
    echo "<h2>�������</h2>";
    print_table($result, $num, $mysqlconnection);
    
    echo "<h2>�����������</h2>";
    
    $result = mysqli_query($mysqlconnection, $query2);
    $num = mysqli_num_rows($result);

    print_table($result, $num, $mysqlconnection, false);

    $page = ob_get_contents(); 
    $_SESSION['page'] = $page;
    ob_end_flush();

    echo "<form action='../tools/2excel_ses.php' method='post'>";
    //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>������� ��� excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
    //ob_end_clean();


?>
</body>
</html>
                
