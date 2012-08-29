<?php
   if(!isset($_GET['shopname'])) {
    	die("parameter needed");
    }	 
		
	function set($config){
		$file =  "shopname";
		file_put_contents($file, $config);
	}
	
	$config = $_GET['shopname'];
	set($config);
?>