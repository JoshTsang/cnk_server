<?php
    if(isset($_GET['IP'])) {
    	die("parameter needed");
    }
	
	$printerIp = $_GET['IP'];
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket < 0)
	{
		echo socket_strerror(socket_last_error())."\n";
		die("Unable to connect printer.ip:$printerIP");
	}
	
	$connection = socket_connect($socket, $printerIp, 9100);
	if (!$connection) {
		echo socket_strerror(socket_last_error())."\n";
		die("Unable to connect printer.ip:$printerIP");
	}
?>