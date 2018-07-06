    <?php
	//$contents = $_POST['data'];
/*
    if (isset ($_POST['data']))
			$contents = $_POST['data'];
		elseif (isset ($_GET['data']))
			$contents = $_GET['data'];
*/
    session_start();
    $contents = $_SESSION['page'];
    $contents = mb_convert_encoding($contents,"utf-8","iso-8859-7");
    $result = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "<b>$2</b>", $contents);

    $filename ="export.xls";
    
	header('Content-type: application/ms-excel');
    header('Content-Disposition: attachment; filename='.$filename);
    //echo $contents;
    echo $result;
    ?>