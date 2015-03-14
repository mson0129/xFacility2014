<?php
//xFacility2014
//XFView
//Studio2b
//Michael Son(michaelson@nate.com)
//01JUL2014(1.0.0.) - This file is newly writed.
//19JUL2014(1.0.1.) - A bug of replace() is modified.
//06FEB2015(1.0.2.) - A bug of replace() is modified.

class XFView extends XFObject {
	var $view, $show;
	var $basePath, $application, $languages, $file;
	
	function XFView($template=NULL, $replacements=NULL) {
		if(!is_null($template))
			$this->create($template, $replacements);
	}
	
	function create($template, $replacements=NULL) {
		unset($this->view);
		unset($this->basePath, $this->application, $this->languages, $this->file);
		if(substr($template, 0, 1)=="/") {
			$this->import($template);
		} else {
			$this->view = $template;
			$this->show = $this->view;
		}
		
		if(!is_null($replacements))
			$this->replace($replacements);
		
		return $this->view;
	}
	
	function import($path=NULL) {
		// ~/application/resources/en-us/template.htm
		//Application=-4, Language=-2, file=-1
		// ~/application/resources/template.htm
		//Application=-3, Language=NULL, file=-1
		// ~/application/en-us/template.htm
		//Application=-3, Language=-2, file=-1
		// ~/application/template.htm
		//Application=-2, Language=NULL, file=-1
		if(!is_null($path) && substr($path, 0, 1)=="/") {
			$paths = explode("/", substr($path, 1));
			unset($path);
			for($i=0; $i<count($paths); $i++) {
				if($i==count($paths)-4) {
					$this->application = strtolower($paths[$i]);
					$this->basePath .= "/".$paths[$i];
				} else if($i==count($paths)-3) {
					if(strtolower($paths[$i])=="resources") {
					} else {
						$this->application = strtolower($paths[$i]);
					}
					$this->basePath .= "/".$paths[$i];
				} else if($i==count($paths)-2) {
					if(strtolower($paths[$i])=="resources") {
						$this->basePath .= "/".$paths[$i];
					} else if(strpos($paths[$i], "-")!==false && strlen($paths[$i])==5) {
						$this->languages[] = strtolower($paths[$i]);
					} else {
						$this->application = strtolower($paths[$i]);
						$this->basePath .= "/".$paths[$i];
					}
				} else if($i==count($paths)-1) {
					$this->file = strtolower($paths[$i]);
				} else {
					$this->basePath .= "/".$paths[$i];
				}
			}
		}
		
		if(is_null($this->language)) {
			$languageClass = new XFLanguage();
			$this->languages = $languageClass->selectLanguages($this->basePath);
		}
		
		foreach($this->languages as $language) {
			$path = $this->basePath."/".$language."/".$this->file;
			if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {
				$fileFlag = true;
				break;
			}
		}
		
		if(substr($path, 0, 1)=="/" && $fileFlag==true) {
			$fileClass = new XFFile($path);
			$this->view = $fileClass->peruse();
			$this->show = $this->view;
			$return = $this->view;
		} else {
			$return = false;
		}
		return $return;
	}
	
	function replace($replacements) {
		if(!is_null($this->view)) {
			$return = $this->view;
			if(is_array($replacements)) {
				foreach($replacements as $key => $value) {
					if(substr($key, 0, 1)=="[" && substr($key, -1)=="]" && !is_array($value)) {
						$return = str_replace($key, $value, $return);
					} else if(is_array($value)) {
						if(substr_count($return, "<!--[".$key."]-->")>0 && substr_count($return, "<!--[/".$key."]-->")>0 && substr_count($return, "<!--[".$key."]-->")==substr_count($return, "<!--[/".$key."]-->")) {
							$temps = explode("<!--[".$key."]-->", $return);
							foreach($temps as $tempsKey => $temp) {
								if($tempsKey==0)
									continue;
								list($loops[], $tails[]) = explode("<!--[/".$key."]-->", $temp, 2);
							}
							$return = $temps[0];
							foreach($loops as $loop => $none) {
								foreach($value as $row => $columns) {
									$tempLoop = $loops[$loop];
									foreach($columns as $column => $columnValue)
										$tempLoop = str_replace("[=".$key.":".$column."]", $columnValue, $tempLoop);
									$tempLoop = str_replace("[=".$key.":#]", $row, $tempLoop);
									$return .= $tempLoop;
								}
								$return .= $tails[$loop];
							}
						}
						
						unset($loops, $tails, $tempLoop);
					} else {
						$return = str_replace("<!--[=".$key."]-->", $value, $return);
						$return = str_replace("[=".$key."]", $value, $return);
						if(substr_count($return, "<!--[".$key."]-->")>0 && substr_count($return, "<!--[/".$key."]-->")>0 && substr_count($return, "<!--[".$key."]-->")==substr_count($return, "<!--[/".$key."]-->")) {
							$temps = explode("<!--[".$key."]-->", $return);
							foreach($temps as $tempsKey => $temp) {
								if($tempsKey==0)
									continue;
								list($loops[], $tails[]) = explode("<!--[/".$key."]-->", $temp, 2);
							}
							$return = $temps[0];
							foreach($loops as $loop => $none) {
								$return .= $tails[$loop];
							}
						}
						if(substr_count($return, "[=".$key.":")>0) {
							$temps = explode("[=".$key.":", $return);
							foreach($temps as $tempsKey => $temp) {
								if($tempsKey==0)
									continue;
								list($loops[], $tails[]) = explode("]", $temp, 2);
							}
							$return = $temps[0];
							foreach($loops as $loop => $none) {
								$return .= $tails[$loop];
							}
						}
						unset($loops, $tails, $tempLoop);
					}
				}
				$this->view = $return;
				$this->show = $this->view;
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function show() {
		echo $this->view;
	}
}
?>