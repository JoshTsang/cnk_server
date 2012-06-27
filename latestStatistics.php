<?php
	require('macros.php');
	function get(){
 		$file =  "../db/latestStatistics.txt";
 
		// 判断文件是否存在，不存在则创建
		if(file_exists($file))
		{
			$timestamp = file_get_contents($file);
		}
		else
		{
			file_put_contents($file, "2012-01-01 00:00");
			$timestamp = "2012-01-01 00:00";
		}
		return $timestamp;
	}
	 
		
	function set($timestamp){
		$file =  "../db/latestStatistics.txt";
		file_put_contents($file, $timestamp);
	}
	
	$action = $_GET['do'];
	if ($action == "get") {
		$timestamp = get();
		echo "[$timestamp]";
	} else if($action == "set") {
		$date = $_GET['date'];
		$time = $_GET['time'];
		set($date." ".$time);
		echo "$timestamp";
	}
 
?>
	