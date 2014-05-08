<?php
/*************************************
**  Light  MSSQL Database  Handler  **
**************************************
**     Author:	James Mouat         **
**    Version:	2.4                 **
**    Written:	June 2004           **
**    Revised:	March 2013          **
*************************************/
// UPDATE v2.4: Now changed to SQLSERV Library
class SQLSRV {
	private $link;
	private $hostname;

	// Constructor of this class
	// UPDATE v2.0: Supports passing a keyed array with (hostname, database, username, password)
	public function __construct($hostname, $database = null, $username = null, $password = null){
		$coninfo = array();
		if (is_array($hostname) && is_null($database)){
			$this->hostname = $hostname['hostname'];
			$coninfo['Database'] = $hostname['database'];
			if (array_key_exists('username', $hostname) && array_key_exists('password', $hostname)){
				$coninfo['UID'] = $hostname['username'];
				$coninfo['PWD'] = $hostname['password'];
			}
		} else {
			$this->hostname = $hostname;
			$coninfo['Database'] = $database;
			if ( !is_null($username) && !is_null($password) ){
				$coninfo['UID'] = $username;
				$coninfo['PWD'] = $password;
			}
		}
		$this->connect($coninfo);
	}

	// Make connection to Database
	private function connect($coninfo){
		$this->link = sqlsrv_connect($this->hostname, $coninfo);
		if (!$this->link){ $err = print_r( sqlsrv_errors(), true); die($this->__errortxt('Unable to Establish Link<br>'.$err));  }
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
// 			foreach ($params as &$v) { $v = mysql_real_escape_string((string)$v); }
// 			$esc_query = vsprintf( str_replace("?","'%s'",$query), $params );
// 			$result = sqlsrv_query($this->link, $esc_query) or die($this->__errortxt($query));
			$result = sqlsrv_query($this->link, $query, $params) or die($this->__errortxt($query));
		} else {
			$result = sqlsrv_query($this->link, $query) or die($this->__errortxt($query));
		}
		if(strcmp(gettype($result),"resource")===0){
			$data = array();
			while( $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) ){
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