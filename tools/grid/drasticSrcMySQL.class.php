<?php
/* DrasticTools version 0.6.23
   (patched by Sugarvag on 23/01/2019 to comply with mysqli)
 * DrasticTools is released under the GPL license: 
 * Copyright (C) 2007 email: info@drasticdata.nl
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * =========================================================================================
 * If you find this sofware useful, we appreciate your donation on http://www.drasticdata.nl
 * Suggestions for improvement can be sent to: info@drasticdata.nl
 * ========================================================================================= 
 */
class drasticSrcMySQL {
	// these options should be set via the options argument on the constructor!
	public $add_allowed 	= true;		// may the user add records? Default is true.
	public $delete_allowed  = true;		// may the user delete records? Default is true.
	public $editablecols;				// array of columnnames to be editable. Defaults to all columns except the id column
	public $defaultcols;				// array of columnnames and values; only records that satisfy these conditions will be selected;
										// added records will have these values as default
	public $defaultcolsContainExpression = false;	// set to true if the defaultcols is an array of columnnames and boolean expressions; Default = false;
	public $sortcol;					// name of column to sort on initially. Defaults to the id column.
	public $sort;						// sort ascending (a) or descending (d)? Default is a.
	public $SQLCharset		= "utf8";	// character set of the strings in the table		
	public $HTMLCharset		= "utf8";	// character set for xhttprequest
	
	// General variables
	public $orderbystr, $wherestr, $addstr;
	public $idname;
	public $idcolnr;
	public $result;
	public $num_rows;
	public $num_fields;
	public $cols;
	public $cols_numeric;
	private $max;

	function __construct($server, $user, $pw, $db, $table, $options = null) {
		if (!isset($_REQUEST["op"])) return;
		if ($options) {
			if (isset($options["add_allowed"])) $this->add_allowed = $options["add_allowed"];
			if (isset($options["delete_allowed"])) $this->delete_allowed = $options["delete_allowed"];
			if (isset($options["editablecols"])) $this->editablecols = $options["editablecols"];
			if (isset($options["defaultcols"])) $this->defaultcols= $options["defaultcols"];
			if (isset($options["defaultcolsContainExpression"])) $this->defaultcolsContainExpression= $options["defaultcolsContainExpression"];
			if (isset($options["sortcol"])) $this->sortcol= $options["sortcol"];
			if (isset($options["sort"])) $this->sort= $options["sort"];
			if (isset($options["SQLCharset"])) $this->SQLCharset= $options["SQLCharset"];
			if (isset($options["HTMLCharset"])) $this->HTMLCharset= $options["HTMLCharset"];						
    }
		/* Optionally retrieve parameters from the addparams parameter.
		 * Uncomment the line below and change "myparameter" to the name of your parameter
		 * If you pass multiple parameters copy the line multiple times
		 * 
		 * $myparameter = mysqli_real_escape_string($_REQUEST["myparameter"]);
		 */		
		$this->conn = mysqli_connect($server, $user, $pw, $db) or die(mysqli_error());
		$this->table = $table;
		$res = mysqli_query($this->conn, "SET NAMES '" . $this->SQLCharset . "'");
		// Initialize the name of the id field, column names and numeric columns:
    $idresult = $this->metadata();
    $primary_found = false;
    // find primary key
    $r = mysqli_fetch_assoc(mysqli_query($this->conn, "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'")); 
    $this->idname = $r['Column_name']; 
    while ($fld = mysqli_fetch_field($idresult)){
		//for($i=0; $i < mysqli_num_fields($idresult); $i++)  {
      //$fld = mysqli_fetch_field($idresult, $i);
			if ($primary_found == false) {
				//if ($fld->primary_key == 1) { 
          // NOTICE: assume first field is primary_key because primary_key does not exist in mysqli_fetch_field
					$this->idname = $fld->name;
					$this->idcolnr = 0;
					$primary_found = true;
				//}
				// elseif ($fld->unique_key == 1) {
				// 	$this->idname = $fld->name;
				// 	$this->idcolnr = $i;
				// }
			}
			$this->cols[] = $fld->name;
			if ($fld->numeric == 1) $this->cols_numeric[] = $fld->name;
    }
		if (!isset($this->idname)) die("Could not find primary or unique key");
		// Initialize editablecols if not done yet:
		if (!isset($this->editablecols)) {
			mysqli_field_seek($idresult, 0);
			for($i=0; $i < mysqli_num_fields($idresult); $i++)  {
				$fldname = mysqli_fetch_field($idresult)->name;
				if ($fldname != $this->idname) $this->editablecols[] = $fldname;
			}
		}
		mysqli_free_result($idresult);
		
		// Initialize Field types:
		$this->fldtypes = array();
		$colresult = mysqli_query($this->conn, "SHOW COLUMNS FROM " . $this->table) or die(mysqli_error());
		for($i=0; $i < mysqli_num_rows($colresult); $i++)  {
			list($fldname, $fldtype, $fldnull, $fldkey, $flddefault, $fldextra) = mysqli_fetch_row($colresult);	
			$this->fldtypes[$fldname] = $fldtype;
		}
		mysqli_free_result($colresult);
		
		// Calculate the WHERE string and the string for the ADD operation, if the defaultcols option is set.
		$this->wherestr = "";
		$this->addstr = " () VALUES () ";
		if ($this->defaultcols){
			if ($this->defaultcolsContainExpression) {
				foreach ($this->defaultcols as $key) $assignment[] = $key;
			} else {
				foreach ($this->defaultcols as $key => $value) $assignment[] = $key . " = '" . $value . "'";
			}
			$wherestr1 = implode(" AND ", $assignment);
			$this->wherestr = sprintf(" WHERE %s ", $wherestr1);
			
			$addstr1 = implode(", ", array_keys($this->defaultcols));
			$addstr2 = implode(", ", array_map(array ($this, "addquotes"), array_values($this->defaultcols)));
			$this->addstr = sprintf(" (%s) VALUES (%s) ", $addstr1, $addstr2);
		}
		
		// Do the sorting:
		if (isset($_REQUEST["sortcol"])) $this->sortcol = mysqli_real_escape_string($this->conn, $_REQUEST["sortcol"]);
		if (isset($_REQUEST["sort"])) $this->sort = mysqli_real_escape_string($this->conn, $_REQUEST["sort"]);
		if (!$this->sortcol) $this->sortcol = $this->idname;
		if (!$this->sort) $this->sort = "a";
		$this->orderbystr = " ORDER BY " . $this->sortcol . ($this->sort == "d"?" DESC":"");		  

		header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );  // disable IE caching
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" ); 
		header( "Cache-Control: no-cache, must-revalidate" ); 
		header( "Pragma: no-cache" );
		if (isset($_REQUEST["op"])) 	$op		= mysqli_real_escape_string($this->conn, $_REQUEST["op"]); else $op = "";		
		if (isset($_REQUEST["id"])) 	$id		= mysqli_real_escape_string($this->conn, $_REQUEST["id"]);
		if (isset($_REQUEST["col"])) 	$col	= mysqli_real_escape_string($this->conn, $_REQUEST["col"]);
		if (isset($_REQUEST["value"])) 	$value	= mysqli_real_escape_string($this->conn, $_REQUEST["value"]);
		if ($op != "vb" && $op != "vc") header("Content-Type: text/html; charset=".$this->HTMLCharset);
		
		switch ($op) {
			case ("a") : if ($this->add_allowed) exit($this->add());
			case ("d") : if ($this->delete_allowed) exit($this->delete($id));
			case ("u") : exit($this->update($id, $col, rawurldecode($value)));
		}
		
		// Get the table in memory
		$this->result = $this->select();
		$this->num_rows = mysqli_num_rows($this->result);
		$this->num_fields = mysqli_num_fields($this->result);

		if ($op == "v") exit($this->view());
		if ($op == "vm") exit($this->view_meta());
		if ($op == "vb") exit($this->view_bar());
		if ($op == "vc") exit($this->view_circle());
		if ($op == "vl") exit($this->view_label());
		if ($op == "vrn") exit($this->view_rownr());
	}
	function __destruct() {
		if ($this->result) mysqli_free_result($this->result);
		if ($this->conn) mysqli_close($this->conn);
	}
	
	private function view_meta(){
		$result[0] = $this->num_rows;
		$result[1] = $this->num_fields;
		$result[2] = $this->idname;
		$result[3] = $this->idcolnr;
		$result[4] = $this->cols;	
		$result[5] = $this->cols_numeric;
		$result[6] = $this->add_allowed;
		$result[7] = $this->delete_allowed;
		$result[8] = $this->editablecols;
		$result[9] = $this->defaultcols;
		$result[10] = $this->sortcol;
		$result[11] = $this->sort;
		$result[12] = $this->fldtypes;
		return(json_encode($result));
	}	

	//
	// These protected functions can be overruled if you want to redefine your datasource
	//
	protected function select(){
		$res = mysqli_query($this->conn,  "SELECT * FROM $this->table" . $this->wherestr . $this->orderbystr) or die(mysqli_error());
		return ($res);
	}	
	protected function add(){
		mysqli_query($this->conn, "INSERT INTO $this->table" . $this->addstr) or die(mysqli_error());
		if (mysqli_affected_rows($this->conn) == 1) return(true); else return(false);
	}
	protected function sums($sumString){
		// SumString contains something like "SUM(var1), SUM(var2), SUM(var3)" and is derived from the sum settings in the grid.
		// We advise to use the sumString, but the rest of the query can be changed, same as for select.
		$res = mysqli_query($this->conn, "SELECT ". $sumString . " FROM $this->table" . $this->wherestr) or die(mysqli_error());
		return ($res);
	}
	// Override this function if you want to use (join) query on multiple tables:
	protected function metadata(){
		$res = mysqli_query($this->conn, "SELECT * FROM $this->table LIMIT 1");
		return ($res);
	}
	
	//
	// Private functions only visible within the class
	//
	private function exists($id, $fld = "") {
		$res = $this -> select();
		// check field
		if ($fld != "") {
			$found = false;
			while (($field = mysqli_fetch_field($res)) != null) {
				if ($field->name == $fld) {
					$found = true;
					break;
				}
			}
			if (!$found) return(false);
		} 
		// check id
		for ($i=0; $i < mysqli_num_rows($res); $i++)  {
			$row = mysqli_fetch_array($res);
			if ($row[$this->idcolnr] == $id) return(true);
		}
		return(false);
	}	
	private function delete($id){
		if (!$this->exists($id)) return(false);
		mysqli_query($this->conn, "DELETE FROM $this->table WHERE $this->idname='$id'") or die(mysqli_error());
		if (mysqli_affected_rows($this->conn) == 1) return(json_encode(true)); else return(json_encode(false));
	}
	private function update($id, $fld, $value){
		if ((in_array($fld, $this->editablecols)) && $this->exists($id, $fld)) {
			mysqli_query($this->conn, "UPDATE $this->table SET $fld='$value' WHERE $this->idname='$id'") or die(mysqli_error());
			if (mysqli_affected_rows($this->conn) == 1) { 
				return("1");
			} else {
				$res = mysqli_query($this->conn, "SELECT $fld FROM $this->table WHERE $this->idname='$id'") or die(mysqli_error());
				$row = mysqli_fetch_array($res);
				if ($row[0] == $value) 
					return("1");
				else
					return("0");
			}
		}
		return("0");
	}
	private function view_rownr(){
		if (isset($_REQUEST["id"])){
			mysqli_data_seek($this->result, 0);
			for ($i = 0; $i < $this->num_rows; $i++) {
				$value = mysqli_fetch_array($this->result);
				if ($value[$this->idcolnr] == $_REQUEST["id"]) {
					return(json_encode($i));
				}
			}
		}
		return(json_encode(-1));
	}	
	private function view(){
		if ($this->num_rows == 0) return(json_encode(array(null, null)));
		if (isset($_REQUEST["cols"])) $cols = explode(",", mysqli_real_escape_string($this->conn, $_REQUEST["cols"]), $this->num_fields);
		if (isset($_REQUEST["id"])){
			$res = mysqli_query($this->conn, "SELECT * FROM $this->table WHERE ".$this->idname." = '".mysqli_real_escape_string($_REQUEST["id"])."'");
			$value = mysqli_fetch_array($res);
			for ($j = 0; $j < ((isset($cols))?(count($cols)):($this->num_fields)); $j++) {
				$row[$j] = $value[((isset($cols))?($cols[$j]):($j))];
			}
			$arr[0] = $row;
			$sqlidarr[0] = $value[$this->idname];
			mysqli_free_result($res);
		}
		else {
			if (isset($_REQUEST["start"])) $start = mysqli_real_escape_string($this->conn, $_REQUEST["start"]); else $start = 0;
			if (isset($_REQUEST["end"])) $end = mysqli_real_escape_string($this->conn, $_REQUEST["end"]); else $end= $this->num_rows;
			if ($start < 0) $start=0;
			if ($end > $this->num_rows) $end = $this->num_rows;
			if ($start < $this->num_rows) {		
				mysqli_data_seek($this->result, $start);
				for ($i = 0; $i < ($end-$start); $i++) {
					$value = mysqli_fetch_array($this->result);
					for ($j = 0; $j < ((isset($cols))?(count($cols)):($this->num_fields)); $j++) {
						$row[$j] = $value[((isset($cols))?($cols[$j]):($j))];
						//echo $row[$j];
					}
					$arr[$i] = $row;
					$sqlidarr[$i] = $value[$this->idname];
				}
			}
		}
		$result[0] = $sqlidarr;
		$result[1] = $arr;
		// optionally add sums
		if (isset($_REQUEST["sns"]) && strlen($_REQUEST["sns"]) > 0) {
			$sumnames = explode(",", mysqli_real_escape_string($this->conn, $_REQUEST["sns"]));
			$sumString = ''; foreach ($sumnames as $sumname) $sumString .= ($sumString==''?'':', ') . "SUM(" . $sumname . ")";
			$res = $this -> sums($sumString);
			$value = mysqli_fetch_array($res, MYSQLI_NUM);
			$result[2] = $value;
			mysqli_free_result($res);
		}
		return(json_encode($result));
	}
	private function view_bar(){
		$id = mysqli_real_escape_string($this->conn, $_REQUEST["id"]);
		$colname = mysqli_real_escape_string($this->conn, $_REQUEST["colname"]);
		$w = mysqli_real_escape_string($this->conn, $_REQUEST["w"]);
		$h = mysqli_real_escape_string($this->conn, $_REQUEST["h"]);
		if (!isset($this->max)) $this->max = $this->MaxNumber($colname);	
		$res = mysqli_query($this->conn, "SELECT $colname FROM $this->table WHERE $this->idname='$id'") or die(mysqli_error());
		$data = mysqli_fetch_array($res);
		$value = $data[0];
		mysqli_free_result($res);
		
		$im = imagecreatetruecolor ($w, $h) or die("Cannot Initialize new GD image stream");
		imagealphablending($im, FALSE);
		imagesavealpha($im, TRUE);
		
		$bg = imagecolorallocatealpha($im, 255, 255, 255, 127);
		imagefill($im, 0, 0, $bg);
		
		$clr = imagecolorallocatealpha($im, 0, 0, 255, 60);
		$height = (int) ceil($h * ($value / $this->max));
		imagefilledrectangle ($im, 0, $h-$height, $w, $h, $clr);
		
		Header("Content-type: image/png");
		Imagepng($im);		
		ImageDestroy($im);
	}
	private function view_circle(){
		$id = mysqli_real_escape_string($this->conn, $_REQUEST["id"]);
		$colname = mysqli_real_escape_string($this->conn, $_REQUEST["colname"]);
		$w = mysqli_real_escape_string($this->conn, $_REQUEST["w"]);
		if (!isset($this->max)) $this->max = $this->MaxNumber($colname);	
		$res = mysqli_query($this->conn, "SELECT $colname FROM $this->table WHERE $this->idname='$id'") or die(mysqli_error());
		$data = mysqli_fetch_array($res);
		$value = $data[0];
		mysqli_free_result($res);
		
		$maxr = $w / 2;
		$maxopp = pi() * pow($maxr, 2);
		$opp = $maxopp * ($value / $this->max);
		$r = sqrt($opp / pi()); 
		$width = $r*2;
		
		$im = imagecreatetruecolor ($w, $w) or die("Cannot Initialize new GD image stream");
		imagealphablending($im, FALSE);
		imagesavealpha($im, TRUE);
		
		$bg = imagecolorallocatealpha($im, 255, 255, 255, 127);
		imagefill($im, 0, 0, $bg);
		
		$clr = imagecolorallocatealpha($im, 0, 0, 255, 60);
		imagefilledarc ($im, $maxr, $maxr, $width, $width, 0, 360, $clr, IMG_ARC_PIE);
		
		Header("Content-type: image/png");
		Imagepng($im);		
		ImageDestroy($im);
	}	
	private function MaxNumber($colname) {
		$max = 0;
		mysqli_data_seek($this->result, 0);
		for ($i=0; $i < $this->num_rows; $i++) {
			$row = mysqli_fetch_array($this->result);
			$max = max($max, $row[$colname]);
		}
		return($max);
	}
	function addquotes($str) {
    	return ("'".$str."'");
	} 
}
?>