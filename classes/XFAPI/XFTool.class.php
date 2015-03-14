<?php
//xFacility2014
//XFTool
//Studio2b
//Michael Son(michaelson@nate.com)
//19JUL2014(1.0.0.) - This class is newly created.
//22SEP2014(1.0.1.) - Default method is modified to NULL.
//15DEC2014(1.1.0.) - XFPermissions

class XFTool extends XFObject {
	var $tool, $table;
	var $get, $post;
	var $dbPrefix;
	protected $permissions;
	
	function __construct() {
		$this->getDbPrefix();
		$this->permissions = new XFPermissions();
		//Post
		//$_POST['how'] = "people:signin;";
		if(!is_null($_POST['how'])) {
			list($temp, $trashcan) = explode(";", $_POST['how'], 2);
			list($class, $method) = explode(":", $temp, 2);
			$class = strtolower($class);
			if(strtolower(get_class($this))==$class) {
				if(strtolower($method)!="show" && strtolower($method)!=$class && method_exists($this, $method)) {
					//$permissionsClass = new XFPermissions();
					//if($permissionClass->isPermittied($_SESSION[xfusers][0][no]);
					$this->post = call_user_func(array($this, $method), $_POST['what']);
					if($_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest") {
						echo json_encode($this->post);
						exit;
					}
				}
			}
		}
		
		//Get
		if(!is_null($_GET['how']) && $_SERVER['HTTP_X_REQUESTED_WITH']!="XMLHttpRequest") {
			list($temp, $trashcan) = explode(";", $_GET['how'], 2);
			list($class, $method) = explode(":", $temp, 2);
			$class = strtolower($class);
			
			if(strtolower(get_class($this))==$class) {
				if(is_null($method)) {
					//Default
					if(is_null($_SESSION['xfusers'][0]['no'])) {
						$method = NULL;
					} else {
						$method = NULL;
					}
				} else {
					$method = strtolower($method);
				}
				if(method_exists($this, "show")) {
					$this->get = call_user_func(array($this, "show"), $method, $_GET['what']);
				}
			}
		}
	}
	
	function XFTool() {
		$this->__construct();
	}
	
	function checkPermission($action, $tool, $table, $no) {
		
	}
	
	function getDbPrefix() {
		$databaseClass = new XFDatabase();
		$this->dbPrefix = $databaseClass->prefix;
	}

	function isMe() {
		list($temp, $trashcan) = explode(";", $_GET['how'], 2);
		list($class, $method) = explode(":", $temp, 2);
		$class = strtolower($class);
		if(strtolower(get_class($this))==$class) {
			$return = true;
		} else {
			$return = false;
		}
		return $return;
	}
	
	//Database IO
	/*
	protected function query($query) {
		if(strpos(strtolower($query), "select")!==false)
			//SELECT
			$requestPermission += 4;
		if(strpos(strtolower($query), "insert")!==false || strpos(strtolower($query), "replace")!==false || strpos(strtolower($query), "update")!==false)
			//INSERT, REPLACE, UPDATE, ON DUPLICATE KEY UPDATE
			$requestPermission += 2;
		if(strpos(strtolower($query), "delete")!==false || strpos(strtolower($query), "replace")!==false || strpos(strtolower($query), "update")!==false)
			//DELETE, REPLACE, UPDATE, ON DUPLICATE KEY UPDATE
			$requestPermission += 1;
		if($requestPermission==0 || is_null($requestPermission)) {
			//DROP, TRUNCATE, ETC...
			//System Permission
			return false;
		}
		if($this->permissions->isPermitted($_SESSION[xfusers][0][no], $requestPermission, $this->tool, $this->table)) {
			$databaseClass = new XFDatabase();
			echo $query."\n";
			$return = $databaseClass->query($query);
		} else {
			$return = "NO PERMISSION";
		}
		return $return;
	}
	
	protected function insert($data) {
		if($this->permissions->isPermitted($_SESSION[xfusers][0][no], "2", $this->tool, $this->table)) {
			$databaseClass = new XFDatabase();
			$return = $databaseClass->insert($this->tool, $this->table, $data);
		} else {
			$return = false;
		}
		return $return;
	}
	
	protected function modify($data, $condition) {
		if($this->permissions->isPermitted($_SESSION[xfusers][0][no], "3", $this->tool, $this->table)) {
			$databaseClass = new XFDatabase();
			$databaseClass->modify($this->tool, $this->table, $data, $condition);
		} else {
			$return = false;
		}
		return $return;
	}
	
	protected function delete($condition) {
		if(is_numeric($condition))
			$no = $condition;
		if($this->permissions->isPermitted($_SESSION[xfusers][0][no], "1", $this->tool, $this->table, $no)) {
			$databaseClass = new XFDatabase();
			$databaseClass->delete($this->tool, $this->table, $condition);
		} else {
			$return = false;
		}
		return $return;
	}
	
	protected function select($condition) {
		if(is_numeric($condition))
			$no = $condition;
		if($this->permissions->isPermitted($_SESSION[xfusers][0][no], "4", $this->tool, $this->table, $no)) {
			$databaseClass = new XFDatabase();
			$databaseClass->select($this->tool, $this->table, $condition);
		} else {
			$return = false;
		}
		return $return;
	}
	*/
}
?>
