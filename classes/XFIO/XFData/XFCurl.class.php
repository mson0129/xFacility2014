<?php
//xFacility2014
//xFCurl
//Studio2b
//Michael Son
//30NOV2014(1.0.0.) - Newly added.

class XFCurl extends XFObject {
	var $method, $url, $what;
	var $protocol, $host, $port, $uri; //Parsing URL
	var $request, $result, $header, $body, $httpCode;

	function __construct($method, $url, $header=NULL, $data=NULL) {
		//method
		$this->method = strtolower($method);
		//url
		if(!is_null($url)) {
			$this->url = $url;
			if(!is_null($header) || !is_null($data)) {
				$this->request($header, $data);
			}
		}
	}
	
	function getParameter($data) {
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
		
		return $param;
	}
	
	function request($header=NULL, $data=NULL) {
		if(is_null($header) && is_null($data))
			return false;
		$crlf = "\r\n";
		
		if($this->method=="get") {
			$param = $this->getParameter($data);
			if(!is_null($param))
				$this->url .= "?".$param;
		} else {
			if(is_array($header)) {
				foreach($header as $value) {
					if(strpos($value, "json")!==false) {
						$jsonFlag = true;
						break;
					}
				}
			}
			if($jsonFlag==true) {
				$param = json_encode($data);
			} else {
				$param = $this->getParameter($data);
			}
			$postParam = $param;
		}
		
		$options = array(
			CURLOPT_RETURNTRANSFER => true,         // return web page
			CURLOPT_HEADER         => true,
			CURLINFO_HEADER_OUT		=> true,	//To get my reqest message
			CURLOPT_FOLLOWLOCATION => true,         // follow redirects
			CURLOPT_ENCODING       => "",           // handle all encodings
			CURLOPT_USERAGENT      => "xFacility",     // who am i
			CURLOPT_AUTOREFERER    => true,         // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
			CURLOPT_TIMEOUT        => 120,          // timeout on response
			CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirect
			CURLOPT_SSLVERSION => 1,
			CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
			CURLOPT_SSL_VERIFYPEER => false,        //
			CURLOPT_VERBOSE        => 1
		);
		
		$ch = curl_init($this->url);
		curl_setopt_array($ch, $options);
		if($this->method=="post") {
			curl_setopt($ch, CURLOPT_POST, true);
		} else {
			curl_setopt($ch, CURLOPT_POST, false);
			if($this->method!="get") {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
			}
			if($this->method=="head") {
				curl_setopt($ch, CURLOPT_NOBODY, true);
			}
		}
		if(!is_null($postParam) && $this->method!="head")
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
		if(is_array($header) && !is_null($header))
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		$return = curl_exec($ch);
		$this->request = curl_getinfo($ch, CURLINFO_HEADER_OUT);
		$this->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		list($this->header, $this->body) = explode("\r\n\r\n", $return);
		$this->result = $return;
		
		return $return;
	}
}
?>