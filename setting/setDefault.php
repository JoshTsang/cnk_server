<?php
    require('../macros.php');
	require('defines.php');
	
	$item = array('name' => 'checkout',
				  'ip' => "PRINTER_FOR_CHECKOUT",
				  'type' => PRINTER_TYPE_58,
				  'title' => "存根联",
				  'usefor' => PRINTER_ORDER);
	$table[0] = $item;
	
	$item = array('name' => 'checkout',
				  'ip' => PRINTER_FOR_CHECKOUT,
				  'type' => PRINTER_TYPE_58,
				  'title' => "存根联",
				  'usefor' => PRINTER_STATISTICS);
	$table[1] = $item;
	
	$config = json_encode($table);
	$file = "printerInfo.json";
	file_put_contents($file, $config);
?>