<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Proteas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="../css/login.css" />
</head>
<body>
<?php
    header('Content-type: text/html; charset=iso8859-7'); 
    require_once("../config.php");
    include("class.login.php");
    $log = new logmein();     //Instentiate the class
    $log->dbconnect();        //Connect to the database

    if ($_GET['logout'])
    {
        $log->logout();
        header("Location: login.php");
    }

    if (!isset($_REQUEST['action']))
    {
        $log->loginform("login", "login-form", "");
    }

    if($_REQUEST['action'] == "login"){
        if($log->login("logon", $_REQUEST['username'], $_REQUEST['password']) == true)
        {        
            header("Location: ../index.php");
        }
    else
        $log->loginform("login", "login-form", "");
        echo "<center><h2>H ������� �������...</h2>";
        echo "<p>�������� ��������� �� ���� ������ ��������� �������� ������ - �������...</p></center>";
    }
?>    
</body>
</html>
