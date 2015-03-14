<?php
//xFacility2014
//XFKakao
//Studio2b
//Michael Son(michaelson@nate.com)
//01DEC2014(1.0.0.) - This file is newly created. Kakao REST API compatible.
class XFKakao extends XFObject {
	var $appkey, $redirect_uri;
	var $link;
	var $access_token, $token_type, $refresh_token, $expires_in, $scope;
	var $id, $nickname, $thumbnail_image, $profile_image, $properties;
	
	function XFKakao($appkey=null, $redirect_uri=null) {
		require ($_SERVER['DOCUMENT_ROOT'].'/xfacility/configs/XFKakao.config.php');
		if(is_null($appkey))
			$appkey = $xFKakao['appkey'];
		if(is_null($redirect_uri))
			$redirect_uri = $xFKakao['redirect_uri'];
		$this->appkey = $appkey;
		$this->redirect_uri = $redirect_uri;
		$this->link = $this->getLink();
	}
	
	function getLink() {
		return sprintf("https://kauth.kakao.com/oauth/authorize?client_id=%s&redirect_uri=%s&response_type=code", $this->appkey, urlencode($this->redirect_uri));
	}
	
	function signin($code) {
		$this->getToken($code);
		$this->getProfile();
		$xfuserClass = new XFUsers();
		$return = $xfuserClass->signin(array("type"=>array("kakao"), "id"=>array($this->id)));
	
		return $return;
	}
	
	function getToken($code) {
		$data[grant_type] = "authorization_code";
		$data[client_id] = $this->appkey;
		$data[redirect_uri] = $this->redirect_uri;
		$data[code] = $code;
		$curlClass = new XFCurl("POST", "https://kauth.kakao.com/oauth/token", null, $data);
		$return = json_decode($curlClass->body, true);
		if(is_array($return)) {
			$this->access_token = $return[access_token];
			$this->token_type = $return[token_type];
			$this->refresh_token = $return[refresh_token];
			$this->expires_in = $return[expires_in];
			$this->scope = $return[scope];
		} else {
			$return = false;
		}
		return $return;
	}
	
	function refreshToken($token=NULL) {
		//Not tested.
		if(is_null($token))
			$token = $this->refresh_token;
		if(!is_null($token)) {
			$data[grant_type] = "authorization_code";
			$data[client_id] = $this->appkey;
			$data[refresh_token] = $token;
			$curlClass = new XFCurl("POST", "https://kauth.kakao.com/oauth/token", null, $data);
			$return = json_decode($curlClass->body, true);
			if(is_array($return)) {
				$this->access_token = $return[access_token];
				$this->token_type = $return[token_type];
				$this->refresh_token = $return[refresh_token];
				$this->expires_in = $return[expires_in];
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function signout($token=NULL) {
		//Not tested.
		if(is_null($token))
			$token = $this->access_token;
		if(!is_null($token)) {
			$header[] = "Authorization: ".$this->token_type." ".$token;
			$curlClass = new XFCurl("POST", "https://kapi.kakao.com/v1/user/logout", $header, null);
			$return = json_decode($curlClass->body, true);
		
			if($return[id]==$this->id) {
				$return = true;
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function unlink($token=NULL) {
		//Not tested.
		if(is_null($token))
			$token = $this->access_token;
		if(!is_null($token)) {
			$header[] = "Authorization: ".$this->token_type." ".$token;
			$curlClass = new XFCurl("POST", "https://kapi.kakao.com/v1/user/unlink", $header, null);
			$return = json_decode($curlClass->body, true);
				
			if($return[id]==$this->id) {
				$return = true;
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function getProfile($token=NULL) {
		if(is_null($token))
			$token = $this->access_token;
		if(!is_null($token)) {
			$header[] = "Authorization: ".$this->token_type." ".$token;
			$curlClass = new XFCurl("GET", "https://kapi.kakao.com/v1/user/me", $header, null);
			$return = json_decode($curlClass->body, true);
			if(is_array($return)) {
				$this->id = $return[id];
				$this->nickname = $return[properties][nickname];
				$this->thumbnail_image = $return[properties][thumbnail_image];
				$this->profile_image = $return[properties][profile_image];
				$this->properties = $return[properties];
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
}
?>