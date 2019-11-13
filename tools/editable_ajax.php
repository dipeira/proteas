<?php
    Require "../config.php";
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
                
    switch ($_POST['column'])
    {
      case 1:
          $row = "name";
          break;
      case 2:
          $row = "titlos";
          break;
      case 3:
          $row = "address";
          break;
      case 4:
          $row = "tk";
          break;
      case 5:
          $row = "tel";
          break;
      case 6:
          $row = "fax";
          break;
      case 7:
          $row = "email";
          break;
      case 8:
          $row = "organikothta";
          break;
      case 9:
          $row = "leitoyrg";
          break;
      /*case 8:
          $row = "students";
          break;
              case 9:
          $row = "entaksis";
          break;
      case 10:
          $row = "ypodoxis";
          break;
      case 11:
          $row = "frontistiriako";
          break;
      case 12:
          $row = "oloimero";
          break;*/
      case 10:
          $row = "comments";
          break;
    }
        $query = "UPDATE school SET $row='".$_POST['value']."' WHERE id='".$_POST['row_id']."'";
        $res = mysqli_query($conn, $query);
        echo $_POST['value'];
        
    mysqli_close($mysqlconnection);
    ?>
