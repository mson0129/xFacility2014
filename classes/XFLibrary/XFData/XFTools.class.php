<?php
//xFacility2014
//XFApplications
//Studio2b
//Michael Son(michaelson@nate.com)
//29JUN2014(1.0.0.) - This file is newly created.

class XFTools extends XFObject {
	var $tool = "xfdata", $table = "applications";
	
	function XFApplications($no=NULL) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
		if(!is_null($no) && is_numeric($no))
			$this->peruse($no);
	}
	
	function create($condition) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
		$databaseClass = new XFDatabase();
		return $databaseClass->insert($this->tool, $this->table, $data);
	}
	
	function modify($data, $condition) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
		$databaseClass = new XFDatabase();
		return $databaseClass->update($this->tool, $this->table, $data, $condition);
	}
	
	function delete($condition) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
		$databaseClass = new XFDatabase();
		return $databaseClass->delete($this->tool, $this->table, $condition);
	}
	
	function browse($condition) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
		$databaseClass = new XFDatabase();
		return $databaseClass->select($this->tool, $this->table, $condition);
	}
	
	function peruse($no=NULL) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
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
				
				//...
				
			} else {
				$return = false;
			}
		}
		return $return;
	}
}
?>