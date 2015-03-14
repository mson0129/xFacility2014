<?php
//xFacility2014
//xFHTTP
//Studio2b
//Michael Son
//19NOV2014(1.0.0.) - Newly added.

//http://www.w3.org/Protocols/HTTP/1.0/spec.html
class XFHTTP extends XFObject {
	var $method, $url, $what;
	var $protocol, $host, $port, $uri; //Parsing URL
	var $request, $result, $header, $body;
	
	function __construct($method, $url, $data=NULL) {
		//method
		if(strtolower($method)=="get" || strtolower($method)=="head" || strtolower($method)=="post") {
			$this->method = strtolower($method);
			//url
			if(!is_null($url)) {
				if($this->parseUrl($url)) {
					$this->request($data);
				}
			}
		}
	}
	
	function parseUrl($url) {
		//protocol
		$pos = strpos($url, '://');
		if($pos===false) {
			$return = false;
		} else {
			$this->protocol = strtolower(substr($url, 0, $pos));
			$url = substr($url, strlen($this->protocol."://"));
			//host
			if(strpos($url, "/")===false) {
				if(strpos($url, ":")===false) {
					$this->host = $url;
					$this->port = ($this->protocol == 'https') ? 443 : 80;
				} else {
					$this->host = substr($url, 0, strpos($url, ":"));
					$this->port = substr($url, strpos($url, ":")+1);
				}
				$this->uri = "/";
			} else {
				$temp = $url;
				$url = substr($url, 0, strpos($url, "/"));
				if(strpos($url, ":")===false) {
					$this->host = $url;
					$this->port = ($this->protocol == 'https') ? 443 : 80;
				} else {
					$this->host = substr($url, 0, strpos($url, ":"));
					$this->port = substr($url, strpos($url, ":")+1);
				}
				$this->uri = substr($temp, strpos($temp, "/"));
			}
			$return = true;
		}
		return $return;
	}
	
	function request($data=NULL) {
		$crlf = "\r\n";
		
		if(is_array($data)) {
			$step = 0;
			$temp[$step] = $data;
			$i=0;
			while(true) {
				if(count($temp[$step])>0) {
					foreach($temp[$step] as $key => $value) {
						unset($temp[$step][$key]);
						$varNames[$step] = $key;
						if(is_array($value)) {
							$temp[++$step] = $value;
							break;
						} else {
							if(!is_null($param))
								$param .= "&";
							foreach($varNames as $varKey => $varName) {
								if($varKey==0)
									$param .= $varName;
								else
									$param .= sprintf("[%s]", $varName);
							}
							$param .= "=".urlencode($value);
						}
					}
				} else {
					if($step==0) {
						break;
					} else {
						unset($varNames[$step]);
						$step--;
					}
				}
			}
		}
		
		if($this->method=="get") {
			if(!is_null($param))
				$param = "?".$param;
			$this->request = ucfirst($this->method)." ".$this->uri.$param." HTTP/1.0".$crlf
			."Host: ".$this->host.$crlf
			.$crlf;
		} else if($this->method=="post") {
			$this->request = ucfirst($this->method)." ".$this->uri." HTTP/1.0".$crlf
			."Host: ".$this->host.$crlf
			.$param.$crlf
			.$crlf;
		}
		
		$fp = fsockopen(($this->protocol == 'https' ? "ssl://" : "").$this->host, $this->port);
		if ($fp!==false) {
			fwrite($fp, $this->request);
			while (!feof($fp)) {
				$return .= fgets($fp, 1024);
			}
			fclose($fp);
		}
		
		list($this->header, $this->body) = explode("\r\n\r\n", $return);
		$this->result = $return;
		
		return $return;
	}
}
?>