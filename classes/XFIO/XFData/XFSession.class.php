<?php
//xFacility2014
//xFSession
//Studio2b
//Michael Son
//03JUL2014(1.0.0.) - This file is rewrited for xFacility2014. It's not compatible with xFacility2012.

class XFSession extends XFObject {
	private $session_db;
	var $max = 2592000;

	function XFSession($domain=NULL) {
		//AutoDomain
		if(is_null($domain) || strpos($_SERVER['HTTP_HOST'], $domain)===false) {
			$domains = explode(".", $_SERVER['HTTP_HOST']);
			for($i=count($domains)-1; $i>=0; $i--) {
				$return = ".".$domains[$i].$return;
				if(($i<count($domains)-1 && strlen($domains[$i])>2) || $i==count($domains)-3 || $domains[$i-1]=="www")
					break;
			}
			$domain = $return;
		} else {
			if(substr($domain, 1)!=".") {
				$domain = ".".$domain;
			}
		}
		
		//DB에 테이블 있는지 확인하고 테이블 없으면 생성
		session_set_cookie_params($this->max, "/", $domain);
		ini_set('session.cache_limiter' ,'nocache, must-revalidate');
		session_set_save_handler(
		array($this, "open"),
		array($this, "close"),
		array($this, "read"),
		array($this, "write"),
		array($this, "destroy"),
		array($this, "gc")
		);
		
		session_start();
	}

	function open($savePath, $id) {
		return false;
	}

	function close() {
		return NULL;
	}

	function read($id) {
		$dbClass = new XFDatabase();
		$return = $dbClass->query(sprintf("SELECT `data` FROM `xf_xfio_session` WHERE `id` = '%s';", $id));
		if ($result!==false)
			return stripslashes($return[0]['data']);
		return '';
	}

	function write($id, $data) {
		$dbClass = new XFDatabase();
		return $dbClass->query(sprintf("REPLACE INTO `xf_xfio_session` VALUES('%s', '%s', '%s', '%s')", $id, $_SERVER["REMOTE_ADDR"], time(), addslashes($data)));
	}

	function destroy($id) {
		$dbClass = new XFDatabase();
		return $dbClass->query(sprintf("DELETE FROM `xf_xfio_session` WHERE `id` = '%s'", $id));
	}

	function gc($maxlifetime) {
		$dbClass = new XFDatabase();
		return $dbClass->query(sprintf("DELETE FROM `xf_xfio_session` WHERE `timestamp` < '%s';OPTIMIZE TABLE `xf_xfio_session`;", time() - $this->max));
	}
}
?>