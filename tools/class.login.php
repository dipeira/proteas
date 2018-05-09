<?php
// url: http://www.emirplicanic.com/php/simple-phpmysql-authentication-class

//For security reasons, don't display any errors or warnings. Comment out in DEV.
error_reporting(0);
//start session
session_start();
class logmein {
    //database setup
       //MAKE SURE TO FILL IN DATABASE INFO
    var $hostname_logon = 'localhost';      //Database server LOCATION
    var $database_logon = 'dipedb';       //Database NAME
    var $username_logon = 'root';       //Database USERNAME
    var $password_logon = 'd1pe_db';       //Database PASSWORD
 
    //table fields
    var $user_table = 'logon';          //Users table name
    var $user_column = 'username';     //USERNAME column (value MUST be valid email)
    var $pass_column = 'password';      //PASSWORD column
    var $user_level = 'userlevel';      //(optional) userlevel column
 
    //encryption
    var $encrypt = false;       //set to true to use md5 encryption for the password
 
    //connect to database
    function dbconnect(){
        $connections = mysql_connect($this->hostname_logon, $this->username_logon, $this->password_logon) or die ('Unabale to connect to the database');
        mysql_select_db($this->database_logon) or die ('Unable to select database!');
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
        $row=mysql_fetch_assoc($result);
        if($row != "Error"){
            if($row[$this->user_column] !="" && $row[$this->pass_column] !=""){
                //register sessions
                //you can add additional sessions here if needed
                $_SESSION['loggedin'] = $row[$this->pass_column];
                $_SESSION['user'] = $row[$this->user_column];
                $_SESSION['userid'] = $row['userid'];
                $_SESSION['adeia'] = $row['adeia'];
                //userlevel session is optional. Use it if you have different user levels
                $_SESSION['userlevel'] = $row[$this->user_level];
                $result = $this->qry("UPDATE ".$this->user_table." SET lastlogin=now()WHERE ".$this->user_column."='?';" , $username);
                $row=mysql_fetch_assoc($result);
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
      $args  = array_map('mysql_real_escape_string', $args);
      array_unshift($args,$query);
      $query = call_user_func_array('sprintf',$args);
      $result = mysql_query($query) or die(mysql_error());
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
    function logincheck($logincode, $user_table, $pass_column, $user_column){
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
        $rownum = mysql_num_rows($result);
        //return true if logged in and false if not
        if($row != "Error"){
            if($rownum > 0){
                return true;
            }else{
                return false;
            }
        }
    }
 
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
        $result = mysql_query($qry) or die(mysql_error());
 
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
    <label for="username"><b>����� ������</b></label>
    <input type="text" id="username" placeholder="�������� ����� ������" name="username" required>

    <label for="password"><b>�������</b></label>
    <input type="password" id="password" placeholder="�������� ������" name="password" required>
    <input name="action" id="action" value="login" type="hidden">
    <button type="submit" id="submit">������� ��� �������</button>
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