<?php
/*************************************
**  Lightweight  Database  Handler  **
**************************************
**     Author:	James Mouat         **
**    Version:	2.3                 **
**    Written:	June 2004           **
**    Revised:	June 2011           **
*************************************/
class MySQL {
	private $link;
	private $hostname;
	private $username;
	private $password;

	// Constructor of this class
	// UPDATE v2.0: Supports passing an array(hostname, username, password)
	public function __construct($hostname, $database = false, $username = null, $password = null){
		if (is_array($hostname)){
			$this->hostname = $hostname['hostname'];
			$this->username = $hostname['username'];
			$this->password = $hostname['password'];
		} else {
			$this->hostname = $hostname;
			$this->username = $username;
			$this->password = $password;
		}
		$this->connect();
		if($database){
			$this->select_db($database);
		} else if (array_key_exists('database',$hostname)){
			$this->select_db($hostname['database']);
		}
	}

	// Make connection to Database
	private function connect(){
		$this->link = mysql_connect($this->hostname, $this->username, $this->password);
		if (!$this->link){ die($this->__errortxt('Unable to Establish Link')); }
	}

	// Select a Database on the active connection
	public function select_db($database){
		if(!$this->link){ $this->connect(); }
		if($this->link){
			$result = mysql_select_db($database, $this->link) or die($this->__errortxt("Unable to Select Database {$database}"));
		} else { die($this->__errortxt('No Database Link')); }
	}

	// Execute an SQL Query
	public function query($query,$params=false){
		// UPDATE v2.1: Supports passing variables to be escaped as a parameter array
		// USEAGE: "SELECT * FROM `table` WHERE `table`.`field`= ?", subsequent ?'s will be replaced with their respective values
		// REMINDER: do not quote (' or ") around ?, as they will be added automatically.
		// NOTE: Update required to use backtics for table names, don't use subsitution for tablenames.
		if ($params){
			foreach ($params as &$v) { $v = mysql_real_escape_string((string)$v); }
			$esc_query = vsprintf( str_replace("?","'%s'",$query), $params );
			$result = mysql_query($esc_query) or die($this->__errortxt($query));
		} else {
			$result = mysql_query($query) or die($this->__errortxt($query));
		}
		if(strcmp(gettype($result),"resource")===0){
			$data = array();
			while( $row = mysql_fetch_assoc($result) ){
				$data[] = $row;
			}
			// Auto Flattern Result
			if( count($data)>1 ){ return $data;
			} else { return $data; }
		} else {
			return $result;
		}
	}

	// Error feedback - Added in v2.3
	private function __errortxt($txt){
		return mysql_error()."<div style='border: 1px solid #000; background-color: #FF8; font-size: 8pt;'>$txt</div>";
	}

	// Close link and Destruct - PHP GC will already do this...
	public function __destruct(){
		if($this->link) @mysql_close($this->link);
	}

	// Absent features: databaseExists(), tableExists()
}
?>