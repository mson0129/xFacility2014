<?php
class XFMail extends XFObject {
	var $from, $to, $subject, $message, $headers, $boundary;
	
	function XFMail($from, $to, $subject, $message) {
		$this->boundary = uniqid("PDFSender");
		
		//From
		if(is_null($from)) {
			$this->from = "donotreply@".end(explode(".", $_SERVER['SERVER_NAME'], 2));
		} else {
			if(substr_count($from, "<") > 0) {
				$temp = explode("<", $from, 2);
				$this->from = "=?UTF-8?B?".base64_encode($temp[0])."?=<".$temp[1];
			} else {
				$this->from = $from;
			}
		}
		//To
		if(substr_count($to, "<") > 0) {
			$temp = explode("<", $to, 2);
			$this->to = "=?UTF-8?B?".base64_encode($temp[0])."?=<".$temp[1];
			unset($temp);
		} else {
			$this->to = $to;
		}
		//Subject
		$this->subject = "=?UTF-8?B?".base64_encode($subject)."?=";
		//Message
		$this->message = "--$this->boundary\r\n" .
		"Content-Type: text/html; charset=UTF-8\r\n" .
		"Content-Transfer-Encoding: base64\r\n\r\n";
		$this->message .= chunk_split(base64_encode($message));
		//Headers
		$this->headers = "MIME-Version: 1.0\r\n";
		$this->headers .= "Content-Type: multipart/mixed; boundary = ".$this->boundary."; charset=UTF-8\r\n".
			"From: ".$this->from . "\r\n" .
			"Reply-To: ".$this->from . "\r\n" .
			"X-Mailer: PHP/" . phpversion();
	}
	
	function send() {
		$return = mail($this->to, $this->subject, $this->message, $this->headers, "-fdonotreply@".end(explode(".", $_SERVER['SERVER_NAME'], 2)));
		if($return===false)
			echo "error";
	}
}