<?php
//xFacility2014
//XFLanguage
//Studio2b
//Michael Son(michaelson@nate.com)
//22JUN2014(0.1.0.) - This file is newly created.
//01JUL2014(1.0.0.) - selectedLanguages(), getXFacilityLanguages(), getApplicationLanguages(), getBrowserLanguages(), getUserLanguages() and getSelectedLanguages() are created.
//22SEP2014(1.0.1.) - getSelectedLanguage() is updated. It doesn't support only $languages, but $lang also now.

class XFLanguage extends XFObject {
	var $configPath = "/xfacility/configs/XFLanguage.config.php";
	var $serverLanguages, $clientLanguages;
	var $xFacilityLanguages, $applicationLanguages;
	var $browserLanguages, $userLanguages, $getLanguages, $postLanguages;
	var $selectedLanguages;
	
	function XFLanguage($application=NULL, $xFacility = false) {
		$this->selectedLanguages = $this->selectLanguages($application, $xFacility);
	}
	
	function selectLanguages($application=NULL, $xFacility = false) {
		//Server: Application > xFacility
		$this->getXFacilityLanguages();
		$this->getApplicationLanguages($application);
		$serverLanguages = array("server");
		if(is_array($this->applicationLanguages) && !is_null($application))
			$serverLanguages = array_merge($serverLanguages, $this->applicationLanguages);
		if(is_array($this->xFacilityLanguages) && ($xFacility==true || is_null($application)))
			$serverLanguages = array_merge($serverLanguages, $this->xFacilityLanguages);
		unset($serverLanguages[0]);
		$serverLanguages = array_unique($serverLanguages);
		foreach($serverLanguages as $serverLanguage) {
			$this->serverLanguages[] = $serverLanguage;
		}
		
		//Client: GET || POST > User > Browser
		$this->getBrowserLanguages();
		$this->getUserLanguages();
		$this->getSelectedLanguages();
		$clientLanguages = array("client");
		if(is_array($this->getLanguages)) {
			$clientLanguages = array_merge($clientLanguages, $this->getLanguages);
		}
		if(is_array($this->postLanguages)) {
			$clientLanguages = array_merge($clientLanguages, $this->postLanguages);
		}
		if(is_array($this->userLanguages)) {
			$clientLanguages = array_merge($clientLanguages, $this->userLanguages);
		}
		if(is_array($this->browserLanguages)) {
			$clientLanguages = array_merge($clientLanguages, $this->browserLanguages);
		}
		unset($clientLanguages[0]);
		$clientLanguages = array_unique($clientLanguages);
		foreach($clientLanguages as $clientLanguage) {
			$this->clientLanguages[] = $clientLanguage;
		}
		
		foreach($this->clientLanguages as $clientLanguage) {
			foreach($this->serverLanguages as $serverLanguage) {
				if($clientLanguage==$serverLanguage) {
					$return[] = $serverLanguage;
					$flag=true;
				}
			}
		}
		if($flag==true) {
			$return = array_unique($return);
		} else {
			$return = $this->serverLanguages;
		}
		
		return $return;
	}
	
	//Server
	function getXFacilityLanguages() {
		require ($_SERVER['DOCUMENT_ROOT'].$this->configPath);
		$this->xFacilityLanguages = $xFLanguage;
		return $this->xFacilityLanguages;
	}
	
	function getApplicationLanguages($application) {
		if(substr($application, 0, 1)!="/") {
			$path = "/studio2b.kr/".$application."/resources";
		} else {
			$path = $application;
		}
		if(!is_null($application) && is_dir($_SERVER['DOCUMENT_ROOT'].$path)) {
			$dirClass = new XFDirectory($path);
			$temp = $dirClass->listDirectory();
			foreach($temp as $directory) {
				if(strlen($directory)==5 && strpos($directory, "-")!==false)
					$return[] = $directory;
			}
			$this->applicationLanguages = $return;
		} else {
			$return = false;
		}
		return $return;
	}
	
	//Client
	function getBrowserLanguages() {
		$langsWithWeight = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		foreach($langsWithWeight as $value) {
			list($return[], $trashcan) = explode(";", $value, 2);
		}
		unset($value);
		foreach($return as $value) {
			if(strlen($value)==5 && strpos($value, "-")!==false)
				$this->browserLanguages[] = trim(strtolower($value));
		}
		return $this->browserLanguages;
	}
	
	function getUserLanguages() {
		if(!is_null($_SESSION['xfusers'][0])) {
			$languages = explode(";", $_SESSION['xfusers'][0]['languages']);
			foreach($languages as $language) {
				if(strlen($language)==5 && strpos($language, "-")!==false)
					$return[] = strtolower($language);
			}
			$this->userLanguages = $return;
		} else {
			$return = false;	
		}
		return $return;
	}
	
	function getSelectedLanguages($mode="both") {
		for($i=0; $i<2; $i++) {
			if($i==0) {
				if(is_null($_GET['lang'])) {
					$selectedLanguages = $_GET['languages'];
				} else {
					$selectedLanguages = $_GET['lang'];
				}
				if(strtolower($mode)=="post")
					continue;
			} else if($i==1) {
				if(is_null($_GET['lang'])) {
					$selectedLanguages = $_POST['languages'];
				} else {
					$selectedLanguages = $_POST['lang'];
				}
				if(strtolower($mode)=="get")
					break;
			}
			
			if(!is_null($selectedLanguages)) {
				$languages = explode(";", $selectedLanguages);
				//print_r($languages);
				foreach($languages as $language) {
					if(strlen($language)==5 && strpos($language, "-")!==false)
						$temp[] = strtolower($language);
				}
				if($i==0) {
					$this->getLanguages = $temp;
				} else if($i==1) {
					$this->postLanguages = $temp;
				}
				unset($temp);
			} else {
				continue;
			}
		}
		if(strtolower($mode)=="get")
			$return = $this->getLanguages;
		else if(strtolower($mode)=="post")
			$return = $this->postLanguages;
		else
			$return = array_unique(array_merge($this->getLanguages, $this->postLanguages));
		return $return;
	}
}
?>