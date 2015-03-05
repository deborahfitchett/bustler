<?php
function checkCache($url,$cachetime) {
	$filepath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . md5($url);
	if (!is_file($filepath)) {
		return false;
	} else {
		$timestamp = filemtime($filepath);
		if ($timestamp + $cachetime > time()) {
			return readFrom($filepath);
		} else {
			return false;
		}
	}
}

function readFrom($filepath) {
	$handle = fopen($filepath, "r");
   	$content = fread($handle, filesize($filepath));
	fclose($handle);
	return $content;
}

function writeTo($content,$filepath) {
	$handle = fopen($filepath, "w");
	$success = fwrite($handle,$content);
	fclose($handle);
	return $success;	// number of bytes or FALSE
}

function cachedCurl($url,$cachetime) {
	$filepath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . md5($url);
	$content = checkCache($url,$cachetime);
	if (!$content) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		curl_close($ch);
		if ($content) {
			writeTo($content,$filepath);
		}
	}
	if (!$content) {
		$content = readFrom($filepath);
	}
	return $content;
}
?>