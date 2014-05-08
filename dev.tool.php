<?php
/***********************************************
**  My Swiss-army knife of Development Tools  **
************************************************
**     Author:	James Mouat                   **
**    Version:	2.1                           **
**    Revised:	May 2014                      **
***********************************************/
/* It's not perfect, but then again, there are better things for me to work on than this... */
/* Just include this PHP file on your dev-server like so...
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/dev.insan3.php")) include_once($_SERVER["DOCUMENT_ROOT"]."/dev.insan3.php");
*/

/* Kinda like the retarded cousin of 'var_dump()', just call this with your array-data to 'dump' it out */
function array_dump($array, $pid = null){
	$type = gettype($array);
	if ( $type!=='array' ){ // (array() !== (array)$array) && !is_object($array) ){
		echo "<div style='display: inline-block; background-color: #F8F8FF;'>";
		if ( $type==='object'){ echo "(XML) = [".$array->saveHTML()."]</div>"; } else { echo "($type) = &quot;$array&quot;</div>"; }
	} else {
		echo "<div style='margin:20px; margin-top: 0px; margin-bottom: 5px; border: 1px dashed #000080; font: 7pt Courier, MonoSpace; text-align: left; background-color: #F8F8FF;'>";
		if (is_object($array)){
			$vars = get_object_vars($array);
			foreach(array_keys($vars) as $k){
				echo "[<b>$k</b>] (".count($vars).")";
				array_dump($vars[$k]);
			}
		} else if (is_array($array)) {
			foreach($array as $entry => $value){
				$oid = uniqid('I');
				if (is_array($value)){
					echo "<div id='$oid'>[<b>$entry</b>] (".count($value).") ";
					echo "<span style='margin-top:-1; font-size: 9px; float:right; cursor: pointer; font-family: sans-serif; '>";
					echo "<span style='padding: 0px 5px; display: inline-block; background-color: #66A; color: FFF;' onclick='document.getElementById(\"$oid\").nextSibling.style.display=\"\"'>Show</span>&nbsp;";
					echo "<span style='padding: 0px 5px; display: inline-block; background-color: #66A; color: FFF;' onclick='document.getElementById(\"$oid\").nextSibling.style.display=\"none\"'>Hide</span>";
					echo "</span>";
					echo "</div>";
					array_dump($value,$oid);
				} else if (is_object($value)) {
					echo "<span style='display: block; background-color: #DDE;'>[$entry]&nbsp;=&nbsp;\"".var_dump($value)."\" (".gettype($value).")</span>";
				} else {
					echo "<span style='display: block; background-color: #DDE;'>[$entry]&nbsp;=&nbsp;\"".strip_tags($value)."\" (".gettype($value).")</span>";
				}
 				}
		} else {
			var_dump($array);
		}
		echo "</div>";
	}
}

// Generic //
error_reporting(E_ALL);
ini_set('display_errors', true);
//EOF//