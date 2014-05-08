<?php
/* ######################## *
 *   Speed-Code Generator   *
 * Written by:  James Mouat *
 *   Modified: 9/06/11      *
 ***************************/
/* This class in its basic form is a Base-Converter

To Generate a new SpeedCode:
$sc = new SpeedCode();
$code = $sc->generate();

To Parse Existing SpeedCode:
$sc = new SpeedCode($code);

/*****************************************************/ 

class SpeedCode{
	private $time; // Epoch time
	private $code; // SpeedCode

	// Characher Map - Used to Deflate and Inflate
	const map = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
//	const map = "0123456789abcdef"; // Hexadecimal

	// Constructor - Obviously
	// Supplied with SpeedCode for old, without to generate a new one
	public function __construct($code=false){
		if ($code) {
			$this->code = $code;
			$this->time = self::getEpoch();
		}
		// CODE and TIME will be updated when generate() is called.
	}
	
	// Generate a new SpeedCode, based on YearMonth + Seconds
	// Returns a string
	public function generate(){
		$this->time = time();
		$msec = $this->time - strtotime( date("Y-m-1", $this->time) );
		$this->code = self::deflate( self::getFolder($this->time).$msec );
		return $this->code;
	}
	
	// Decodes a supplied SpeedCode
	// Returns keyed array( 'year', 'month', 'seconds' )
	public function decode(){
		$int = self::inflate($this->code);
		$Y = intval("20".substr($int,0,2));
		$m = substr($int,2,2);
		return array('y'=> $Y, 'm' => $m, 's' => intval(substr($int,4)) );
	}
	
	// Returns the EPOCH time, based on current SpeedCode
	public function getEpoch(){
		$d = self::decode($this->code);
		return strtotime("{$d['y']}-{$d['m']}-1") + $d['s'];
	}

	public function getFolder($epoch = false){
		return ($epoch) ? date("ym", $epoch) : date("ym", self::getEpoch());
	}

## ################# ##
## PRIVATE FUNCTIONS ##
		
	// Deflates integer using class Character map
	private function deflate($int){
		$divisor = strlen(self::map);
		$ph = ($int < 1) ? array(0) : array();
		while($int > 0){
			$ph[] = fmod($int,$divisor);
			$int = floor($int/$divisor);
		}
		$result = '';
		while(count($ph)){ $result.= substr(self::map,array_pop($ph),1); }
		return $result;
	}

	// Inflates string using class Character map
	private function inflate($str){
		$return = 0;
		$chars = str_split($str,1);
		$divisor = strlen(self::map);
		for($p = 0; $p<strlen($str); $p++){
			$i = strpos( self::map, array_pop( $chars ) );
			$return+= $i*pow($divisor,$p);
		}
		return $return;
	}
}
/* EOF */