<?php
//xFacility2014
//XFAccounts
//Studio2b
//Michael Son(michaelson@nate.com)
//29JUN2014(1.0.0.) - This file is newly created.

class XFAccounts extends XFObject {
	var $tool = "xfcore";
	var $table = "accounts";
	var $no, $status, $id, $password, $passwordDate, $etc;
	
	function XFAccounts($no=NULL) {
		if(!is_null($no)) {
			$this->peruse($no);
		}
		//Sync with SESSION data
		//Sync with COOKIE data
	}
	
	//Basic IO
	function create($data) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
		
		$databaseClass = new XFDatabase();
		
		foreach($data as $row=>$columns) {
			if(is_array($this->checkAccount($columns[id])))
				unset($data[$row]);
		}
		
		if(count($data)>0)
			$return = $databaseClass->insert($this->tool, $this->table, $data);
		else
			$return = false;
		return $return;
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
	
	function peruse($no) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
		if(is_null($no)) {
			if(is_null($this->no)) {
				return false;
			} else {
				$no = $this->no;
			}
		}
		
		$databaseClass = new XFDatabase();
		$result = $databaseClass->select($this->tool, $this->table, $no.";");
		if($result!==false) {
			$return = $result[0];
			$this->no = $result[0]['no'];
			$this->status = $result[0]['status'];
			$this->id = $result[0]['id'];
			$this->password = $result[0]['password'];
			$this->passwordDate = $result[0]['passwordDate'];
			$this->etc = $result[0]['etc'];
		} else {
			$return = false;
		}
		return $return;
	}
	
	//Non-basic IO
	function checkAccount($id) {
		$condition = array(array("id"=>$id));
		$return = $this->browse($condition);
		return $return;
	}
	
	function signup($id, $password, $userNo, $type) {
		//id
		if(count($this->checkAccount($id)==0)) {
			//userNo
			
			
			//type
			$type = strtolower($type);
			if($type!="facebook" && $type!="kakao")
				unset($type);
			
			//create
			
		} else {
			$return = false;
		}
		return $return;
	}
}
?>