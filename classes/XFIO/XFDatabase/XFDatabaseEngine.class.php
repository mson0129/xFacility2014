<?php
//xFacility2014
//XFDatabaseEngine(2.0.0.)
//Studio2b
//Michael Son(michaelson@nate.com)
//24DEC2012(1.0.0.) - This file(XFDB.class.php) is newly added for xFacility2012.
//22JUN2014(2.0.0.) - This file is modified for xFacility2014.

class XFDatabaseEngine extends XFObject {
	//Values
	var $link;
	var $query;
	//Result
	var $result;
	var $counter;
	
	//Settings
	var $kind; //MySQL, MSSQL, ETC...
	var $server;
	var $database;
	var $username;
	var $pw;
	var $prefix;
	
	function XFDatabaseEngine() {
		require ($_SERVER['DOCUMENT_ROOT'].'/xfacility/configs/XFDatabaseEngine.config.php');
		$this->kind = $xFDatabaseEngine['kind'];
		$this->server = $xFDatabaseEngine['server'];
		$this->database = $xFDatabaseEngine['database'];
		$this->username = $xFDatabaseEngine['username'];
		$this->password = $xFDatabaseEngine['password'];
		$this->prefix = $xFDatabaseEngine['prefix'];
		//echo "XFDatabaseEngine Ready<br />\n";
	}
}
?>