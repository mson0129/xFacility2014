<?php
//xFacility2014
//XFDatabase(1.2.0.)
//Studio2b
//Michael Son(michaelson@nate.com)
//22JUN2014(1.0.0.) - This is newly added. If you find the file XFDB.class.php(xFacility2012), open XFDatabaseEngine.class.php.
//27JUN2014(1.1.0.) - condition() is added.
//29JUN2014(1.2.0.) - select(), insert(), update() and delete() are added.
//15DEC2014(1.3.0.) - Engine Instance is updated.
//16FEB2015(1.3.1.) - select() is modified and $this->counter is added. (a bug of $this->counter)

class XFDatabase extends XFObject {
	var $counter;
	var $engine;
	var $prefix;
	
	function XFDatabase() {
		require ($_SERVER['DOCUMENT_ROOT'].'/xfacility/configs/XFDatabaseEngine.config.php');
		$this->engine = new XFMySQL();
		$this->prefix = $xFDatabaseEngine['prefix'];
	}
	
	function connect() {
		$this->engine->connect();
	}
	
	function disconnect() {
		$this->engine->disconnect();
	}
	
	function query($query) {
		$this->engine->connect();
		$return = $this->engine->query($query);
		$this->engine->disconnect();
		$this->counter = $this->engine->counter;
		return $return;
	}
	
	function getColumns($tool, $table) {
		$return = $this->engine->getColumns($tool, $table);
		return $return;
	}
	
	function condition($condition) {
		$return = $this->engine->condition($condition);
		return $return;
	}
	
	function insert($tool, $table, $data) {
		$return = $this->engine->insert($tool, $table, $data);
		return $return;
	}
	
	function update($tool, $table, $data, $condition) {
		$return = $this->engine->update($tool, $table, $data, $condition);
		return $return;
	}
	
	function delete($tool, $table, $condition) {
		$return = $this->engine->delete($tool, $table, $condition);
		return $return;
	}
	
	function select($tool, $table, $condition=NULL) {
		$return = $this->engine->select($tool, $table, $condition);
		$this->counter = $this->engine->counter;
		return $return;
	}
	
	//xFacility IO
	function create($tool, $table, $data) {
		$permissionsClass = new XFPermissions();
		if($permissionsClass->isPermitted($_SESSION[xfusers][0][no], "+2", $tool, $table))
		return $this->insert($tool, $table, $data);
	}
	
	function modify($tool, $table, $data, $condition) {
		return $this->update($tool, $table, $data, $condition);
	}
	
	function browse($tool, $table, $condition) {
		return $this->select($tool, $table, $condition);
	}
	
	function peruse($tool, $table, $condition, $no) {
		if(is_numeric($no)) {
			$return = $this->select($tool, $table, $no.";");
		} else {
			$return = false;
		}
		return $return;
	}
}
?>
