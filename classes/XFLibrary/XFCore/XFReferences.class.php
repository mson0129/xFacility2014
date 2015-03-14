<?php
//xFacility2014
//XFReferences
//Studio2b
//Michael Son(michaelson@nate.com)
//29JUN2014(1.0.0.) - This file is newly writed.

class XFReferences extends XFObject {
	var $tool = "xfcore";
	var $table = "references";
	var $no, $status, $fromTool, $fromTable, $fromNo, $toTool, $toTable, $toNo, $etc;
	
	function XFReferences($no = NULL) {
		if(!is_null($no) && is_numeric($no)) {
			$this->peruse($no);
		}
	}
	
	function create($data) {
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
		
		$databaseClass = new XFDatabase();
		$result = $databaseClass->select($this->tool, $this->table, $no.";");
		if($result!=false) {
			$return = $result[0];
			$this->no = $result[0]['no'];
			$this->status = $result[0]['status'];
			$this->fromTool = $result[0]['fromTool'];
			$this->fromTable = $result[0]['fromTable'];
			$this->fromNo = $result[0]['fromNo'];
			$this->toTool = $result[0]['toTool'];
			$this->toTable = $result[0]['toTable'];
			$this->toNo = $result[0]['toNo'];
			$this->etc = $result[0]['etc'];
		} else {
			$return = false;
		}
		return $return;
	}
}
?>