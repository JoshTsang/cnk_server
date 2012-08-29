<?php
	require '../macros.php';
	require 'defines.php';
    require '../print.php';
function printTestPage($printerIP, $title, $usefor) {
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
	if ($socket < 0)
	{
		echo socket_strerror(socket_last_error())."\n";
		die("Unable to connect printer.ip:$printerIP");
	}
	$connection = socket_connect($socket, $printerIP, 9100); 
	if (!$connection) {
		echo socket_strerror(socket_last_error())."\n";
		die("Unable to connect printer.ip:$printerIP");
	}
	printl($socket, "菜脑壳电子点菜系统\r\n打印机测试\r\n");
	if(file_exists("shopname")) {
		$shopname = file_get_contents("shopname");
	} else {
		$shopname = "未设置";
	}
	printl($socket, "店铺名:".$shopname);
	printl($socket, "打印机IP:".$printerIP);
	printl($socket, "小票抬头:".$title);
	printl($socket, "打印内容码:".$usefor);
	printl($socket, "\r\n");
	
	printR($socket, PRINTER_COMMAND_CUT);
	printR($socket, PRINTER_COMMAND_ALARM);
	socket_close($socket);
}
	$file =  "printerInfo.json";
 
	if(file_exists($file)) {
		$json = file_get_contents($file);
	}
	
	$jsonObj = json_decode($json);
	$count = count($jsonObj);
	for ($i=0; $i<$count; $i++) {
		printTestPage($jsonObj[$i]->ip, $jsonObj[$i]->title, $jsonObj[$i]->usefor);
	}
?>