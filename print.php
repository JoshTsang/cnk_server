<?php
function printTitle($socket, $str) {
	socket_write($socket,"\x1b\x21\x0");
	
	$print = iconv("UTF-8","GB2312", $str);
	socket_write($socket, $print);
	socket_write($socket, "\r\n");
}

function printHeader($socket, $table, $timestamp) {
	printl($socket, "            xxx餐饮店                               ");
	$print = sprintf("桌号:%-4d    %s", $table, $timestamp);
	printl($socket, $print);
	printl($socket, "-------------------------------");
	printl($socket, "品名         单价  数量   小计");
}

function printFooter($socket, $total) {
	$print = sprintf("-------------------------------\r\n".
					 "合计:%24.2f\r\n".
					 "-------------------------------\r\n".
					 "\r\n          谢谢惠顾!            \r\n \r\n ".
					 "-------------------------------\r\n", $total);
	
	printl($socket, $print);
}

function printl($socket, $str) {
	$print = iconv("UTF-8","GB2312", $str);
	socket_write($socket, $print);
	socket_write($socket, "\r\n");
}

function printc($socket, $str) {
	if (strlen($str)>15) {
		$str = substr($str, 0, 15);
	}
	$print = iconv("UTF-8","GB2312", $str);
	socket_write($socket, $print);
	socket_write($socket, "\x09");
}

function printJson($print) {
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
	$connection = socket_connect($socket,'192.168.1.8', 9100); 
	
	$json_string = $print;
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$timestamp = $obj->timestamp;
	$total = 0;
	
	printTitle($socket, "存根联\r\n");
	printHeader($socket, $tableId, $timestamp);
	
	if ($dishCount <= 0) {
		header("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
		exit();
	}
	/*
	for ($i=0; $i<$dishCount; $i++) {
		$dishId = $obj->order[$i]->id;
		$price = $obj->order[$i]->price;
		$dishQuantity = $obj->order[$i]->quan;
		$dishName = $obj->order[$i]->name;
		$total += $price * $dishQuantity;
		$printString = sprintf("%s|%6.2f|%3d|%6.2f", $dishName, $price, $dishQuantity, $price*$dishQuantity);
		printl($socket, $printString);
	}
	*/
	for ($i=0; $i<$dishCount; $i++) {
		$dishId = $obj->order[$i]->id;
		$price = $obj->order[$i]->price;
		$dishQuantity = $obj->order[$i]->quan;
		$dishName = $obj->order[$i]->name;
		$total += $price * $dishQuantity;
		socket_write($socket, "\x1b\x44\x0b\x12\x00");
		printc($socket, $dishName);
		$printString = sprintf("%6.2f%4d% 10.2f", $price, $dishQuantity, $price*$dishQuantity);
		printl($socket, $printString);
	}
	printFooter($socket, $total);
	
	//TODO print 1 copy for debug
	exit(0);
	printTitle($socket, "客户联\r\n");
	printHeader($socket, $tableId, $timestamp);
	
	if ($dishCount <= 0) {
		header("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
		exit();
	}
	/*
	for ($i=0; $i<$dishCount; $i++) {
		$dishId = $obj->order[$i]->id;
		$price = $obj->order[$i]->price;
		$dishQuantity = $obj->order[$i]->quan;
		$dishName = $obj->order[$i]->name;
		$total += $price * $dishQuantity;
		$printString = sprintf("%s|%6.2f|%3d|%6.2f", $dishName, $price, $dishQuantity, $price*$dishQuantity);
		printl($socket, $printString);
	}
	*/
	$total = 0;
	for ($i=0; $i<$dishCount; $i++) {
		$dishId = $obj->order[$i]->id;
		$price = $obj->order[$i]->price;
		$dishQuantity = $obj->order[$i]->quan;
		$dishName = $obj->order[$i]->name;
		$total += $price * $dishQuantity;
		socket_write($socket, "\x1b\x44\x0b\x12\x00");
		printc($socket, $dishName);
		$printString = sprintf("%6.2f%4d% 10.2f", $price, $dishQuantity, $price*$dishQuantity);
		printl($socket, $printString);
	}
	printFooter($socket, $total);
}
?>