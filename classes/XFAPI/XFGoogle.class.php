<?php
//xFacility2014
//XFGoogle
//Studio2b
//Michael Son(michaelson@nate.com)
//01DEC2014(1.0.0.) - This file is newly created.
//Google API OAuth - https://developers.google.com/accounts/docs/OAuth2WebServer

class XFGoogle extends XFObject {
	var $link;
	var $client_id, $redirect_uri, $client_secret;
	var $access_token, $refresh_token, $token_type, $expires_in;
	
	function XFGoogle($client_id=NULL, $redirect_uri=NULL, $client_secret=NULL) {
		require ($_SERVER['DOCUMENT_ROOT'].'/xfacility/configs/XFGoogle.config.php');
		if(is_null($client_id))
			$client_id = $xFGoogle['client_id'];
		if(is_null($redirect_uri))
			$redirect_uri = $xFGoogle['redirect_uri'];
		if(is_null($client_secret))
			$client_secret = $xFGoogle['client_secret'];
		$this->client_id = $client_id;
		$this->redirect_uri = $redirect_uri;
		$this->client_secret = $client_secret;
		$this->link = $this->getLink($client_id, $redirect_uri);
	}
	
	function getLink($client_id, $redirect_uri, $scope=NULL, $approval_prompt=NULL, $access_type=NULL, $state=NULL, $login_hint=NULL) {
		if(is_null($client_id)) {
			$client_id = $this->client_id;
		} else {
			$this->client_id = $client_id;
		}
		if(is_null($redirect_uri)) {
			$redirect_uri = $this->redirect_uri;
		} else {
			$this->redirect_uri = $redirect_uri;
		}
		if(!is_null($client_id) && !is_null($redirect_uri)) {
			$response_type="code";
			if($scope==0 || $scope=="all" || is_null($scope)) {
				$scope = "https://www.googleapis.com/auth/youtube";
			} else if($scope==1) {
				$scope = "https://www.googleapis.com/auth/youtube.readonly";
			} else if($scope==2) {
				$scope = "https://www.googleapis.com/auth/youtube.upload";
			}
			if($approval_prompt!="force")
				$approval_prompt="auto";
			if($access_type!="online" || $access_type!="offline")
				$access_type="online";
			if(!is_null($state))
				$state = "&state=".$state;
			$this->link = sprintf("https://accounts.google.com/o/oauth2/auth?client_id=%s&redirect_uri=%s&response_type=%s&scope=%s&approval_prompt=%s&access_type=%s%s", $client_id, urlencode($redirect_uri), $response_type, urlencode($scope), $approval_prompt, $access_type, $state);
			$return = $this->link;
		} else {
			$return = false;
		}
		return $return;
	}
	
	function getToken($code) {
		$data[code] = $code;
		$data[client_id] = $this->client_id;
		$data[client_secret] = $this->client_secret;
		$data[redirect_uri] = $this->redirect_uri;
		$data[grant_type] = "authorization_code";
		$curlClass = new XFCurl("POST", "https://accounts.google.com/o/oauth2/token", null, $data);
		$return = json_decode($curlClass->body, true);
		
		if(is_array($return) && is_null($return[error])) {
			$this->access_token = $return[access_token];
			$this->token_type = $return[token_type];
			$this->refresh_token = $return[refresh_token];
			$this->expires_in = $return[expires_in];
		} else {
			$return = false;
		}
		return $return;
	}
	
	function refreshToken($token) {
		if(is_null($token))
			$token = $this->refresh_token;
		if(!is_null($token)) {
			$data[client_id] = $this->client_id;
			$data[client_secret] = $this->client_secret;
			$data[refresh_token] = $token;
			$data[grant_type] = "refresh_token";
			$curlClass = new XFCurl("POST", "https://accounts.google.com/o/oauth2/token", null, $data);
			$return = json_decode($curlClass->body, true);
			
			if(is_array($return) && is_null($return[error])) {
				$this->access_token = $return[access_token];
				$this->expires_in = $return[expires_in];
				$this->token_type = $return[token_type];
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function unlink($token=NULL) {
		if(is_null($token))
			$token = $this->access_token;
		if(!is_null($token) && is_null($return[error])) {
			$data[token] = $token;
			$curlClass = new XFCurl("GET", "https://accounts.google.com/o/oauth2/revoke", null, $data);
			if($curlClass->httpCode==200) {
				$return = true;
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