<?php
    if(isset($_POST['config'])) {
    	die("parameter needed");
    }
	
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
	 
		
	function set($config){
		$file =  "printerInfo.json";
		file_put_contents($file, $config);
	}
	
	$config = $_POST['config'];
	set($config);
?>