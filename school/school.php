<?php
    header('Content-type: text/html; charset=iso8859-7'); 
    require_once"../config.php";
    require_once"../tools/functions.php";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso8859-7" />
        
        <title>�������</title>
        <style type="text/css" title="currentStyle">
            /* @import "../css/demo_page.css"; */
            @import "../js/datatables/datatables.css.min";
        </style>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" language="javascript" src="../js/jquery.js"></script>
        <script type="text/javascript" language="javascript" src="../js/datatables/jquery.dataTables.js"></script>
        <script type="text/javascript">
          $(document).ready(function() {
              /* Init DataTables */
              $('#school-table').DataTable({
                language: {
                  url: '../js/datatables/greek.json'
                },
                pageLength: 20,
                lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "����"]]
              });
          } );
        </script>
    </head>
    
    <body id="dt_example">
    <?php require '../etc/menu.php'; ?>
    <center>
      <h2>����� ��������</h2>
    </center>
        <div id="container">
<?php
    require_once"../config.php";
    //require_once"../tools/functions.php";
        
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    
    $query = "SELECT * from school ORDER BY type,name ASC";
    $result = mysqli_query($mysqlconnection, $query);
    $num=mysqli_num_rows($result);
    //$i=0;
?>
<div id="demo">
<table cellpadding="0" cellspacing="0" border="1" id="school-table" class='imagetable' style='width:90%'>
    <thead>
      <tr>
        <th style="max-width:50px">��������</th>
        <th style="max-width:200px">�����</th>
        <th>���.����������</th>
        <th>��������</th>
        <th>e-mail</th>
        <th>������������</th>
        <th>�.�./���.</th>
        <th>�����</th>
        <!-- <th>��.�������</th>
        <th>��.��������</th>
        <th>��������.�����</th>
        <th>��������</th> -->
      </tr>
    </thead>
    <tbody>
<?php
while ($row = mysqli_fetch_assoc($result))
{
    echo "<tr id='".$row['id']."' class='gradeA'>";  
    echo "<td>";
    echo "<span title=\"�������\"><a href=\"school_status.php?org=".$row['id']."\"><img style=\"border: 0pt none;\" src=\"../images/view_action.png\"/></a></span>&nbsp;&nbsp;";
    if ($usrlvl < 3) {
          echo "<span title=\"�����������\"><a href=\"school_edit.php?org=".$row['id']."\"><img style=\"border: 0pt none;\" src=\"../images/edit_action.png\"/></a></span>&nbsp;&nbsp;";
    }
    echo "</td>";
    
    echo "\n<td><a href='school_status.php?org=".$row['id']."'>".$row['name']."</a></td>";
    echo "\n<td>".$row['code']."</td>";
    echo "\n<td>".preg_replace('/\s+/', '', $row['tel'])."</td>";
    echo "\n<td><a href='mailto:".$row['email']."'>".$row['email']."</a></td>";
    echo "\n<td>".$row['organikothta']."</td>";
    echo "<td>";
    switch ($row['type']) {
      case '0':
        echo "�����";
        break;
      case '1':
        echo "�.�.";
        break;
      case '2':
        echo "���.";
        break;
    }
    echo "</td>";
    echo "<td>";
    switch ($row['type2']) {
      case '0':
        echo "�������";
        break;
      case '1':
        echo "��������";
        break;
      case '2':
        echo "������";
        break; 
    }
    echo "</td>";
    //echo "\n<td>".$row['entaksis']."</td>";
    //echo "\n<td>".$row['ypodoxis']."</td>";
    //echo "\n<td>".$row['front']."</td>";
    //echo "\n<td>".$row['olo']."</td>";
    echo "\n</tr>";
}
//<tr id="3" class="gradeA"> <tr id="2" class="gradeC"><tr id="1" class="gradeX">
?>
        
    </tbody>
    
</table>
</div>
<div class="spacer"></div>
            

</div>
<INPUT TYPE='button' VALUE='���������' onClick="parent.location='../index.php'">
    </body>
</html>
