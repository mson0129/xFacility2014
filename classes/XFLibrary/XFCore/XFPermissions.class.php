<?php
//xFacility2014
//XFPermissions
//Studio2b
//Michael Son(michaelson@nate.com)
//26JUN2014(0.1.0.) - This file is created.
class XFPermissions extends XFObject {
	var $configPath = "/xfacility/configs/XFPermissions.config.php";
	var $xFacilityPermissions;
	
	function XFPermissions() {
	}
	
	function translate($permission) {
		if(substr($permission, -1)=="+") {
			$return = "+";
			$permission = substr($permission, 0, -1);
		}
		
		if(!is_numeric($permission)) {
			//string to numeric
			$temp = 0;
			for($i=0; $i<strlen($permission); $i++) {
				$char = substr($permission, $i, 1);
				if($char=="r") {
					$temp += 4;
				} else if($char=="w") {
					$temp += 2;
				} else if($char=="x") {
					$temp += 1;
				}
			}
			if($temp==7) {
				$return = $temp;
			} else {
				$return = $temp.$return;
			}
		} else {
			//numeric to string
			$temp = NULL;
			if($permission >= 4) {
				$temp .= "r";
				$permission -= 4;
			}
			if($permission >= 2) {
				$temp .= "w";
				$permission -= 2;
			}
			if($permission >= 1) {
				$temp .= "x";
				$permission -= 1;
			}
			if(strlen($temp)==3) {
				$return = $temp;
			} else {
				$return = $temp.$return;
			}
		}
		return $return;
	}
	
	/*
	 function isPermitted($userNo, $objNo, $permission) {
	//isPermitted("234", "12", "r+");
	//234번 유저가 12번 게시물에 읽기 이상의 권한이 있는지 확인
	
	$peruse = 4; //Read
	$create = 2; //Write
	$delete = 1; //eXtecute
	
	$nowPermission = getPermission($userNo, $objNo);
	
	if(substr($permission, -1)=="+") {
	$basePermission = substr($permission, 0, -1);
	$flag = true;
	}
	}
	
	function getPermission($userNo, $tool, $table, $objNo) {
		//현재 권한을 가져오는 함수
		
		if(is_numeric($objNo) && !is_null($objNo)) {
			if(is_null($userNo)) {
				//getGuestPermission
				$return = substr($obj[0]['permission'], 3, 0);
			} else {
				$dbClass = new XFDatabase();
				$obj = $dbClass->select($tool, $table, $objNo);
			}
		} else {
			$return = false;
		}
		
		return $return;
	}
	*/
	
	function isPermitted($userNo=NULL, $requestPermission, $tool=NULL, $table=NULL, $condition=NULL, $password=NULL) {
		//init
		$peruse = false; //Read(4)
		$create = false; //Write(2)
		$delete = false; //eXtecute(1)
		$permission = $this->getPermission($userNo, $tool, $table, $condition, $password);
		
		if($permission >= 4) {
			$permission -= 4;
			$peruse = true;
		}
		if($permission >= 2) {
			$permission -= 2;
			$create = true;
		}
		if($permission >= 1) {
			$permission -= 1;
			$delete = true;
		}
		
		if(!is_numeric($requestPermission)) {
			$requestPermission = $this->translate($requestPermission);
		}
		
		if(strlen($requestPermission)==1 && $requestPermission<8 && is_numeric($requestPermission)) {
			if($requestPermission >= 4) {
				$requestPermission -= 4;
				$request['peruse'] = true;
			}
			if($requestPermission >= 2) {
				$requestPermission -= 2;
				$request['create'] = true;
			}
			if($requestPermission >= 1) {
				$requestPermission -= 1;
				$request['delete'] = true;
			}
			
			if($request['peruse']==true && is_null($peruse))
				return false;
			if($request['create']==true && is_null($peruse))
				return false;
			if($request['delete']==true && is_null($peruse))
				return false;
			$return = true;
		} else {
			$return = false;
		}
		return $return;
	}
	
	function getPermission($userNo=NULL, $tool=NULL, $table=NULL, $condition=NULL, $password=NULL) {
		//init
		$flagOwner = false;
		require ($_SERVER['DOCUMENT_ROOT'].$this->configPath);
		$this->xFacilityPermissions = $xfPermissions;
		$dbClass = new XFDatabase();
		
		//Read permissions of what
		if(!is_null($tool) && !is_null($table) && !is_null($condition)) {
			//what == rows(items)
			$what = $dbClass->select($tool, $table, $condition);
		} else if(!is_null($tool) && !is_null($table)) {
			//what == table
			$condition = array(array(tool=>$tool, table=>$table));
			$what = $dbClass->select("xfcore", "permissions", $condition);
		} else if(!is_null($tool)) {
			//what == tool
		} else {
			//what == site
			$what[0]['permission'] = $this->xFacilityPermissions['what'];
		}
		
		/*
		echo $tool."<br />\n";
		echo $table."<br />\n";
		echo $no."<br />\n";
		
		print_r($what);
		*/
		
		//Read permissions of who
		if(!is_null($userNo))
			$who = $dbClass->select("xfcore", "users", $userNo);
		
		if(count($who)==0 || is_null($userNo))
			$who = NULL; //guest
		
		//owner
		if(is_null($what[0]['owner'])) {
			$temp = $dbClass->select($tool, $table, array(array("no"=>$no, "password"=>$password)));
			if(count($temp)>0 && !is_null($password))
				//guest == owner
				$flagOwner = true;
		}
		if(($who[0]['no'] == $what[0]['owner'] && !is_null($who)) || $flagOwner == true) {
			if($flagOwner==true) {
				//Owner == Guest - who Permission = Default Permission
				$whoOwner = substr($this->xFacilityPermissions['who'], 0, 1);
			} else {
				//Owner == User - User Permission
				$whoOwner = substr($who[0]['permission'], 0, 1);
			}
			$whatOwner = substr($what[0]['permission'], 0, 1);
			$owner = max($whoOwner, $whatOwner);
		} else {
			$owner = 0;
		}
		
		/*
		echo $who[0]['permission']."<br />\n";
		echo "owner: ".$owner."<br />\n";
		*/
		
		//group(!guest)
		if(!is_null($what[0]['group']) && !is_null($who)) {
			//parsing
			if($this->isUserInGroups($who[0]['no'], $groups)) {
				
			}
			$group = 0;
		} else {
			$group = 0;
		}
		
		/*
		echo "group: ".$group."<br />\n";
		*/
		
		//every
		if(is_null($who)) {
			//Guest - Default Permission
			$whoEvery = substr($this->xFacilityPermissions['who'], 2, 1);
		} else {
			//User - User Permission
			$whoEvery = substr($who[0]['permission'], 2, 1);
		}
		$whatEvery = substr($what[0]['permission'], 2, 1);
		$every = max($whoEvery, $whatEvery);
		
		/*
		echo "every: ".$every."<br />\n";
		*/
		
		$return = max($owner, $group, $every);
		
		/*
		echo "return: ".$return."<br />\n";
		echo "getPermitted exec.<br />\n";
		*/
		return $return;
	}
	
	function isUserInGroups($userNo, $groups) {
		$dbClass = new XFDatabase();
		return true;
	}
}
?>
