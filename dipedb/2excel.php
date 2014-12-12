    <?php
	//$contents = $_POST['data'];
	if (isset ($_POST['data']))
			$contents = $_POST['data'];
		elseif (isset ($_GET['data']))
			$contents = $_GET['data'];
	
    $filename ="export.xls";
    
	header('Content-type: application/ms-excel');
    header('Content-Disposition: attachment; filename='.$filename);
    echo $contents;
    ?>