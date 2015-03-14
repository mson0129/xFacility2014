<?php
//in-dev.
class XFTwitter extends XFObject {
	var $redirect_uri, $consumer_secret, $consumer_key;
	var $access_token, $token_type, $refresh_token, $expires_in, $scope;
	var $id, $nickname, $thumbnail_image, $profile_image, $properties;
	
	
	//https://dev.twitter.com/web/sign-in/implementing
	//https://apps.twitter.com/app/7902228
	function XFTwitter() {
		require ($_SERVER['DOCUMENT_ROOT'].'/xfacility/configs/XFTwitter.config.php');
		$this->redirect_uri = $xFTwitter['redirect_uri'];
		$this->consumer_secret = $xFTwitter['consumer_secret'];
		$this->consumer_key = $xFTwitter['consumer_key'];
	}
	
	function signin($code) {
		$this->getToken($code);
		$this->getProfile();
		$xfuserClass = new XFUsers();
		$return = $xfuserClass->signin(array("type"=>array("kakao"), "id"=>array($this->id)));
	
		return $return;
	}
	
	function getToken() {
		$header[] = 'Authorization:';
		$header[] = sprintf('oauth_consumer_key="%s",', $this->consumer_key);
		$header[] = sprintf('oauth_nonce="%s",', trim(base64_encode(time()), '='));
		$header[] = sprintf('oauth_signature_method="%s",', 'HMAC-SHA1');
		$header[] = sprintf('oauth_timestamp="%s",', time());
		//$header[] = sprintf('oauth_token="%s"', NULL);
		$header[] = 'oauth_version="1.0"';
		$curlClass = new XFCurl("POST", "http://dev.twitter.com/oauth/request_token", $header);
		$return = json_decode($curlClass->body, true);
		if(is_array($return)) {
			print_r($return);
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