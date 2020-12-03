<?php
// url: http://www.emirplicanic.com/php/simple-phpmysql-authentication-class

//start session
session_start();
require_once dirname(__DIR__).'/config.php';

class logmein {
    //database setup
    var $hostname_logon;      //Database server LOCATION
    var $database_logon;       //Database NAME
    var $username_logon;       //Database USERNAME
    var $password_logon;       //Database PASSWORD
    var $db_conn;
 
    //table fields
    var $user_table = 'logon';          //Users table name
    var $user_column = 'username';     //USERNAME column (value MUST be valid email)
    var $pass_column = 'password';      //PASSWORD column
    var $user_level = 'userlevel';      //(optional) userlevel column
 
    //encryption
    var $encrypt = false;       //set to true to use md5 encryption for the password
 
    //connect to database
    function dbconnect(){
        global $db_host, $db_name, $db_user, $db_password;
        $this->hostname_logon = $db_host;
        $this->database_logon = $db_name;
        $this->username_logon = $db_user;
        $this->password_logon = $db_password;
        $this->db_conn = mysqli_connect($this->hostname_logon, $this->username_logon, $this->password_logon, $this->database_logon) or die ('Unabale to connect to the database');
        return;
    }
 
    //login function
    function login($table, $username, $password){
        //conect to DB
        $this->dbconnect();
        //make sure table name is set
        if($this->user_table == ""){
            $this->user_table = $table;
        }
        //check if encryption is used
        if($this->encrypt == true){
            $password = md5($password);
        }
        //execute login via qry function that prevents MySQL injections
        $result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->user_column."='?' AND ".$this->pass_column." = '?';" , $username, $password);
        $row=mysqli_fetch_assoc($result);
        if($row != "Error"){
            if($row[$this->user_column] !="" && $row[$this->pass_column] !=""){
                //register sessions
                //you can add additional sessions here if needed
                $_SESSION['loggedin'] = $row[$this->pass_column];
                $_SESSION['user'] = $row[$this->user_column];
                $_SESSION['userid'] = $row['userid'];
                $_SESSION['adeia'] = $row['adeia'];
                $_SESSION['requests'] = $row['requests'];
                //userlevel session is optional. Use it if you have different user levels
                $_SESSION['userlevel'] = $row[$this->user_level];
                $result = $this->qry("UPDATE ".$this->user_table." SET lastlogin=now() WHERE ".$this->user_column."='?';" , $username, $password);
                return true;
            }else{
                session_destroy();
                return false;
            }
        }else{
            return false;
        }
 
    }
 
    //prevent injection
    function qry($query) {
      $this->dbconnect();
      $args  = func_get_args();
      $query = array_shift($args);
      $query = str_replace("?", "%s", $query);
      // workaround to replace mysql_real_escape_string
      foreach ($args as $arg) {
        $escaped[] = mysqli_real_escape_string($this->db_conn, $arg);
      }
      array_unshift($escaped,$query);
      $query = call_user_func_array('sprintf',$escaped);
      $result = mysqli_query($this->db_conn,$query); //or die(mysqli_error($this->db_conn));
          if($result){
            return $result;
          }else{
             $error = "Error";
             return $result;
          }
    }
 
    //logout function
    function logout(){
        session_destroy();
        return;
    }
 
    //check if loggedin
    function logincheck($logincode, $user_table = null, $pass_column = null, $user_column = null){
        //conect to DB
        $this->dbconnect();
        //make sure password column and table are set
        if($this->pass_column == ""){
            $this->pass_column = $pass_column;
        }
        if($this->user_column == ""){
            $this->user_column = $user_column;
        }
        if($this->user_table == ""){
            $this->user_table = $user_table;
        }
        //exectue query
        $result = $this->qry("SELECT * FROM ".$this->user_table." WHERE ".$this->pass_column." = '?';" , $logincode);
        $rownum = mysqli_num_rows($result);
        //return true if logged in and false if not
        if($rownum != "Error"){
            if($rownum > 0){
                return true;
            }else{
                return false;
            }
        }
    }
 /*
    //reset password
    function passwordreset($username, $user_table, $pass_column, $user_column){
        //connect to DB
        $this->dbconnect();
        //generate new password
        $newpassword = $this->createPassword();
 
        //make sure password column and table are set
        if($this->pass_column == ""){
            $this->pass_column = $pass_column;
        }
        if($this->user_column == ""){
            $this->user_column = $user_column;
        }
        if($this->user_table == ""){
            $this->user_table = $user_table;
        }
        //check if encryption is used
        if($this->encrypt == true){
            $newpassword_db = md5($newpassword);
        }else{
            $newpassword_db = $newpassword;
        }
 
        //update database with new password
        $qry = "UPDATE ".$this->user_table." SET ".$this->pass_column."='".$newpassword_db."' WHERE ".$this->user_column."='".stripslashes($username)."'";
        $result = mysqli_query($qry) or die(mysqli_error($this->db_conn));
 
        $to = stripslashes($username);
        //some injection protection
        $illegals=array("%0A","%0D","%0a","%0d","bcc:","Content-Type","BCC:","Bcc:","Cc:","CC:","TO:","To:","cc:","to:");
        $to = str_replace($illegals, "", $to);
        $getemail = explode("@",$to);
 
        //send only if there is one email
        if(sizeof($getemail) > 2){
            return false;
        }else{
            //send email
            $from = $_SERVER['SERVER_NAME'];
            $subject = "Password Reset: ".$_SERVER['SERVER_NAME'];
            $msg = "
 
Your new password is: ".$newpassword."
 
";
 
            //now we need to set mail headers
            $headers = "MIME-Version: 1.0 rn" ;
            $headers .= "Content-Type: text/html; \r\n" ;
            $headers .= "From: $from  \r\n" ;
 
            //now we are ready to send mail
            $sent = mail($to, $subject, $msg, $headers);
            if($sent){
                return true;
            }else{
                return false;
            }
        }
    }
 
    //create random password with 8 alphanumerical characters
    function createPassword() {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;
        while ($i <= 7) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }
 */
    //login form
    function loginform($formname, $formclass, $formaction){
        //conect to DB
        $this->dbconnect();
        echo'
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
<div class="imgcontainer">
    <img src="../images/logo.png" alt="Avatar" class="avatar">
</div>
<div class="container">
    <label for="username"><b>Όνομα Χρήστη</b></label>
    <input type="text" id="username" placeholder="Εισάγετε όνομα χρήστη" name="username" required>

    <label for="password"><b>Κωδικός</b></label>
    <input type="password" id="password" placeholder="Εισάγετε κωδικό" name="password" required>
    <input name="action" id="action" value="login" type="hidden">
    <button type="submit" id="submit">Είσοδος στο σύστημα</button>
</div>
</form>
';
    }
    //reset password form
    function resetform($formname, $formclass, $formaction){
        //conect to DB
        $this->dbconnect();
        echo'
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
<div><label for="username">Username</label>
<input name="username" id="username" type="text"></div>
<input name="action" id="action" value="resetlogin" type="hidden">
<div>
<input name="submit" id="submit" value="Reset Password" type="submit"></div>
</form>
 
';
    }
}
?>