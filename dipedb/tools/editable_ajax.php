<?php
        Require "../config.php";
        $conn = mysql_connect($db_host, $db_user, $db_password);
        mysql_select_db($db_name, $conn);
        mysql_query("SET NAMES 'greek'", $conn);
        mysql_query("SET CHARACTER SET 'greek'", $conn);
                
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
        $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
        $res = mysql_query($query,$conn);
        echo $_POST['value'];
        
        mysql_close();
?>
