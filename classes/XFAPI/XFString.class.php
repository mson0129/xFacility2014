<?php
//xFacility2014
//XFString(1.0.1.)
//Studio2b
//Michael Son(michaelson@nate.com)
//27JUN2014 - This file is newly created.
//28JUN2014 - browse() is updated.

class XFString extends XFObject {
	var $string;
	
	function XFString($str) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		
		$this->string = $str;
	}
	
	function create($string) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		
		$this->string = $string;
		return true;
	}
	
	function delete() {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		
		unset($this->string);
		return true;
	}
	
	function modify($array) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		
		$return = $this->string;
		foreach($array as $key => $value) {
			if(!is_array($value))
				$return = str_replace($key, $value, $return);
		}
		$this->string = $return;
		return true;
	}
	
	function browse($needles, $table=false) {
		/*
		Michael Son(michaelson@nate.com)
		
		27JUN2014 - New
		28JUN2014 - This function supports inputing array.
		
		INPUT:
		$this->string = "aaaahelloworldhello";
		$needles = array("aaa", "hello", "world);
		$table = true;
		
		OUTPUT:
		$return[0]['position'] = 0;
		$return[0]['keyword'] = "aaa";
		$return[1]['position'] = 4;
		$return[1]['keyword'] = "hello";
		$return[2]['position'] = 9;
		$return[2]['keyword'] = "world";
		$return[3]['position'] = 14;
		$return[3]['keyword'] = "hello";
		*/
		
		if(!is_array($needles)) {
			$temp[0] = $needles;
			$needles = $temp;	
		}
		//print_r($needles);
		foreach($needles as $needle) {
			//echo $needle;
			//echo $this->string;
			for($i=0; $i<strlen($this->string); $i) {
				if($i+strlen($needle)>=strlen($this->string))
					break;
				//echo "Now:".$i."<br />\n";
				//echo strpos($this->string, $needle, $i)."<br />\n";
				if(strpos($this->string, $needle, $i)===false)
					break;
				if($table==true) {
					$key = strpos($this->string, $needle, $i);
					$value = $needle;
					$temp[$key] = $value;
				} else {
					$return[] = strpos($this->string, $needle, $i);
				}
				$i = strpos($this->string, $needle, $i) + strlen($needle);
			}
		}
		if($table==true) {
			ksort($temp);
			$i=0;
			foreach($temp as $key => $value) {
				$return[$i]['position'] =  $key;
				$return[$i]['keyword'] = $value;
				$i++;
			}
		} else {
			sort($return);
		}
		return $return;
	}
	
	function replace($array) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		
		return $this->modify($array);
	}
	
	function search($needle) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		
		return $this->browse($needle);
	}
}
?>