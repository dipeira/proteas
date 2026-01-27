<?php

// Workaround for the missing mysqli_result function
// Ideal for the transition to mysqli
// taken from https://mariolurig.com/coding/mysqli_result-function-to-match-mysqli_result/
function mysqli_result($res,$row=0,$col=0)
{ 
    if (!$res) return false;
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows-1) && $row >=0) {
        mysqli_data_seek($res, $row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])) {
            return $resrow[$col];
        }
    }
    return false;
}

function show_tooltip($text, $tooltip) {
    echo "<div class='tooltip'>$text";
    echo "<span class='tooltiptext'>$tooltip</span>";
    echo "</div>";
}

function sendEmail($email, $subject, $body, $debug = false, $is_html = false){
    require_once '../vendor/autoload.php';
    require_once '../config.php';
    global $db_user, $db_host, $db_name, $db_password, $db_user;
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  

    // SMTP username & password
    global $smtp_password;
    global $smtp_username;
    global $smtp_server;
    global $smtp_port;
                
    // set up smtp transport
    $transport = Swift_SmtpTransport::newInstance($smtp_server, $smtp_port, 'ssl')
    ->setUsername($smtp_username)
    ->setPassword($smtp_password);

    $mailer = Swift_Mailer::newInstance($transport);
    
    // swiftmailer antiflood plugin (every 25 emails)
    //$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(15));
    // swiftmailer logger
    //$logger = new Swift_Plugins_Loggers_ArrayLogger();
    //$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
    
    // swiftmailer echo logger
    //$logger = new Swift_Plugins_Loggers_EchoLogger();
    //$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
        
    // get & validate email address
    $email = filter_var( $email, FILTER_VALIDATE_EMAIL );
    $from = getParam('foreas',$mysqlconnection);
    $headers = "From:" . $from;
    $mymail = getParam('email',$mysqlconnection);
    
    if ($debug) {
        echo "<br>$subject<br>$body<br>$email<br>$mymail";
        return;
    }

    $message = Swift_Message::newInstance($subject)
    ->setFrom($mymail);
    
    if ($is_html) {
        $message->setContentType("text/html");
    }
    
    // *** SOS *** uncomment '$testemail', comment '$email' to test
    // ->setTo("it@dipe.ira.sch.gr")
    $message->setTo($email)
    ->setBody($body);
    $result = $mailer->send($message);
    return $result;
}

function get_diavgeia_subject($ada) {
    if (!$ada) {
      return;
    }
    //setup the request, you can also use CURLOPT_URL
    $ch = curl_init();
    $mystr = 'https://diavgeia.gov.gr/luminapi/opendata/decisions/'.$ada;
    curl_setopt($ch, CURLOPT_URL, $mystr);
    // Returns the data/output as a string instead of raw data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  
    //Set your auth headers
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //     'Content-Type: application/json',
    //     'Authorization: Bearer ' . $TOKEN
    //     ));
  
    // get stringified data/output. See CURLOPT_RETURNTRANSFER
    $data = curl_exec($ch);
    
    // close curl resource to free up system resources 
    curl_close($ch);
    $dt = json_decode($data);
  
    $ret = str_replace('&', '+', $dt->subject);
    return $ret;
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
} 

function hours_to_teachers($hours)
{
    return round($hours/23, 0);
}

function hours_to_teachers_adaptive($hrs)
{
    $hours = abs($hrs);
    if ($hrs > 0) {
        return '+'.floor($hrs/23) .  ' (+' . ($hours % 23) . ')';
    }
    if (($hours % 23) >= 12) {
        return '-'.ceil($hours/23) . ' (+' . (23 - $hours % 23) . ')';
    } elseif (($hours % 23) < 12){
        $ypol = $hours % 23;
        $floor = floor($hours/23);
        $ret = $floor == 0 ? $floor : '-'.$floor;
        if ($ypol > 0) {
            $ret .=' (-' . $ypol . ')';
        }
        return $ret;
    }
}

// tdc
// prints a <td> with the hours_to_teachers result as a mouseover span
function tdc($val,$colspan = null,$withspan = true)
{
    $cols = $colspan ? "colspan=$colspan" : '';
    $colval = $withspan ? 
        "<span title='".hours_to_teachers($val)."'>$val</span>" :
        $val;
    if ($val == 0) {
        return "<td $cols style='background:none;background-color:rgba(0, 255, 0, 0.37)'>$colval</td>";
    } elseif ($val < 0 ) {
        return "<td $cols style='background:none;background-color:rgba(255, 0, 0, 0.45)'>$colval</td>";
    } else {
        return "<td $cols style='background:none;background-color:rgba(255,255,0,0.3)'>$colval</td>";
    }
}

// tdc2
// prints a <td> with a custom value as a mouseover span
function tdc2($val,$span = null)
{
    $colval = $span ? 
        "<span title='".$span."'>$val</span>" :
        $val;
    if ($val == 0) {
        return "<td style='background:none;background-color:rgba(0, 255, 0, 0.37)'>$colval</td>";
    } elseif ($val < 0 ) {
        return "<td style='background:none;background-color:rgba(255, 0, 0, 0.45)'>$colval</td>";
    } else {
        return "<td style='background:none;background-color:rgba(255,255,0,0.3)'>$colval</td>";
    }
}

/*
* Return previous school year
*/
function find_prev_year($sxoletos)
{
    $tmp = (int)(substr($sxoletos, 0, 4));
    $tmp = (string)($tmp - 1);
    $tmp = $tmp . substr($sxoletos, 2, 2);
    return $tmp;
}

/* display notification
    * JQuery plugin: http://www.9lessons.info/2011/10/jquery-notification-plugin.html
    * type: 0: success, 1: error
    */     
function notify($msg, $type)
{
    $typewrd = $type ? 'error' : 'success';
    echo "<script type=\"text/javascript\">
            $(document).ready(function(){
            showNotification({
            message: '$msg',
            type: '$typewrd',
            autoClose: true,
            duration: 4
            });
        });
        </script>";
}

//get parameter from param table
function getParam($name,$conn)
{
    $query = "SELECT value from params WHERE name='$name'";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        return false;
    }
    return mysqli_result($result, 0, "value");
}
function setParam($name,$value,$conn)
{
    $query = "UPDATE params SET value='$value' WHERE name='$name'";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        return false;
    }
}

function ExcelToPHP($dateValue = 0)
{
    $myExcelBaseDate = 25569;
    //  Adjust for the spurious 29-Feb-1900 (Day 60)
    if ($dateValue < 60) {
        --$myExcelBaseDate;
    }

    // Perform conversion
    if ($dateValue >= 1) {
        $utcDays = $dateValue - $myExcelBaseDate;
        $returnValue = round($utcDays * 86400);
        if (($returnValue <= PHP_INT_MAX) && ($returnValue >= -PHP_INT_MAX)) {
            $returnValue = (integer) $returnValue;
        }
    } else {
        $hours = round($dateValue * 24);
        $mins = round($dateValue * 1440) - round($hours * 60);
        $secs = round($dateValue * 86400) - round($hours * 3600) - round($mins * 60);
        $returnValue = (integer) gmmktime($hours, $mins, $secs);
    }

    // Return
    return $returnValue;
}   //  function ExcelToPHP()

// source: http://code.loon.gr/snippet/php/%CE%BC%CE%B5%CF%84%CE%B1%CF%84%CF%81%CE%BF%CF%80%CE%AE-greek-%CF%83%CE%B5-greeklish
function greek_to_greeklish($string)
{
    return strtr(
        $string, array(
        'Α' => 'A', 'Β' => 'V', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Θ' => 'TH', 'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L',
        'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'KS', 'Ο' => 'O', 'Π' => 'P', 'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F','Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'O',
        'α' => 'a', 'β' => 'v', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'i',
        'θ' => 'th', 'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'ks', 'ο' => 'o', 'π' => 'p', 'ρ' => 'r',
        'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'o', 'ς' => 's',
        'ά' => 'a', 'έ' => 'e', 'ή' => 'i', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ώ' => 'o',
        'ϊ' => 'i', 'ϋ' => 'y','Ϊ' => 'I', 'Ϋ' => 'Y','ΐ' => 'i', 'ΰ' => 'y'
        )
    );
}

function print_latest_commits($lines = 20) {
    //setup the request, you can also use CURLOPT_URL
    $ch = curl_init();
    $apiurl = 'https://api.github.com/repos/dipeira/proteas/commits';
    $url = 'https://github.com/dipeira/proteas/commit/';
    curl_setopt($ch, CURLOPT_URL, $apiurl);
    // Returns the data/output as a string instead of raw data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0');
    
    // get stringified data/output. See CURLOPT_RETURNTRANSFER
    $data = curl_exec($ch);
    // close curl resource to free up system resources 
    curl_close($ch);
    if (!$data)
      return;
  
    $decoded = json_decode($data);
    $dt = array_slice($decoded, 0, $lines);
    
    echo "<div class='info-section' style='margin-top: 16px;'>";
    echo "<h3>🔄 Πρόσφατες αλλαγές - προσθήκες</h3>";
    
    // get latest version (commit) & check with installed version (if installed with git)
    $rev = exec('git rev-parse --short HEAD');
    if (strlen($rev) > 0){
        $row = $dt[0];
        $latest = substr($row->sha,0,7);
        $is_latest = $rev == $latest;
        echo "<div style='padding: 10px 12px; margin-bottom: 12px; border-radius: 6px; background: " . ($is_latest ? "#d1fae5" : "#fef3c7") . "; border-left: 4px solid " . ($is_latest ? "#10b981" : "#f59e0b") . ";'>";
        echo "<strong style='color: " . ($is_latest ? "#065f46" : "#92400e") . ";'>";
        echo $is_latest ? "✓ Εγκατεστημένη έκδοση: $rev - Έχετε εγκαταστήσει την τελευταία έκδοση!" : "⚠️ Εγκατεστημένη έκδοση: $rev - Δεν έχετε εγκαταστήσει την τελευταία έκδοση. Παρακαλώ ενημερώστε!";
        echo "</strong>";
        echo "</div>";
    }
    
    echo "<style>
        .commits-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }
        .commit-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            transition: all 0.2s;
            border-left: 3px solid #3b82f6;
        }
        .commit-item:hover {
            background: #f3f4f6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .commit-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .commit-sha {
            background: #1f2937;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .commit-sha:hover {
            background: #111827;
        }
        .commit-date {
            color: #6b7280;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .commit-message {
            color: #374151;
            font-size: 0.875rem;
            line-height: 1.5;
            word-break: break-word;
        }
        .commit-author {
            color: #6b7280;
            font-size: 0.75rem;
            margin-top: 6px;
        }
        @media (max-width: 768px) {
            .commit-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>";
    
    echo "<div class='commits-list'>";
    foreach ($dt as $row) {
        $shortsha = substr($row->sha,0,7);
        $message = htmlspecialchars($row->commit->message);
        $date = date('d-m-Y, H:i:s',strtotime($row->commit->author->date));
        $author = htmlspecialchars($row->commit->author->name);
        $author_date = strtotime($row->commit->author->date);
        $time_ago = '';
        $diff = time() - $author_date;
        if ($diff < 3600) {
            $time_ago = floor($diff / 60) . ' λεπτά πριν';
        } elseif ($diff < 86400) {
            $time_ago = floor($diff / 3600) . ' ώρες πριν';
        } elseif ($diff < 604800) {
            $time_ago = floor($diff / 86400) . ' ημέρες πριν';
        } elseif ($diff < 2592000) {
            $time_ago = floor($diff / 604800) . ' εβδομάδες πριν';
        } elseif ($diff < 31536000) {
            $time_ago = floor($diff / 2592000) . ' μήνες πριν';
        } else {
            $time_ago = floor($diff / 31536000) . ' έτη πριν';
        }
        
        echo "<div class='commit-item'>";
        echo "<div class='commit-header'>";
        echo "<a href='".$url.$row->sha."' target='_blank' class='commit-sha'>".$shortsha.'</a>';
        echo "<span class='commit-date'>🕒 $date ($time_ago)</span>";
        echo "</div>";
        echo "<div class='commit-message'>".nl2br($message).'</div>';
        echo "<div class='commit-author'>👤 $author</div>";
        echo "</div>";
    }
    echo '</div>';
    echo '</div>';
    return;
}

function days2ymd($input)
{
    $ret[0] = floor($input/360);
    $ret[1] = floor(($input%360)/30);
    $ret[2] = floor(($input%360)%30);
    if ($ret[0] < 0 || $ret[1] < 0 || $ret[2] < 0) {
        return Array(0,0,0);
    }
    return $ret;
}

function days2date($input)
{
    $ret[0] = floor($input/360);
    $ret[1] = floor(($input%360)/30);
    $ret[2] = floor(($input%360)%30);
    if ($ret[2]==0 && $ret[1]==0) {
        $ret[2]=30;
        $ret[1]=12;
        $ret[0]-=1;
    }
    else
        {
        if ($ret[2]==0) {
            $ret[2]=30;
            if ($ret[1]<=1) {
                $ret[1]=12;
                $ret[0]-=1;
            }
            else {
                $ret[1]-=1;
            }
        }
        if ($ret[1]==0) {
            $ret[1]=12;
            $ret[0]-=1;
        }
    }
        return $ret;
}

function date2days($d)
{
    $d = strtotime($d);
    return date('d', $d) + date('m', $d)*30 + date('Y', $d)*360;
}

function exp2excel($data)
{
    $filename ="export.xls";
    header('Content-type: application/ms-excel');
    header('Content-Disposition: attachment; filename='.$filename);
    echo $data;
}


// creates a new record in yphrethsh table for each employee (if there isn't any) - used when changing sxoliko etos
// disp: 0 - none, 1 - basic, 2 - extensive
function do2yphr($mysqlconnection, $disp = 1)
{
    if ($disp) {
        echo "<h3>Πλήρωση πίνακα υπηρετήσεων</h3>";
    }
    set_time_limit(1200);  
    $sxol_etos = getParam('sxol_etos', $mysqlconnection);
    $i = $ins_count = 0;
    $query0 = "SELECT * from employee";
    $result0 = mysqli_query($mysqlconnection, $query0);
    $num = mysqli_num_rows($result0);

    while ($i < $num)
    {
        $id = mysqli_result($result0, $i, "id");
        $sx_yphrethshs = mysqli_result($result0, $i, "sx_yphrethshs");
        $sx_organikhs = mysqli_result($result0, $i, "sx_organikhs");
        $hours = mysqli_result($result0, $i, "wres");
        //$query1 = "select * from yphrethsh WHERE emp_id=$id AND organikh=$sx_organikhs AND sxol_etos=$sxol_etos";
        $query1 = "select * from yphrethsh WHERE emp_id=$id AND sxol_etos=$sxol_etos";
        $result1 = mysqli_query($mysqlconnection, $query1);
        if (!mysqli_num_rows($result1)) {
            $ins_query = "INSERT INTO yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) VALUES ('$id', '$sx_yphrethshs', '$hours', '$sx_organikhs', '$sxol_etos')";
            $result2 = mysqli_query($mysqlconnection, $ins_query);
            if ( $result2 ) $ins_count++;
            if ($disp > 1) {
                echo "$id, ";
            }
        }
        $i++;
    }

    mysqli_close($mysqlconnection);
    if ($disp) {
        echo "<br>$i υπάλληλοι<br>$ins_count αλλαγές...<br>";
    }
}


function display_school_requests($sch, $sxol_etos, $mysqlconnection, $auth = false)
{
    if ($auth){ ?>
      <script type="text/javascript">
        $(document).ready(function(){
          $('.submit-btn').click(function(event) {
              event.preventDefault();
              // do other stuff for a valid form
              var id = event.target.id;
              var name = event.target.name;
              if (name == 'del'){
                var conf = confirm('Είστε σίγουροι;\nΠατήστε ΟΚ για τη διαγραφή του αιτήματος');
                if (conf == true){
                  var theData = {
                    id: id,
                    type: 'delete'
                  }
                } else {
                  return;
                }
              } else {
                var commentid = 'comment'+id;
                var comment = $('#'+commentid).val();
                var doneid = 'done'+id;
                var done = $('#'+doneid).val();
                var theData = {
                    id: id,
                    comment: comment,
                    done: done,
                    school: <?=$sch; ?>,
                    type: 'update'
                };
              }
              $.post('postrequest.php', theData, function(data) {
                  alert(data);
                  location.reload(true);
              });
          });
        });
      </script>
    <?php
    }
    $query = "SELECT * from school_requests where school=$sch AND sxol_etos=$sxol_etos AND hidden = 0 ORDER BY submitted DESC";
    $res = mysqli_query($mysqlconnection, $query);
    if (mysqli_num_rows($res) > 0) {
        echo !$auth ? "<h1>Αιτήματα Σχολικής Μονάδας</h1>" :
        "<h1><a href='requests.php'>Αιτήματα Σχολικής Μονάδας</a></h1>";
        echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Κείμενο αιτήματος</th>";
        echo "<th>Σχόλιο Δ/νσης</th>";
        echo "<th>Διεκπεραίωση</th>";
        echo "<th>Ημ/νία Υποβολής</th>";
        echo "<th>Ημ/νία Διεκπεραίωσης</th>";
        echo $auth ? "<th>Ενέργεια</th>" : '';
        echo "</tr></thead>\n<tbody>";
        while ($row = mysqli_fetch_array($res)){
            echo "<tr>";
            echo "<td>".$row['id']."</td>";
            echo "<td>".nl2br($row['request'])."</td>";
            if ($auth) {
                echo $row['done'] ? "<td>".$row['comment']."</td>" : 
                "<td><textarea id='comment".$row['id']."' name='comment' rows='5' cols='30'>".$row['comment']."</textarea></td>";
            } else {
                "<td>".nl2br($row['comment'])."</td>";
            }
            echo "<td>";
            if ($auth) {
                echo "<select id='done".$row['id']."'>";
                echo $row['done'] ? "<option value='0'>Όχι</option><option value='1' selected>Ναι</option>" :
                    "<option value='0' selected>Όχι</option><option value='1'>Ναι</option>";
            }
            else {
                echo $row['done'] ? 'Ναι' : 'Όχι';
            }
            echo "</td>";
            echo "<td>";
            echo date("d-m-Y H:m:s", strtotime($row['submitted']));
            echo "</td>";
            echo "<td>";
            echo $row['done'] ? date("d-m-Y H:m:s", strtotime($row['handled'])) : '';
            echo "</td>";
            echo $auth ? "<td><input id='".$row['id']."' class='submit-btn' type='submit' value='Υποβολή'><br><input name='del' id='".$row['id']."' class='submit-btn btn-red' type='submit' value='Διαγραφή'></td>" : '';
            echo $auth ? "<input type='hidden' name = 'id' value='".$row['id']."'>" : '';
            $req = str_replace(['\'', '"'], "", $row['request']);
            echo $auth ? "<input type='hidden' name = 'sch_request' value='$req'>" : '';
            echo $auth ? "<input type='hidden' name = 'type' value='update'>" : '';
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else echo "<h3>Δεν έχουν υποβληθεί αιτήματα από τη σχολική μονάδα!</h3>";
}

function shorten_text($text, $length = 200)
{
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

?>