<script type="text/javascript">
	function lookup(inputString) {
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions').hide();
		} else {
			$.post("rpc.php", {queryString: ""+inputString+"", id: "0"}, function(data){
				if(data.length >0) {
					$('#suggestions').show();
					$('#autoSuggestionsList').html(data);
				}
			});
		}
	} // lookup
	
	function fill(thisValue) {
		$('#inputString').val(thisValue);
		setTimeout("$('#suggestions').hide();", 200);
	}
	
	function lookup1(inputString) {
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions1').hide();
		} else {
			$.post("rpc.php", {queryString: ""+inputString+"", id: "1"}, function(data){
				if(data.length >0) {
					$('#suggestions1').show();
					$('#autoSuggestionsList1').html(data);
				}
			});
		}
	} // lookup
	
	function fill1(thisValue) {
		$('#inputString1').val(thisValue);
		setTimeout("$('#suggestions1').hide();", 200);
	}
	
	function confirmDelete(delUrl) {
	  if (confirm("ΠΡΟΣΟΧΗ - Είστε σίγουρος ότι θέλετε να διαγράψετε την εγγραφή;")) {
	   document.location = delUrl;
	  }
	}
</script>

<?php
 
	function getKlados ($id,$conn)
	{
		$query = "SELECT perigrafh from klados where id=".$id;
		$result = mysql_query($query, $conn);
		//if (!$result) 
		//	die('Could not query:' . mysql_error());
		return mysql_result($result, 0);
	}
	
	function getSchool ($id,$conn)
	{
		$query = "SELECT name from school where id=".$id;
		$result = mysql_query($query, $conn);
		//if (!$result) 
		//	die('Could not query:' . mysql_error());
                //else
                    return mysql_result($result, 0);	
	}
	
	function getSchoolID ($name,$conn)
	{
		$query = "SELECT id from school where name='".$name."'";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		else
			return mysql_result($result, 0);	
	}
        
    //The function returns the no. of business days between two dates and it skips the holidays
    function getWorkingDays($startDate,$endDate,$holidays){
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);

        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            }
            else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

            //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
            if ($no_remaining_days > 0 )
            {
            $workingDays += $no_remaining_days;
            }

            //We subtract the holidays
            foreach($holidays as $holiday){
                $time_stamp=strtotime($holiday);
                //If the holiday doesn't fall in weekend
                if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                    $workingDays--;
            }

        return $workingDays;
    }
        
    // 23-12-2013: compute ypoloipo adeiwn - if yphrethsh = foreas adeies = 25 days
    function ypoloipo_adeiwn ($id, $sql)
    {
        $qry2 = "SELECT sx_yphrethshs,thesi FROM employee WHERE id= $id";
        $res2 = mysql_query($qry2, $sql);
        $sx_yphr = mysql_result($res2, 0, "sx_yphrethshs");
        $thesi = mysql_result($res2, 0, "thesi");
        // if apospasmenoi / dioikhtikoi
        if ($sx_yphr == 389 || $sx_yphr == 398 || $thesi == 4)
        {
            $cur_yr = date("Y");
            $prev_yr = $cur_yr - 1;
            $qry = "SELECT sum(days) as rem FROM adeia WHERE TYPE = 2 AND year(START) = $cur_yr AND year(FINISH) = $cur_yr AND emp_id = $id";
            $res = mysql_query($qry, $sql);
            $cur_kan = mysql_result($res, 0, "rem");
            $rem = 25 - $cur_kan;
        
            $qry1 = "SELECT sum(days) as rem FROM adeia WHERE TYPE = 2 AND year(START) = $prev_yr AND year(FINISH) = $prev_yr AND emp_id = $id";
            $res1 = mysql_query($qry1, $sql);
            $prev_kan = mysql_result($res1, 0, "rem");
            $prev_rem = 25 - $prev_kan;
            
            // xmas adeies
            $pre = $after = 0;
            $qry0 = "SELECT start, finish FROM adeia WHERE type=2 AND YEAR(start) = $prev_yr AND YEAR(finish) = $cur_yr AND emp_id = $id";
            $res0 = mysql_query($qry0, $sql);
            if (mysql_num_rows($res0)>0)
            {
                $start = mysql_result($res0, 0, "start");
                $finish = mysql_result($res0, 0, "finish");
                $holidays=array("$prev_yr-12-25","$prev_yr-12-26","$cur_yr-01-01","$cur_yr-01-06");
                $pre = getWorkingDays($start,"$prev_yr-12-31",$holidays);
                $after = getWorkingDays("$cur_yr-01-01",$finish,$holidays);
                //echo "pre: $pre, after: $after<br>";
            }
            $cur_kan += $after;
            $rem -= $after;
            $prev_rem -= $pre;
            
            
            // prev xmas adeies
            $pre = $after = 0;
            $preprev = $prev_yr-1;
            $qry0 = "SELECT start, finish FROM adeia WHERE type=2 AND YEAR(start) = $preprev AND YEAR(finish) = $prev_yr AND emp_id = $id";
            $res0 = mysql_query($qry0, $sql);
            if (mysql_num_rows($res0)>0)
            {
                $start = mysql_result($res0, 0, "start");
                $finish = mysql_result($res0, 0, "finish");
                $holidays=array("$preprev-12-25","$preprev-12-26","$prev_yr-01-01","$prev_yr-01-06");
                $pr_pre = getWorkingDays($start,"$preprev-12-31",$holidays);
                $pr_after = getWorkingDays("$prev_yr-01-01",$finish,$holidays);
                //echo "pre: $pr_pre, after: $pr_after<br>";
            }
            //$cur_kan += $after;
            //$rem -= $after;
            //$prev_rem -= $pre;
            $prev_kan += $pr_after;
            $prev_rem -= $pr_after;
            

            //echo "<small>Υπολ.$cur_yr: $rem, Yπολ.$prev_yr: $prev_rem / Κανονικές $cur_yr: $cur_kan, Κανονικές $prev_yr: $prev_kan</small><br>";
            $ret[2] = $prev_yr;
            $ret[3] = $prev_rem - $cur_kan;
            if ($ret[3]<0)
                $ret[3] = 0;
            
            $ret[0] = $cur_yr;
            $ret[1] = $rem + $prev_rem;
            
        }
        // if ekpaideytikoi
        else
        {
            $cur_yr = date("Y");
            $prev_yr = $cur_yr - 1;
            $qry = "SELECT sum(days) as rem FROM adeia WHERE TYPE = 2 AND year(START) = $cur_yr AND emp_id = $id";
            $res = mysql_query($qry, $sql);
            $rem = mysql_result($res, 0, "rem");
        
            $ret[0] = $cur_yr;
            $ret[1] = 10 - $rem;
            $ret[2] = 0;
        }
        return $ret;
    }
	
	function kladosCombo ($klados,$conn)
	{
		//$query = "SELECT * from klados";
                $query = "SELECT * from klados ORDER BY perigrafh";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$num=mysql_num_rows($result);
		echo "<select name=\"klados\" id=\"klados\">";
                echo "<option value='' selected>(Επιλογή:)</option>";
		while ($i < $num) 
		{
			$id=mysql_result($result, $i, "id");
			$per=mysql_result($result, $i, "perigrafh");
			if (strcmp($klados,$id)==0)
				echo "<option value=\"".$id."\" selected=\"selected\">".$per."</option>";
			else
				echo "<option value=\"".$id."\">".$per."</option>";
		$i++;
		}
		echo "</select>";
	}
	function kladosCmb ($conn)
	{
		$query = "SELECT * from klados ORDER BY perigrafh";
                //$query = "SELECT * from klados";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$num=mysql_num_rows($result);
		echo "<select style='max-width: 97px;' name=\"klados\" id=\"klados\">";
		echo "<option value='' selected>(Επιλογή:)</option>";
		while ($i < $num) 
		{
			$id=mysql_result($result, $i, "id");
			$per=mysql_result($result, $i, "perigrafh");
			$onoma=mysql_result($result, $i, "onoma");
			echo "<option value=\"".$id."\">".$per.", ".$onoma."</option>";
		$i++;
		}
		echo "</select>";
	}
    function typeCmb ($conn)
	{
		$query = "SELECT * from ektaktoi_types";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$num=mysql_num_rows($result);
		echo "<select name=\"type\" id=\"type\">";
		echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		while ($i < $num) 
		{
			$id=mysql_result($result, $i, "id");
			$type=mysql_result($result, $i, "type");
			echo "<option value=\"".$id."\">".$type."</option>";
		$i++;
		}
		echo "</select>";
	}
    function typeCmb1 ($typeinp,$conn)
	{
		$query = "SELECT * from ektaktoi_types";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$num=mysql_num_rows($result);
                $type1 = get_type($typeinp, $conn);
		echo "<select name=\"type\" id=\"type\">";
		echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		while ($i < $num) 
		{
			$id=mysql_result($result, $i, "id");
			$type=mysql_result($result, $i, "type");
                        if ($type1 == $type)
                            echo "<option value=\"$id\" selected>".$type."</option>";
                        else
                            echo "<option value=\"".$id."\">".$type."</option>";
		$i++;
		}
		echo "</select>";
	}
	function vathmosCmb ($conn)
	{
		echo "<select name=\"vathm\">";
		echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		echo "<option value=\"Γ\">Γ</option>";
		echo "<option value=\"Β\">Β</option>";
		echo "<option value=\"Α\">Α</option>";
		echo "</select>";
	}
	function vathmosCmb1 ($v, $conn)
	{
		echo "<select name=\"vathm\">";
		if (strcmp($v,'Γ')==0)
			echo "<option value=\"Γ\" selected>Γ</option>";
		else
			echo "<option value=\"Γ\">Γ</option>";
		if (strcmp($v,'Β')==0)
			echo "<option value=\"Β\" selected>Β</option>";
		else
			echo "<option value=\"Β\">Β</option>";
		if (strcmp($v,'Α')==0)
			echo "<option value=\"Α\" selected>Α</option>";
		else
			echo "<option value=\"Α\">Α</option>";
		echo "</select>";
	}
    function taksiCmb ()
	{
		echo "<select name=\"taksi\">";
		echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		echo "<option value=\"1\">Α</option>";
                echo "<option value=\"2\">Β</option>";
                echo "<option value=\"3\">Γ</option>";
                echo "<option value=\"4\">Δ</option>";
                echo "<option value=\"5\">Ε</option>";
                echo "<option value=\"6\">ΣΤ</option>";
		echo "</select>";
	}
	function taksiCmb1 ($t)
	{
		echo "<select name=\"taksi\">";
		if ($t == 1)
			echo "<option value=\"1\" selected>Α</option>";
		else
			echo "<option value=\"1\">Α</option>";
                if ($t == 2)
			echo "<option value=\"2\" selected>Β</option>";
		else
			echo "<option value=\"2\">Β</option>";
                if ($t == 3)
			echo "<option value=\"3\" selected>Γ</option>";
		else
			echo "<option value=\"3\">Γ</option>";
                if ($t == 4)
			echo "<option value=\"4\" selected>Δ</option>";
		else
			echo "<option value=\"4\">Δ</option>";
		if ($t == 5)
			echo "<option value=\"5\" selected>Ε</option>";
		else
			echo "<option value=\"5\">Ε</option>";
                if ($t == 6)
                	echo "<option value=\"6\" selected>ΣΤ</option>";
		else
			echo "<option value=\"6\">ΣΤ</option>";
		echo "</select>";
	}
	function metdidCombo ($met_did)
	{
		echo "<select name=\"met_did\">";
		if ($met_did == 0)
		{
			echo "<option value='0' selected=\"selected\">Όχι</option>";
			echo "<option value='1'>Μεταπτυχιακό</option>";
			echo "<option value='2'>Διδακτορικό</option>";			
			echo "<option value='3'>Μετ. & Διδ.</option>";
		}
		elseif ($met_did == 1)
		{
			echo "<option value='0'></option>";
			echo "<option value='1' selected=\"selected\">Μεταπτυχιακό</option>";
			echo "<option value='2'>Διδακτορικό</option>";
			echo "<option value='3'>Μετ. & Διδ.</option>";
		}
		elseif ($met_did == 2)
		{
			echo "<option value='0'></option>";
			echo "<option value='1'>Μεταπτυχιακό</option>";
			echo "<option value='2' selected=\"selected\">Διδακτορικό</option>";			
			echo "<option value='3'>Μετ. & Διδ.</option>";
		}
		elseif ($met_did == 3)
		{
			echo "<option value='0'></option>";
			echo "<option value='1'>Μεταπτυχιακό</option>";
			echo "<option value='2'>Διδακτορικό</option>";			
			echo "<option value='3' selected=\"selected\">Μετ. & Διδ.</option>";
		}
		echo "</select>";
	}
	function opsel()
	{
		echo "<select name=\"op\">";
		echo "<option value=\"=\" selected>=</option>";
		echo "<option value=\">\" >></option>";
		echo "<option value=\"<\" ><</option>";
		echo "</select>";
	}
    function thesicmb($thesi)
    {
        switch ($thesi)
            {
                case 0:
                    $th = "Εκπαιδευτικός";
                    break;
                case 1:
                    $th = "Υποδιευθυντής";
                    break;
                case 2:
                    $th = "Διευθυντής/Προϊστάμενος";
                    break;
                case 3:
                    $th = "Τμήμα Ένταξης";
                    break;
                case 4:
                    $th = "Διοικητικός";
                    break;
                case 5:
                    $th = "Ιδιωτικός";
                    break;
                case 6:
                    $th = "Δ/ντής-Πρ/νος Ιδιωτικού Σχ.";
                    break;
            }
            return $th;
    }
    function thesiselectcmb($thesi)
    {
        echo "<tr><td>Θέση</td><td>";
        echo "<select name=\"thesi\">";
        if ($thesi == 0)
            echo "<option value='0' selected=\"selected\">Εκπαιδευτικός</option>";
        else
            echo "<option value='0'>Εκπαιδευτικός</option>";
        if ($thesi == 1)
            echo "<option value='1' selected=\"selected\">Υποδιευθυντής</option>";
        else
            echo "<option value='1'>Υποδιευθυντής</option>";
        if ($thesi == 2)
            echo "<option value='2' selected=\"selected\">Διευθυντής/Προϊστάμενος</option>";	
        else
            echo "<option value='2'>Διευθυντής/Προϊστάμενος</option>";
        if ($thesi == 3)
            echo "<option value='3' selected=\"selected\">Τμήμα Ένταξης</option>";	
        else
            echo "<option value='3'>Τμήμα Ένταξης</option>";
        if ($thesi == 4)
            echo "<option value='4' selected=\"selected\">Διοικητικός</option>";	
        else
            echo "<option value='4'>Διοικητικός</option>";
        if ($thesi == 5)
            echo "<option value='5' selected=\"selected\">Ιδιωτικός</option>";	
        else
            echo "<option value='5'>Ιδιωτικός</option>";
        if ($thesi == 6)
            echo "<option value='6' selected=\"selected\">Δ/ντής-Πρ/νος Ιδιωτικού Σχ.</option>";	
        else
            echo "<option value='6'>Δ/ντής-Πρ/νος Ιδιωτικού Σχ.</option>";
    }
	
	
	function schoolCombo ($schid,$conn)
	{
		$query = "SELECT * from school";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$num=mysql_num_rows($result);
		echo "<select name=\"school\">";
		while ($i < $num) 
		{
			$id=mysql_result($result, $i, "id");
			$name=mysql_result($result, $i, "name");
			if (strcmp($schid,$id)==0)
				echo "<option value=\"".$id."\" selected=\"selected\">".$name."</option>";
			else
				echo "<option value=\"".$id."\">".$name."</option>";
		$i++;
		}
		echo "</select>";
	}
	
	function schCombo ($name1,$conn)
	{
		$query = "SELECT * from school";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$num=mysql_num_rows($result);
		echo "<select name='$name1'>";
		echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		while ($i < $num) 
		{
			$id=mysql_result($result, $i, "id");
			$name=mysql_result($result, $i, "name");
			echo "<option value=\"".$id."\">".$name."</option>";
		$i++;
		}
		echo "</select>";
	}
        function get_type ($typeid,$conn)
	{
		$query = "SELECT * from ektaktoi_types WHERE id=$typeid";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$typos=mysql_result($result, $i, "type");
		return $typos;
	}
        function getDimos ($id,$conn)
	{
		$query = "SELECT name from dimos where id=".$id;
		$result = mysql_query($query, $conn);
		//if (!$result) 
		//	die('Could not query:' . mysql_error());
                //else
                $dimos = mysql_result($result, 0);
                if (!$dimos)
                    return "¶γνωστος";
                else
                    return $dimos;
	}
        function katastCmb ($v)
	{
		echo "<select name=\"status\">";
		if ($v==1)
			echo "<option value=\"1\" selected>Εργάζεται</option>";
		else
			echo "<option value=\"1\">Εργάζεται</option>";
		if ($v==2)
			echo "<option value=\"2\" selected>Λύση Σχέσης - Παραίτηση</option>";
		else
			echo "<option value=\"2\">Λύση Σχέσης - Παραίτηση</option>";
                if ($v==3)
			echo "<option value=\"3\" selected>¶δεια</option>";
		else
			echo "<option value=\"3\">¶δεια</option>";
                if ($v==4)
			echo "<option value=\"4\" selected>Διαθεσιμότητα</option>";
		else
			echo "<option value=\"4\">Διαθεσιμότητα</option>";
		echo "</select>";
	}
	
    function adeiaCmb ($inp,$conn,$ekt = 0)
	{
		$query = $ekt ? "SELECT * from adeia_ekt_type" : "SELECT * from adeia_type";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$num=mysql_num_rows($result);
		echo "<select id='type' name=\"type\" >";
		while ($i < $num) 
		{
			$id=mysql_result($result, $i, "id");
			$type=mysql_result($result, $i, "type");
			if (strcmp($id,$inp)==0)
				echo "<option value=\"".$id."\" selected=\"selected\">".$type."</option>";
			else
				echo "<option value=\"".$id."\">".$type."</option>";
		$i++;
		}
		echo "</select>";
	}
        
    function days2ymd ($input)
	{
		$ret[0] = floor ($input/360);
		$ret[1] = floor (($input%360)/30);
		$ret[2] = floor (($input%360)%30);
                return $ret;
    }

	function days2date ($input)
	{
		$ret[0] = floor ($input/360);
		$ret[1] = floor (($input%360)/30);
		$ret[2] = floor (($input%360)%30);
            if ($ret[2]==0 && $ret[1]==0)
            {
                $ret[2]=30;
                $ret[1]=12;
                $ret[0]-=1;
            }
            else
            {
                if ($ret[2]==0)
                {
                    $ret[2]=30;
                    if ($ret[1]<=1)
                    {
                        $ret[1]=12;
                        $ret[0]-=1;
                    }
                    else
                        $ret[1]-=1;
                }
                if ($ret[1]==0)
                {
                    $ret[1]=12;
                    $ret[0]-=1;
                }
            }
		return $ret;
	}
	// vathmos -> ret[0]: vathmos, ret[1]: days (pleonazwn sto vathmo)
	function vathmos($days)
	{
		// =IF(R3<1080;"ΣΤ";IF(R3<3240;"Ε";IF(R3<5400;"Δ";IF(R3<7560;"Γ";"Β"))))
		switch ($days)
		{
			case ($days<1080):
				$ret[0] = "ΣΤ";
				break;
			case ($days>=1080 && $days<3240):
				$ret[0] = "Ε";
				$ret[1] = $days-1080;
				break;
			case ($days>=3240 && $days<5400):
				$ret[0] = "Δ";
				$ret[1] = $days-3240;
				break;
			case ($days>=5400 && $days<7560):
				$ret[0] = "Γ";
				$ret[1] = $days-5400;
				break;
			default:
				$ret[0] = "Β";
				$ret[1] = $days-7560;
				break;
		}
		return $ret;
	}
	function mk_kat($days,$vathmos)         // mk katatakshs
	{
		switch ($vathmos[0])
		{
			case ("ΣΤ"):	// ΣΤ'
				$mk = 0;
				break;
			case ("Ε"):	// Ε'
				switch ($days)
				{
					case ($days>=1080 && $days<1800):
						$mk = 0;
						break;
					case ($days>=1800 && $days<2520):
						$mk = 1;
						break;
					case ($days>=2520):
						$mk = 2;
						break;
				}
                                break;
			case ("Δ"):  // Δ'
				switch ($days)
				{
					case ($days>=3240 && $days<3960):
						$mk = 0;
						break;
					case ($days>=3960 && $days<4680):
						$mk = 1;
						break;
					case ($days>=4680):
						$mk = 2;
						break;
				}
                                break;
			case ("Γ"):	// Γ'
				switch ($days)
				{
					case ($days>=5400 && $days<6120):
						$mk = 0;
						break;
					case ($days>=6120 && $days<6840):
						$mk = 1;
						break;
					case ($days>=6840):
						$mk = 2;
						break;
				}
                                break;
			case ("Β"):	// Β'
				switch ($days)
				{
					case ($days>=7560 && $days<8640):
						$mk = 0;
						break;
					case ($days>=8640 && $days<9720):
						$mk = 1;
						break;
					case ($days>=9720 && $days<10800):
						$mk = 2;
						break;
					case ($days>=10800 && $days<11880):
						$mk = 3;
						break;
					case ($days>=11880 && $days<12960):
						$mk = 4;
						break;
					case ($days>=12960 && $days<14040):
						$mk = 5;
						break;
					case ($days>=14040):
						$mk = 6;
						break;
				}
                                break;
		}
		return $mk;
                
	}
    // 16-12-2013 added Γ4
    function mk($days,$vathmos)
	{
		switch ($vathmos[0])
		{
			case ("ΣΤ"):	// ΣΤ'
				$mk = 0;
				break;
			case ("Ε"):	// Ε'
				switch ($days)
				{
					case ($days>=1080 && $days<1800):
						$mk = 0;
						break;
					case ($days>=1800 && $days<2520):
						$mk = 1;
						break;
					case ($days>=2520):
						$mk = 2;
						break;
				}
                                break;
			case ("Δ"):  // Δ'
				switch ($days)
				{
					case ($days>=3240 && $days<3960):
						$mk = 0;
						break;
					case ($days>=3960 && $days<4680):
						$mk = 1;
						break;
					case ($days>=4680 && $days<5400):
						$mk = 2;
						break;
                                        case ($days>=5400):
						$mk = 3;
						break;
				}
                                break;
			case ("Γ"):	// Γ'
				switch ($days)
				{
					case ($days>=5400 && $days<6120):
						$mk = 0;
						break;
					case ($days>=6120 && $days<6840):
						$mk = 1;
						break;
                                        case ($days>=6840 && $days<7560):
						$mk = 2;
						break;
                                        case ($days>=7560 && $days<8280):
						$mk = 3;
						break;
                                        case ($days>=8280):
						$mk = 4;
						break;
				}
                                break;

                                
			case ("Β"):	// Β'
				switch ($days)
				{
					case ($days>=7560 && $days<8640):
						$mk = 0;
						break;
					case ($days>=8640 && $days<9720):
						$mk = 1;
						break;
					case ($days>=9720 && $days<10800):
						$mk = 2;
						break;
					case ($days>=10800 && $days<11880):
						$mk = 3;
						break;
					case ($days>=11880 && $days<12960):
						$mk = 4;
						break;
					case ($days>=12960 && $days<14040):
						$mk = 5;
						break;
					case ($days>=14040):
						$mk = 6;
						break;
				}
                                break;
		}
		return $mk;        
	}
        
    // mk_plus: returns mk[0]: mk & mk[1]:days in mk
    // 16-12-2013 added Γ4
    function mk_plus($days,$vathmos)
	{
		switch ($vathmos)
		{
			case ("ΣΤ"):	// ΣΤ'
				$mk[0] = 0;
				break;
			case ("Ε"):	// Ε'
				switch ($days)
				{
					case ($days>=1080 && $days<1800):
						$mk[0] = 0;
                                                $mk[1] = $days - 1080;
						break;
					case ($days>=1800 && $days<2520):
						$mk[0] = 1;
                                                $mk[1] = $days - 1800;
						break;
					case ($days>=2520):
						$mk[0] = 2;
                                                $mk[1] = $days - 2520;
						break;
				}
                                break;
                        
			case ("Δ"):  // Δ'
				switch ($days)
				{
					case ($days>=3240 && $days<3960):
						$mk[0] = 0;
                                                $mk[1] = $days - 3240;
						break;
					case ($days>=3960 && $days<4680):
						$mk[0] = 1;
                                                $mk[1] = $days - 3960;
						break;
					case ($days>=4680 && $days<5400):
						$mk[0] = 2;
                                                $mk[1] = $days - 4680;
						break;
                                        case ($days>=5400):
						$mk[0] = 3;
                                                $mk[1] = $days - 5400;
						break;
				}
                                break;
                        
			case ("Γ"):	// Γ'
				switch ($days)
				{
					case ($days>=5400 && $days<6120):
						$mk[0] = 0;
                                                $mk[1] = $days - 5400;
						break;
					case ($days>=6120 && $days<6840):
						$mk[0] = 1;
                                                $mk[1] = $days - 6120;
						break;
                                        case ($days>=6840 && $days<7560):
						$mk[0] = 2;
                                                $mk[1] = $days - 6840;
						break;
					case ($days>=7560 && $days<8280):
						$mk[0] = 3;
                                                $mk[1] = $days - 7560;
						break;
                                        case ($days>=8280):
						$mk[0] = 4;
                                                $mk[1] = $days - 8280;
						break;
				}
                                break;
                        
			case ("Β"):	// Β'
				switch ($days)
				{
					case ($days>=7560 && $days<8640):
						$mk[0] = "0";
                                                $mk[1] = $days - 7560;
						break;
					case ($days>=8640 && $days<9720):
						$mk[0] = 1;
                                                $mk[1] = $days - 8640;
						break;
					case ($days>=9720 && $days<10800):
						$mk[0] = 2;
                                                $mk[1] = $days - 9720;
						break;
					case ($days>=10800 && $days<11880):
						$mk[0] = 3;
                                                $mk[1] = $days - 10800;
						break;
					case ($days>=11880 && $days<12960):
						$mk[0] = 4;
                                                $mk[1] = $days - 11880;
						break;
					case ($days>=12960 && $days<14040):
						$mk[0] = 5;
                                                $mk[1] = $days - 12960;
						break;
					case ($days>=14040):
						$mk[0] = 6;
                                                $mk[1] = $days - 14040;
						break;
				}
                                break;
                         case ("Α"):
                             $mk[0]=0;
                             $mk[1]=0;
                             break;
		}
		return $mk;
    }
    
    function mk_plus_new($days,$vathmos)
	{
		switch ($vathmos)
		{
			case ("ΣΤ"):	// ΣΤ'
				$mk[0] = 0;
				break;
			case ("Ε"):	// Ε'
				switch ($days)
				{
					case ($days>=1080 && $days<=1800):
						$mk[0] = 0;
                                                $mk[1] = $days - 1080;
						break;
					case ($days>1800 && $days<=2520):
						$mk[0] = 1;
                                                $mk[1] = $days - 1800;
						break;
					case ($days>2520):
						$mk[0] = 2;
                                                $mk[1] = $days - 2520;
						break;
				}
                                break;
                        
			case ("Δ"):  // Δ'
				switch ($days)
				{
					case ($days>=3240 && $days<=3960):
						$mk[0] = 0;
                                                $mk[1] = $days - 3240;
						break;
					case ($days>3960 && $days<=4680):
						$mk[0] = 1;
                                                $mk[1] = $days - 3960;
						break;
					case ($days>4680 && $days<=5400):
						$mk[0] = 2;
                                                $mk[1] = $days - 4680;
						break;
                                        case ($days>5400):
						$mk[0] = 3;
                                                $mk[1] = $days - 5400;
						break;
				}
                                break;
                        
			case ("Γ"):	// Γ'
				switch ($days)
				{
					case ($days>=5400 && $days<=6120):
						$mk[0] = 0;
                                                $mk[1] = $days - 5400;
						break;
					case ($days>6120 && $days<=6840):
						$mk[0] = 1;
                                                $mk[1] = $days - 6120;
						break;
                                        case ($days>6840 && $days<=7560):
						$mk[0] = 2;
                                                $mk[1] = $days - 6840;
						break;
					case ($days>7560):
						$mk[0] = "3";
                                                $mk[1] = $days - 7560;
						break;
				}
                                break;
                        
			case ("Β"):	// Β'
				switch ($days)
				{
					case ($days>=7560 && $days<=8640):
						$mk[0] = "0";
                                                $mk[1] = $days - 7560;
						break;
					case ($days>8640 && $days<=9720):
						$mk[0] = 1;
                                                $mk[1] = $days - 8640;
						break;
					case ($days>9720 && $days<=10800):
						$mk[0] = 2;
                                                $mk[1] = $days - 9720;
						break;
					case ($days>10800 && $days<=11880):
						$mk[0] = 3;
                                                $mk[1] = $days - 10800;
						break;
					case ($days>11880 && $days<=12960):
						$mk[0] = 4;
                                                $mk[1] = $days - 11880;
						break;
					case ($days>12960 && $days<=14040):
						$mk[0] = 5;
                                                $mk[1] = $days - 12960;
						break;
					case ($days>14040):
						$mk[0] = 6;
                                                $mk[1] = $days - 14040;
						break;
				}
                                break;
                         case ("Α"):
                             $mk[0]=0;
                             $mk[1]=0;
                             break;
		}
		return $mk;
	}
    // mk16: Function for N.4354/2015
    // returns new MK
    function mk16($days) {
        // @excel: =INT(Q2/2)+1
        $years = floor ($days/360);
        $mk = floor($years/2) + 1;
        return $mk > 19 ? 19 : $mk;
    }
    // mk16_plus: Function for N.4354/2015
    // returns new MK and days since last MK (pleonazwn)
    function mk16_plus($days) {
        // @excel: =INT(Q2/2)+1
        $years = floor ($days/360);
        $mk = floor($years/2) + 1;
        $ret[0] = $mk > 19 ? 19 : $mk;
        $ret[1] = $days - (($mk * 2) - 2);
        print_r($ret);
        return ret;
    }
        
	function exp2excel ($data)
	{
		$filename ="export.xls";
		header('Content-type: application/ms-excel');
		header('Content-Disposition: attachment; filename='.$filename);
		echo $data;
	}
	
	function ypol_yphr($yphr,$anatr)
	{
		$d1 = strtotime($yphr);
		$result = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $anatr;
		if ($result<=0)
		echo "Λάθος ημερομηνία";
		else
		{
			$ymd=days2ymd($result);	
			//return $ymd;
			$ret = "Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]";
			return $ret;
		}
	}
        
    function ExcelToPHP($dateValue = 0) {
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
                    return strtr($string, array(
                                    'Α' => 'A', 'Β' => 'V', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Θ' => 'TH', 'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L',
                                    'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'KS', 'Ο' => 'O', 'Π' => 'P', 'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F','Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'O',
                                    'α' => 'a', 'β' => 'v', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'i',
                                    'θ' => 'th', 'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'ks', 'ο' => 'o', 'π' => 'p', 'ρ' => 'r',
                                    'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'o', 'ς' => 's',
                                    'ά' => 'a', 'έ' => 'e', 'ή' => 'i', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ώ' => 'o','ϊ' => 'i', 'ϋ' => 'y',
                                    'ΐ' => 'i', 'ΰ' => 'y'
                    ));
    }
        
    // generic combo function
    function tblCmb ($conn,$tbl,$inp = 0,$fieldnm)
	{
		$query = "SELECT * from $tbl";
		$result = mysql_query($query, $conn);
		if (!$result) 
			die('Could not query:' . mysql_error());
		$num=mysql_num_rows($result);
                echo $fieldnm ? "<select id=\"$fieldnm\" name=\"$fieldnm\" >" : "<select id=\"$tbl\" name=\"$tbl\" >";
		//echo "<select id=\"$tbl\" name=\"$tbl\" onchange='replace()' >";
                echo "<option value=\"\"> </option>";
		while ($i < $num) 
		{
			$id=mysql_result($result, $i, "id");
			$name=mysql_result($result, $i, "name");
			if ($id==$inp)
				echo "<option value=\"".$id."\" selected=\"selected\">".$name."</option>";
			else
				echo "<option value=\"".$id."\">".$name."</option>";
		$i++;
		}
		echo "</select>";
	}
    function getNamefromTbl ($conn, $tbl, $id)
    {
        $query = "SELECT * from $tbl WHERE id=$id";
        $result = mysql_query($query, $conn);
        if (!$result) 
    die('Could not query:' . mysql_error());
        $name=mysql_result($result, 0, "name");
        return $name;
    }
    function getIDfromTbl ($conn, $tbl, $name)
    {
        $query = "SELECT * from $tbl WHERE name=$name";
        $result = mysql_query($query, $conn);
        if (!$result) 
    die('Could not query:' . mysql_error());
        $id=mysql_result($result, 0, "id");
        return $id;
    }
    //get parameter from param table
    function getParam($name,$conn)
    {
        $query = "SELECT value from params WHERE name='$name'";
        $result = mysql_query($query, $conn);
        if (!$result) 
    die('Could not query:' . mysql_error());
        return mysql_result($result, 0, "value");
    }
    function setParam($name,$value,$conn)
    {
        $query = "UPDATE params SET value='$value' WHERE name='$name'";
        $result = mysql_query($query, $conn);
        if (!$result) 
    die('Could not query:' . mysql_error());
    }
    // creates a new record in yphrethsh table for each employee (if there isn't any) - used when changing sxoliko etos
    // disp: 0 - none, 1 - basic, 2 - extensive
    function do2yphr($mysqlconnection, $disp = 1)
    {
        if ($disp)
            echo "<h3>Πλήρωση πίνακα υπηρετήσεων</h3>";
        set_time_limit(1200);  
        $sxol_etos = getParam('sxol_etos', $mysqlconnection);
        $i = $ins_count = 0;
        $query0 = "SELECT * from employee";
        $result0 = mysql_query($query0, $mysqlconnection);
        $num = mysql_num_rows($result0);

        while ($i < $num)
        {
            $id = mysql_result($result0, $i, "id");
            $sx_yphrethshs = mysql_result($result0, $i, "sx_yphrethshs");
            $sx_organikhs = mysql_result($result0, $i, "sx_organikhs");
            $hours = mysql_result($result0, $i, "wres");
            //$query1 = "select * from yphrethsh WHERE emp_id=$id AND organikh=$sx_organikhs AND sxol_etos=$sxol_etos";
            $query1 = "select * from yphrethsh WHERE emp_id=$id AND sxol_etos=$sxol_etos";
            $result1 = mysql_query($query1, $mysqlconnection);
            if (!mysql_num_rows($result1))
            {
                $ins_query = "INSERT INTO yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) VALUES ('$id', '$sx_yphrethshs', '$hours', '$sx_organikhs', '$sxol_etos')";
                $ins_result = mysql_query($ins_query, $mysqlconnection);
                $ins_count++;
                if ($disp > 1)
                    echo "$id, ";
            }
            $i++;
        }

        mysql_close();
        if ($disp)
            echo "<br>$i υπάλληλοι<br>$ins_count αλλαγές...<br>";
    }
    // returns school category
    function getCategory($cat){
        switch ($cat){
            case 0:
                return "¶γνωστο";
                exit;
            case 1:
                return "Α' ($cat)";
                exit;
            case 2:
                return "Β' ($cat)";
                exit;
            case 3:
                return "Γ' ($cat)";
                exit;
            case 4:
                return "Δ' ($cat)";
                exit;
            case 5:
                return "Ε' ($cat)";
                exit;
            case 6:
                return "ΣΤ' ($cat)";
                exit;
            case 7:
                return "Ζ' ($cat)";
                exit;
            case 8:
                return "Η' ($cat)";
                exit;
            case 9:
                return "Θ' ($cat)";
                exit;
        }
    }
    /* display notification
    * JQuery plugin: http://www.9lessons.info/2011/10/jquery-notification-plugin.html
    * type: 0: success, 1: error
    */        
    function notify($msg, $type){
        $typewrd = $type ? 'error' : 'success';
        echo "<script type=\"text/javascript\">
            $(document).ready(function(){
            showNotification({
            message: '$msg',
            type: '$typewrd',
            autoClose: true,
            duration: 3
            });
        });
        </script>";
        }
    /*
    * check for expired adeies idiwtikoy ergoy
    */
    function check_idiwtiko($conn){
        $ret = "";
        $query = "SELECT id,surname,name,idiwtiko_liksi from employee WHERE idiwtiko=1 AND idiwtiko_liksi <= curdate()";
        $result = mysql_query($query, $conn);
        if (!$result) 
        die('Could not query:' . mysql_error());
        if (!mysql_num_rows($result))
            return "";
        else
            $ret = "Οι παρακάτω εκπ/κοί έχουν άδεια ιδιωτικού έργου σε δημόσιο φορέα που έχει λήξει:<br>";
        while ($row = mysql_fetch_array($result, MYSQL_BOTH)){
            $ret .= "<small><a href=\"employee.php?id=".  $row['id'] ."&op=view\" target=\"_blank\">". $row['surname'] ." ". $row['name'] ."</a></small><br>";
        }
        return $ret;
    }
    /*
    * Return previous school year
    */
    function find_prev_year($sxoletos){
        $tmp = (int)(substr($sxoletos,0,4));
        $tmp = (string)($tmp - 1);
        $tmp = $tmp . substr($sxoletos,2,2);
        return $tmp;
    }
    /*
    * Wres_tmimata: Compute required hours based on oloimero schedule 2016-17
    * Returns required hours depending on number of classes
    * tmimata: 0: A, 1: B, 2: Γ, 3: Δ, 4: E, 5: ΣΤ
    * 6: Ολ. 15.00, 7: Ολ. 16:00, 8: ΠΖ
    */
    function anagkes1617($tm){
        $artm = $tm[0]+$tm[1]+$tm[2]+$tm[3]+$tm[4]+$tm[5];
        // 4/thesia
        if ($artm == 4){
        $hours = [];
        $hours['05-07'] = $tm[4]*1;
        $hours['06'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*3 + $tm[4]*3;
        $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[4]*1;
        $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[4]*2;
        $hours['16'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
        $hours['32'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
        $hours['19-20'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1;
        $hours['70'] = $tm[0]*21 + $tm[1]*21 + $tm[2]*20 + $tm[4]*22;
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 : 0;
        return $hours;
        }
        // 5/thesia
        elseif ($artm == 5){
        // ean opws fek dld synd/lia 3-4
        $hours = [];
        if ($tm[2] + $tm[3] == 1){
            $hours['05-07'] = $tm[4]*2 + $tm[5]*2;
            $hours['06'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*3 + $tm[4]*3 + $tm[5]*3;
            $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[4]*2 + $tm[5]*2;
            $hours['16'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['32'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
            $hours['19-20'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['70'] = $tm[0]*21 + $tm[1]*21 + $tm[2]*20 + $tm[4]*20 + $tm[5]*20;
        // alliws ean synd/lia 5-6
        } else {
            $hours['05-07'] = $tm[4]*1;
            $hours['06'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*3 + $tm[3]*3 + $tm[4]*3;
            $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[3]*3 + $tm[4]*2;
            $hours['16'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['32'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1;
            $hours['19-20'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['70'] = $tm[0]*21 + $tm[1]*21 + $tm[2]*20 + $tm[3]*20 + $tm[4]*22;
        }
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 : 0;
        
        return $hours;
        }
        // 6/thesia+anw
        else {
        $hours = [];
        $hours['05-07'] = $tm[4]*2 + $tm[5]*2;
        $hours['06'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*3 + $tm[3]*3 + $tm[4]*3 + $tm[5]*3;
        $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[3]*3 + $tm[4]*2 + $tm[5]*2;
        $hours['16'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['32'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1;
        $hours['19-20'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['70'] = $tm[0]*21 + $tm[1]*21 + $tm[2]*20 + $tm[3]*20 + $tm[4]*20 + $tm[5]*20;
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 : 0;
        // PZ
        $hours['P'] = $tm[8]*5;
        return $hours;
        }
    }
    /*
    * Headmaster's hours depending on number of school classes
    * 4,5: 20, 6-9: 12, 10,11: 10, 12+: 8
    */
    function wres_dnth($tm) {
        if ($tm == 4 || $tm == 5)
            return 20;
        elseif ($tm > 5 && $tm < 10)
            return 12;
        elseif ($tm == 10 || $tm == 11)
            return 10;
        elseif ($tm >= 12)
            return 8;
    }
    function tdc($val){
    return $val >= 0 ? "<td style='background:none;background-color:#00FF00'>$val</td>" : "<td style='background:none;background-color:#FF0000'>$val</td>";
    }
    /*
    * ektimhseis1617
    * Function to compute required and available hours for oloimero schedule 2016-17
    * uses anagkes1617
    * sch: school code, $print: TRUE to print, false to return 3 arrays(required, available, diff)
    */
    function ektimhseis1617($sch, $mysqlconnection, $sxoletos, $print = FALSE)
    {
        set_time_limit(1200);
        $avhrs = [];
        $all = $allcnt = [];
        // init db
        mysql_query("SET NAMES 'greek'", $mysqlconnection);
        mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
        // get tmimata
        $query = "SELECT tmimata,leitoyrg from school WHERE id='$sch'";
        $result = mysql_query($query, $mysqlconnection);
        $tmimata_exp = explode(",",mysql_result($result, 0, "tmimata"));
        $leit = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
        // oligothesia
        if ($leit < 4) 
        {
        $reqhrs['70'] = $leit * 25;
        }
        else {
        // Απαιτούμενες ώρες
        $reqhrs = anagkes1617($tmimata_exp);
        }
        // ώρες Δ/ντή
        $query = "SELECT * from employee e JOIN klados k ON e.klados = k.id WHERE sx_yphrethshs='$sch' AND status=1 AND thesi = 2";
        $result = mysql_query($query, $mysqlconnection);
        if (mysql_num_rows($result)) {
            $dnthrs = wres_dnth($leit);
            $klados = mysql_result($result, 0, "klados");
            $klper = mysql_result($result, 0, "k.perigrafh");
            $avhrs[$klados] = $dnthrs;
            // ώρες Δ/ντή στην ανάλυση
            $ar = Array('surname' =>  mysql_result($result, 0, "e.surname"), 'klados' =>  mysql_result($result, 0, "k.perigrafh"), 'hours' => $dnthrs);
            $all[] = $ar;
            $allcnt[$klper]++;
        }
        // ώρες υπηρετούντων (εκπ/κοί - υπ/ντές)
        //$query = "SELECT klados, sum(wres) as wres from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,1) GROUP BY klados";
        $query = "SELECT e.klados, sum(y.hours) as wres FROM employee e join yphrethsh y on e.id = y.emp_id WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi in (0,1) GROUP BY klados";
        $result = mysql_query($query, $mysqlconnection);
        while ($row = mysql_fetch_array($result)){
            $kl = strval($row['klados']);
            $avhrs[$kl] += $row['wres'];
        }
        if ($print){
        // αναλυτικά...
        $query = "SELECT e.surname,k.perigrafh, y.hours FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi in (0,1) ORDER BY e.klados";
        $result = mysql_query($query, $mysqlconnection);
        while ($row = mysql_fetch_array($result)){
            $ar = Array('surname' => $row['surname'], 'klados' => $row['perigrafh'], 'hours' => $row['hours']);
            $all[] = $ar;
            $allcnt[$row['perigrafh']]++;
        }
        }
        // αναπληρωτές (εκτός ΖΕΠ / ΕΚΟ (type=6))
        $query = "SELECT klados,sum(y.hours) as wres FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where y.yphrethsh=$sch AND y.sxol_etos = $sxoletos AND e.status = 1 AND e.type != 6 GROUP BY klados";
        $result = mysql_query($query, $mysqlconnection);
        while ($row = mysql_fetch_array($result)){
            $kl = strval($row['klados']);
            $avhrs[$kl] += $row['wres'];
        }
        if ($print){
        // αναλυτικά...(εκτός ΖΕΠ / ΕΚΟ (type=6))
        $query = "SELECT e.surname, k.perigrafh, y.hours FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id JOIN klados k ON e.klados=k.id where y.yphrethsh=$sch AND y.sxol_etos = $sxoletos AND e.status = 1 AND e.type != 6 ORDER BY e.klados";
        $result = mysql_query($query, $mysqlconnection);
        while ($row = mysql_fetch_array($result)){
            $srn = $row['surname'] . ' *';
            $ar = Array('surname' => $srn, 'klados' => $row['perigrafh'], 'hours' => $row['hours']);
            $all[] = $ar;
            $allcnt[$row['perigrafh']]++;
        }
        }
        
        // replace kladoi @ array
        //kladoi: 2/70, 3/06, 4/08, 5/11, 6/16, 15/19-20, 13/05-07, 14/05-07, 20/32
        $avar = $avhrs;
        $avar['70'] = $avar['2'];
        unset($avar['2']);
        $avar['06'] = $avar['3'];
        unset($avar['3']);
        $avar['08'] = $avar['4'];
        unset($avar['4']);
        $avar['11'] = $avar['5'];
        unset($avar['5']);
        $avar['16'] = $avar['6'];
        unset($avar['6']);
        $avar['19-20'] = $avar['15'];
        unset($avar['15']);
        $avar['05-07'] = $avar['13'] + $avar['14'];
        unset($avar['13']);
        unset($avar['14']);
        $avar['32'] = $avar['20'] + $avar['28'];
        unset($avar['20']);

        // subtract available from required
        foreach ($avar as $key => $value) {
            if(array_key_exists($key, $reqhrs) && array_key_exists($key, $avar))
                $ret[$key] = $avar[$key] - $reqhrs[$key];
        }
        // subtract oloimero & pz from PE70
        $ret['OP'] = $ret['70'] - $reqhrs['O'] - $reqhrs['P'];
        
        if ($print){
        echo "<h3>Λειτουργικά Κενά / Πλεονάσματα <em>(σε ώρες)</em></h3>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<thead><th>Κλάδος</th><th>05-07</th><th>06</th><th>08</th><th>11</th><th>16</th><th>32</th><th>19-20</th><th>70</th><th>Ολοημ.</th><th>Π.Ζ.</th></thead>";
        echo "<tr><td>Απαιτούμενες</td><td>".$reqhrs['05-07']."</td><td>".$reqhrs['06']."</td><td>".$reqhrs['08']."</td><td>".$reqhrs['11']."</td><td>".$reqhrs['16']."</td><td>".$reqhrs['32']."</td><td>".$reqhrs['19-20']."</td><td>".$reqhrs['70']." ($leit)</td><td>".$reqhrs['O']."</td><td>".$reqhrs['P']."</td></tr>";
        echo "<tr><td>Διαθέσιμες</td><td>".$avar['05-07']."</td><td>".$avar['06']."</td><td>".$avar['08']."</td><td>".$avar['11']."</td><td>".$avar['16']."</td><td>".$avar['32']."</td><td>".$avar['19-20']."</td><td>".$avar['70']." (".$allcnt['ΠΕ70'].")</td><td></td><td></td></tr>";
        echo "<tr><td>Διαφορά (+/-)</td>".tdc($ret['05-07']).tdc($ret['06']).tdc($ret['08']).tdc($ret['11']).tdc($ret['16']).tdc($ret['32']).tdc($ret['19-20']).tdc($ret['70']).tdc($ret['OP'])."<td></td></tr>";
        echo "</table>";
        echo "<a id='toggleBtn' href='#' onClick=>Αναλυτικά</a>";
        echo "<div id='analysis' style='display: none;'>";
            echo "<table class=\"imagetable\" border='1'>";
            echo "<tr><td colspan=3><u>ΣΥΝΟΛΑ:</u> ";
            foreach ($allcnt as $key=>$value){
                echo "&nbsp;&nbsp;$key: <strong>$value</strong>";
            }
            echo "</td></tr>";
            foreach ($all as $row) {
                echo "<tr><td>".$row['surname']."</td><td>".$row['klados']."</td><td>".$row['hours']."</td></tr>";
            }
            echo "</table>";
            echo "* Αναπληρωτής";
        echo "</div>";
        echo "<br><br>";
        }
        else {
            return ['required' => $reqhrs, 'available' => $avar, 'diff' => $ret, 'leit' => $leit];
        }
    }
?>
