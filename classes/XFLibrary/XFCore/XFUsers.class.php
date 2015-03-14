<?php
//xFacility2014
//XFUsers(0.7.0.)
//Studio2b
//Michael Son(michaelson@nate.com)
//27JUN2014(0.2.0.) - This file is newly writed. Only browse() and peruse() is supported.
//29JUN2014(0.7.0.) - create(), modify() and delete() is added. browse() and peruse() is re-writed.

class XFUsers extends XFObject {
	var $tool = "xfcore", $table = "users";
	var $no, $status, $permission, $name, $birth, $languages, $etc;
	var $error;
	var $site, $email;
	
	function XFUsers($no=NULL) {
		require ($_SERVER['DOCUMENT_ROOT'].'/xfacility/configs/XFUsers.config.php');
		$this->site = $xFUsers['name'];
		$this->email = $xFUsers['email'];
		if(!is_null($no) && is_numeric($no)) {
			$this->peruse($no);
		}
	}
	
	//Basic IO
	function insert($data) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		$databaseClass = new XFDatabase();
		return $databaseClass->insert($this->tool, $this->table, $data);
	}
	
	function update($data, $condition) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		$databaseClass = new XFDatabase();
		return $databaseClass->update($this->tool, $this->table, $data, $condition);
	}
	
	function delete($condition) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		$databaseClass = new XFDatabase();
		return $databaseClass->delete($this->tool, $this->table, $condition);
	}
	
	function browse($condition) {
		//Michael Son(michaelson@nate.com)
		//27JUN2014
		//29JUN2014 - Call of query() and condition() are changed to select().
	
		$dbClass = new XFDatabase();
		return $dbClass->select($this->tool, $this->table, $condition);
	}
	
	function peruse($no=NULL) {
		//Michael Son(michaelson@nate.com)
		//27JUN2014
		//29JUN2014 - Call of query() is changed to select().
		
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
			$this->permission = $result[0]['permission'];
			$this->name = $result[0]['name'];
			$this->birth = $result[0]['birth'];
			$this->languages = $result[0]['languages'];
			$this->etc = $result[0]['etc'];
		} else {
			$return = false;
		}
		return $return;
	}
	
	//Non-basic IO
	function signup($postWhat) {
		//06OCT2014(mson0129@gmail.com) - Rewrited
		
		$tableClass = new XFTable();
		$dbClass = new XFDatabase();
		
		//Create a user
		$temp = $postWhat;
		unset($temp['id'], $temp['password'], $temp['passwordConfirm']);
		$userData = $tableClass->import($temp);
		unset($temp);
		
		$result = $this->insert($userData);
		if($result==true) {
			$accountClass = new XFAccounts();
			
			if(strtolower(date_default_timezone_get())!="utc")
				date_default_timezone_set("UTC");
			
			//Create Local ID Accounts
			$temp['id'] = $postWhat['id'];
			foreach($postWhat['id'] as $row=>$val) {
				$temp['status'][$row] = "1";
				$temp['type'][$row] = "local";
				$temp['passwordDate'][$row] = time();
			}
			$temp['password'] = $postWhat['password'];
			$accountData = $tableClass->import($temp);
			$accountClass->create($accountData);
			
			//Create E-Mail Accounts
			$temp['id'] = $postWhat['email'];
			foreach($temp[type] as $row=>$val) {
				$temp[type][$row] = "email";
			}
			$accountData = $tableClass->import($temp);
			unset($temp);
			$accountClass->create($accountData);
		}
		
		unset($userData);
		$userData = $tableClass->import($postWhat);
		foreach($userData as $row=>$columns) {
			$condition[$row]['status'] = $columns['status'];
			$condition[$row]['permission'] = $columns['permission'];
			$condition[$row]['name'] = $columns['name'];
			$condition[$row]['birth'] = $columns['birth'];
			$condition[$row]['languages'] = $columns['languages'];
			$user = $this->browse($condition);
			unset($condition);
			if(is_array($user)) {
				$counter++;
				$account = $accountClass->checkAccount($columns['id']);
				$dbClass->insert("xfcore", "references", array(array(status=>"1", fromTool=>"xfcore", fromTable=>"accounts", fromNo=>$account[0][no], toTool=>"xfcore", toTable=>"users", toNo=>$user[0][no])));
				$account = $accountClass->checkAccount($columns['email']);
				$dbClass->insert("xfcore", "references", array(array(status=>"1", fromTool=>"xfcore", fromTable=>"accounts", fromNo=>$account[0][no], toTool=>"xfcore", toTable=>"users", toNo=>$user[0][no])));
				if($user[0]['status']==0)
					$this->sendConfirmation($user);
			}
			unset($user);
		}
		
		//Make references accounts with a user
		
		/*
		$columns = $dbClass->getColumns("xfcore", "accounts");
		foreach($columns as $value) {
			if(is_array($post[$value])) {
				$accountValue[$value] = $post[$value];
				if($value != "status")
					unset($post[$value]);
			}
		}
		$accountData = $tableClass->import($accountValue);
		$dbClass->insert("xfcore", "accounts", $accountData);
		$accountTable = $dbClass->select("xfcore", "accounts", $accountData);
		
		$userData = $tableClass->import($post);
		$this->create($userData);
		$userTable = $this->browse($userData);
		
		foreach($accountTable as $key=>$value) {
			$dbClass->insert("xfcore", "references", array(array("status"=>1, "fromTool"=>"xfcore", "fromTable"=>"users", "fromNo"=>$userTable[$key]['no'], "toTool"=>"xfcore", "toTable"=>"accounts", "toNo"=>$accountTable[$key]['no'])));
		}
		
		$this->signin($accountTable);
		*/
		if($counter>0)
			$return = true;
		else
			$return = false;
		return $return; 
	}
	
	function sendConfirmation($user) {
		foreach($user as $row=>$columns) {
			$temp[$row] = $user[$row];
			$code = base64_encode(hash_hmac('sha256', $_SERVER['HTTP_HOST'], $columns[no]));
			
			$title = new XFView("/xfacility/configs/view/xfusers-sendconfirmation-title.htm");
			$title->replace(array(name=>$this->site, email=>$this->email, user=>$temp));
			$message = new XFView("/xfacility/configs/view/xfusers-sendconfirmation-message.htm");
			$message->replace(array(name=>$this->site, email=>$this->email, user=>$temp, code=>$code));
			
			$mail = new XFMail(NULL, sprintf("%s<%s>", $columns['name'], $columns['email']), $title->show, $message->show);
			$mail->send();
			unset($temp);
		}
	}
	
	function confirm($userNo, $code) {
		return ($code==base64_encode(hash_hmac('sha256', $_SERVER['HTTP_HOST'], $userNo)))?true:false;
	}
	
	function withdraw($post) {
		$tableClass = new XFTable();
		$condition = $tableClass->import($post);
		$userTable = $this->browse($condition);
		foreach($userTable as $row => $columns) {
			$referencesClass = new XFReferences();
			unset($referencesClass);
		}
		return $this->delete($condition);
	}
	
	function signin($post) {
		$tableClass = new XFTable();
		$condition = $tableClass->import($post);
		$dbClass = new XFDatabase();
		$condition[0]['status'] = 1;
		if((!is_null($condition[0]['id']) && !is_null($condition[0]['password']) && is_null($condition[0]['type'])) || (!is_null($condition[0]['id']) && !is_null($condition[0]['type']))) {
			foreach($condition as $row => $columns) {
				if(!is_null($columns['no'])) {
					unset($condition[$row]);
					$condition[$row]['no'] = $columns['no'];
				}
			}
			$result = $dbClass->select("xfcore", "accounts", $condition);
			if($result!=false) {
				foreach($result as $row => $columns) {
					$condition = array(array("status" => 1, "fromTool" => "xfcore", "fromTable" => "accounts", "fromNo" => $columns['no'], "toTool" => "xfcore", "toTable" => "users"));
					$result = $dbClass->select("xfcore", "references", $condition);
					$return = $this->peruse($result[0]['toNo']);
					$tableClass->create($_SESSION['xfusers']);
					$result = $tableClass->browse(array(array("no"=>$this->no)));
					if(!is_array($result) && $return['status']==1)
						$_SESSION['xfusers'][] = $return;
					else if($return['status']!=1) {
						$return = false;
						$this->error = "status==".$return['status']; //0 = email confirm, -1 = permission of administrator
					}
					//print_r($_SESSION);
				}
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function signout($condition = NULL) {
		if(is_null($condition)) {
			unset($_SESSION['xfusers']);
		} else {
			$tableClass = new XFTable($_SESSION['xfusers']);
			$result = $tableClass->browse($condition);
			foreach($result as $value) {
				unset($_SESSION['xfusers'][$value]);
			}
			sort($_SESSION['xfusers']);
			if(!is_array($_SESSION['xfusers'][0])) {
				unset($_SESSION['xfusers']);
			}
		}
	}
	
	function isUserSignedIn($condition=NULL) {
		if(is_null($condition)) {
			if(is_array($_SESSION['xfusers'])) {
				$return = true;
			} else {
				//No user is signed in. (signout error)
				unset($_SESSION['xfusers']);
				$return = false;
			}
		} else {
			$tableClass = new XFTable($_SESSION['xfusers']);
			$result = $tableClass->browse($condition);
			if(count($result)>0) {
				$return = true;
			} else {
				$return = false;
			}
		}
		return $return;
	}
}
?>