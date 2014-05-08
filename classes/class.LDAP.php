<?php
/**************************************************
** Lightweight Directory Access Protocol Library **
***************************************************
**     Author:	James Mouat                      **
**    Version:	2.3                              **
**    Written:	August 2006                      **
**    Revised:	June 2013                        **
**************************************************/
class LDAP {
	private $link;
	private $basedn;

	// Constructor of this class
	public function __construct($hostname, $basedname = null, $username = null, $password = null){
		// UPDATE v2.0: Supports passing a keyed array of connection parameters eg (hostname, username, password)
		$coninfo = array('UID' => null, 'PWD' => null);
		if (is_array($hostname) && is_null($basedname)){
			$this->basedn = $hostname['basedname'];
			$coninfo['hostname'] = $hostname['hostname'];
			if (array_key_exists('username', $hostname) && array_key_exists('password', $hostname)){
				$coninfo['UID'] = $hostname['username'];
				$coninfo['PWD'] = $hostname['password'];
			}
		} else {
			$this->basedn = $basedname;
			$coninfo['hostname'] = $hostname;
			if ( !is_null($username) && !is_null($password) ){
				$coninfo['UID'] = $username;
				$coninfo['PWD'] = $password;
			}
		}
		$this->link = ldap_connect($coninfo['hostname']);
		if (!$this->link){ die($this->__errortxt("Unable to connect to: {$coninfo['hostname']}")); }
		ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->link, LDAP_OPT_REFERRALS, 0);
		$this->bind($coninfo['UID'], $coninfo['PWD']);
	}

	// Make connection to Database
	private function bind($bind_usr = null, $bind_pwd = null){
		if ( is_null($bind_usr) && is_null($bind_pwd) ){
			$result = ldap_bind($this->link, $bind_usr, $bind_pwd);
			if (!$result){ die($this->__errortxt("Unable to perform Anonymous Bind")); }
		} else {
			$result = ldap_bind($this->link, $bind_usr, $bind_pwd);
			if (!$result){ die($this->__errortxt("Unable to bind with account: {$bind_usr}")); }
		}
	}

	// Execute an LDAP Query
	public function query($query, $search_dn = null, $attribs=false){
		$return = array();
		if (!$attribs) { $attribs = array(); }
		$base_dn = (is_null($search_dn)) ? $this->basedn : $search_dn;
		$result = ldap_search($this->link, $base_dn, $query, $attribs);
		if(strcmp(gettype($result),"resource")===0){
			$entries = ldap_get_entries($this->link, $result);
			unset($entries['count']);
			if ($attribs){
				foreach ( $entries as $record){
					$r = array();
					if (is_array($record)){
// 						$keys = array_keys($record);
// 						array_dump($keys);
						foreach($attribs as $attr){
							if (array_key_exists($attr, $record)){
								unset($record[$attr]['count']);
								$r[$attr] = $record[$attr];
							}
						}
					$return[] = $r;
					}
				}
			} else { ## Give me EVERYTHING ##
// 				foreach ( $entries as $record){
// 					$qty = $record['count'];
// 					array_dump($record);
// 					echo "<hr>";
// 				}
				$return = $entries;
			}

		}
		return $return;
	}

	private function __flatten($obj){
		$result = false;
		if (is_array($obj) && array_key_exists('count', $obj)){

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