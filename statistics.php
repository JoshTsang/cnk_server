<?php
	require('macros.php');
	require('print.php');
	
	if(!isset($_POST['json'])) {
		exit();
	}
	
	function printSalesHeader($socket, $timeStart, $timeEnd) {
		$print = sprintf("开始:%s\r\n结束:%s", $timeStart, $timeEnd);
		printl($socket, $print);
		printl($socket, "-------------------------------");
	}
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
	$connection = socket_connect($socket,'192.168.1.8', 9100); 
	
	$json_string = $_POST['json'];
	//$json_string = "{\"total\":3972,\"rows\":[{\"amount\":348,\"count\":29,\"percentage\":0.08761329305135952,\"name\":\"回锅肉\"},{\"amount\":372,\"count\":31,\"percentage\":0.09365558912386707,\"name\":\"红烧肉\"}],\"timeEnd\":\"2012-07-02 22:19\",\"timeStart\":\"2012-06-28 12:59\"}";
	$obj = json_decode($json_string); 
	$rowCount = count($obj->rows);
	$amount = $obj->amount;
	$timestart = $obj->timeStart;
	$timeend = $obj->timeEnd;
	
	printTitle($socket, "销售统计\r\n");
	printSalesHeader($socket, $tableId, $timestamp);
	
	if ($rowCount <= 0) {
		header("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
		exit();
	}
	
	for ($i=0; $i<$rowCount; $i++) {
		$dishName = $obj->rows[$i]->name;
		$amount = $obj->rows[$i]->amount;
		$count = $obj->rows[$i]->count;
		$percentage = $obj->rows[$i]->percentage;
		$printString = sprintf("%s\r\n%6d%16.2f%8.2f%%", $dishName, $count, $amount, $percentage*100);
		printl($socket, $printString);
	}
	printFooter($socket, $total);
?>