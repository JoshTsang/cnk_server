<?php
	require '../macros.php';
	require 'defines.php';
    require '../classes/printer.php';
    
    $file =  "../".PRINTER_CONF;
    $printer = new printer($file);
 
	if(file_exists($file)) {
		$json = file_get_contents($file);
	} else {
	    die("printer conf not found");
	}
	
	$jsonObj = json_decode($json);
	$count = count($jsonObj);
	for ($i=0; $i<$count; $i++) {
		if ($jsonObj[$i]->usefor < 200) {
			$printer->printTestPage($jsonObj[$i]->ip, $jsonObj[$i]->title, $jsonObj[$i]->usefor, $jsonObj[$i]->type);
		}
	}
?>