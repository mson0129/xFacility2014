<?php
//xFacility2014
//XFObject(1.0.1.)
//Studio2b
//Michael Son(michaelson@nate.com)
//22JUN2014(1.0.0.) - This file is newly created.
//22SEP2014(1.0.1.) - Loader is updated.
//10FEB2015(1.1.0.) - encoding setup included.

//iconv_set_encoding("input_encoding", "UTF-8");
//iconv_set_encoding("output_encoding", "UTF-8");
//iconv_set_encoding("internal_encoding", "UTF-8");

function loader($classPath=NULL) {
	if(is_null($classPath))
		$classPath = "/xfacility/classes";
	if(substr($classPath, -1)=="/")
		$classPath = substr($classPath, 0, -1);
	$paths[0] = $_SERVER['DOCUMENT_ROOT'].$classPath;
	$j = 1;
	for($i=0; $i<$j; $i++) {
		$handle = opendir($paths[$i]);
		while(false !==($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if(!is_dir($paths[$i]."/".$file)) {
					if(substr($file, 0, 1)!=".") {
						if($_GET['debug']==true)
							echo $paths[$i]."/".$file;
						require_once($paths[$i]."/".$file);
						if($_GET['debug']==true)
							echo "...OK\n";
					}
				} else {
					$paths[] = $paths[$i]."/".$file;
					$j = count($paths);
				}
			}
		}
	}
	return false;
}
loader();

class XFObject {
}

$sessionClass = new XFSession();
?>
