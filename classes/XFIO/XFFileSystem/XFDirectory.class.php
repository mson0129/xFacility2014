<?php
//xFacility2014
//XFDirectory(1.1.0.)
//Studio2b
//Michael Son(michaelson@nate.com)
//23JUN2014
//01JUL2014 - listDirectory() and listFile() are added.

class XFDirectory extends XFObject {
	var $path, $fullPath;
	
	function XFDirectory($path = NULL) {
		//Add front slash
		if(substr($path, 0, 1)!="/")
			$path = "/".$path;
		//Remove end slash
		if(substr($path, -1)=="/")
			$path = substr($path, 0, strlen($path)-1);
		//Set a path
		if(!is_null($path)) {
			$this->fullPath = $_SERVER['DOCUMENT_ROOT'].$path;
			$this->path = $path;
		} else {
			$this->fullPath = $_SERVER['DOCUMENT_ROOT'];
		}
	}
	
	//path
	function goToParent() {
		if(substr($this->path, 0, 1)=="/")
			$tempPath = substr($this->path, 1, strlen($this->path));
		$temp = explode("/", $tempPath);
		for($i=0; $i<count($temp)-1; $i++) {
			$return .= "/".$temp[$i];
		}
		$this->path = $return;
		$this->fullPath = $_SERVER['DOCUMENT_ROOT'].$return;
		return $return;
	}
	
	function goToSub($directory) {
		$this->path .= "/".$directory;
		$this->fullPath .= "/".$directory;
		return $this->path;
	}
	
	//IO
	function create() {
		$directories = explode("/", $this->fullPath);
		foreach($directories as $directory) {
			$now .= "/".$directory;
			if(!is_dir($now))
				if(!mkdir($now))
					return false;
		}
		return true;
	}
	
	function modify($newPath) {
		if(is_dir($_SERVER['DOCUMENT_ROOT']."/".$newPath) && !is_dir($this->fullPath)) {
			$return = false;
		} else {
			rename($this->fullPath, $_SERVER['DOCUMENT_ROOT']."/".$newPath);
			$return = $_SERVER['DOCUMENT_ROOT']."/".$newPath;
		}
		return $return;	
	}
	
	function delete() {
		//rmdirAll
		//Kim, Gyoung Han
		//Original: http://flystone.tistory.com/54
		//Not Approved
		
		$directories = dir($this->fullPath);
		while(false !== ($entry = $directories->read())) {
			if(($entry != '.') && ($entry != '..')) {
				if(is_dir($this->fullPath.'/'.$entry)) {
					rmdirAll($this->fullPath.'/'.$entry);
				} else {
					@unlink($this->fullPath.'/'.$entry);
				}
			}
		}
		$directories->close();
		@rmdir($name);
	}
	
	function browse() {
		//Get Info
		if(is_dir($this->fullPath)) {
			$return = pathinfo($this->fullPath);
		} else {
			$return = false;
		}
		return $return;
	}
	
	function peruse() {
		//Contents
		if(is_dir($this->fullPath)) {
			$handle = opendir($this->fullPath);
		} else {
			$handle = opendir(dirname($this->fullPath));
		}
		while (false !== ($subPath = readdir($handle))) {
			if ($subPath != "." && $subPath != "..") {
				if($includePath==true) {
					$return[] = $this->path."/".$subPath;
				} else {
					$return[] = $subPath;
				}
			}
		}
		closedir($handle);
		
		return $return;
	}
	
	function listDirectory() {
		if(is_dir($this->fullPath)) {
			$handle = opendir($this->fullPath);
		} else {
			$handle = opendir(dirname($this->fullPath));
		}
		while (false !== ($dir = readdir($handle))) {
			if ($dir != "." && $dir != "..") {
				if(is_dir($this->fullPath."/".$dir)) {
					if($includePath==true) {
						$return[] = $this->path."/".$dir;
					} else {
						$return[] = $dir;
					}
				}
			}
		}
		closedir($handle);
		return $return;
	}
	
	function listFile() {
		if(is_dir($this->fullPath)) {
			$handle = opendir($this->fullPath);
		} else {
			$handle = opendir(dirname($this->fullPath));
		}
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if(file_exists($this->fullPath."/".$file)) {
					if($includePath==true) {
						$return[] = $this->path."/".$file;
					} else {
						$return[] = $file;
					}
				}
			}
		}
		closedir($handle);
		return $return;
	}
}
?>