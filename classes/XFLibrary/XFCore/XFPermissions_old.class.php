<?php
//xFacility2014
//XFPermissions
//Studio2b
//Michael Son(michaelson@nate.com)
//26JUN2014(0.1.0.) - This file is created.
//29JUN2014(0.6.0.) - create(), modify(), delete(), browse() and peruse() is added.

class XFPermissions_old extends XFObject {
	var $tool = "xfcore";
	var $table = "permissions";
	var $no, $status, $etc;
	
	function XFPermissions_old($xfUserRow = NULL) {
		if(is_null($xfUserRow)) {
			if(is_null($_SESSION['xfuser'][0])) {
				//Guest
			} else {
				//Online User
			}
		} else {
			//Specific User
		}
	}
	
	//Basic IO
	function craete($data) {
		$databaseClass = new XFDatabase();
		return $databaseClass->insert($this->tool, $this->table, $data);
	}
	
	function modify($data, $condition) {
		$databaseClass = new XFDatabase();
		return $databaseClass->update($this->tool, $this->table, $data, $condition);
	}
	
	function delete($condition) {
		$databaseClass = new XFDatabase();
		return $databaseClass->delete($this->tool, $this->table, $condition);
	}
	
	function browse($condition) {
		$databaseClass = new XFDatabase();
		return $databaseClass->select($this->tool, $this->table, $condition);
	}
	
	function peruse($no = NULL) {
		if(is_null($no)) {
			if(is_null($this->no)) {
				return false;
			} else {
				$no = $this->no;
			}
		}
		if(is_numeric($no)) {
			$databaseClass = new XFDatabase();
			$result = $databaseClass->select($this->tool, $this->table, $no.";");
			if($result!=false) {
				$return = $result[0];
				$this->no = $result[0]['no'];
				$this->status = $result[0]['status'];
				$this->etc = $result[0]['etc'];
			} else {
				$return = false;
			}
		}
		return $return;
	}
	
	//Non-standard
	function is_permitted($table, $no, $column) {
		return true;
	}
	
	function chmod() {
		
	}
	
	function chown() {
		
	}
	
	function chgrp() {
		
	}
}
?>