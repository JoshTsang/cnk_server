<?php
    function get(){
 		$file =  "printerInfo.json";
 
		// 判断文件是否存在，不存在则创建
		if(file_exists($file)) {
			$timestamp = file_get_contents($file);
		} else {
			$timestamp = "";
		}
		return $timestamp;
	}
	
	$json = get();
	echo "$json";
?>