<?php
header('Content-type: text/html; charset=iso8859-7'); 
include("class.login.php");
$log = new logmein();     //Instentiate the class
$log->dbconnect();        //Connect to the database

if ($_GET['logout'])
{
    $log->logout();
    header("Location: login_check.php");
}

if (!isset($_REQUEST['action']))
{
    ?>
<p style="font-weight:normal;color:#000000;background-color:#B5FF6B;border: 4px solid #b5a759;letter-spacing:0pt;word-spacing:2pt;font-size:28px;text-align:center;font-family:trebuchet MS, sans-serif;line-height:2;">Διευθυνση Πρωτοβάθμιας Εκπαίδευσης Ν. Ηρακλείου
    <br>Βάση Δεδομένων Μόνιμου Προσωπικού</p>
<?php
    $log->loginform("login", "id", "");
}

if($_REQUEST['action'] == "login"){
    if($log->login("logon", $_REQUEST['username'], $_REQUEST['password']) == true)
    {

    
    header("Location: ../index.php");
    }
else
    echo "H είσοδος απέτυχε...";
}
?>
