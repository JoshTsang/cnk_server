<?php
function printTitle($socket, $str) {
	socket_write($socket,"\x1b\x21\x0");
	//socket_write($socket, "\x1b\x4c");
	$print = iconv("UTF-8","GB18030", $str);
	socket_write($socket, $print);
	socket_write($socket, "\r\n");
}

function printHeader($socket, $table, $timestamp) {
	printl($socket, "                 百姓鲜榨汁\r\n");
	$print = sprintf("桌号:%-4d                  %s", $table, $timestamp);
	printl($socket, $print);
	printl($socket, "----------------------------------------------");
	printl($socket, "品名                       单价  数量    小计");
}

function printFooter($socket, $total) {
	$print = sprintf("----------------------------------------------\r\n".
					 "合计:%34.2f\r\n".
					 "----------------------------------------------\r\n".
					 "\r\n               谢谢惠顾!               \r\n \r\n ", $total);
	printl($socket, $print);
	//socket_write($socket, "FF");
}

function printl($socket, $str) {
	$print = iconv("UTF-8","GB18030", $str);
	socket_write($socket, $print);
	socket_write($socket, "\r\n");
}

function printR($socket, $str) {
	socket_write($socket, $str);
}

function printerStatus($printerIp) {
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
	
	set_time_limit(0);
	ob_implicit_flush();
	$ret = socket_write($socket, "\x10\x4\x1");
	if ($ret <= 0) {
		echo socket_strerror(socket_last_error())."\n";
		die("failed to write printer.ip:$printerIP");
		return -1;
	}
	//printl($socket, $print);
	//$ret = socket_set_timeout($stream, $seconds, $microseconds)
	$ret = socket_recv($socket, $buf, 1024, MSG_DONTWAIT);
	if (!$ret) {
		echo socket_strerror($socket_last_error);
	}
	socket_close($socket);
	return sprintf("%d",$ret);
}

function printc($socket, $str) {
	// if (strlen($str)>15) {
		// $str = substr($str, 0, 15);
	// }
	$print = iconv("UTF-8","GB18030", $str);
	socket_write($socket, $print);
	socket_write($socket, "\x09");
	//socket_write($socket, "\r\n");
}

function printOrder($socket, $tableId, $timestamp, $obj, $total) {
	printHeader($socket, $tableId, $timestamp);
	
	$dishCount = count($obj->order);
	$total = 0;
	for ($i=0; $i<$dishCount; $i++) {
		$dishId = $obj->order[$i]->id;
		$price = $obj->order[$i]->price;
		$dishQuantity = $obj->order[$i]->quan;
		$dishName = $obj->order[$i]->name;
		$total += $price * $dishQuantity;
		//socket_write($socket, "\x1b\x44\x0F\x00\n");
		//printc($socket, $dishName);
		$zhLen = (strlen($dishName) - iconv_strlen($dishName, "UTF-8"))/2;
		$enLen = iconv_strlen($dishName, "UTF-8") - $zhLen;
		$dishNameSpace = $zhLen*2 + $enLen;
		if ($dishNameSpace > 12) {
			$printString = sprintf("%s\n%24s%7.2f%6d%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
		} else {
			$spaceLen = 24 - $dishNameSpace;
			$printString = sprintf("%s%$spaceLen"."s%7.2f%6d%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
		}
		printl($socket, $printString);
	}
	printFooter($socket, $total);
}

function printJson($print) {
	// $ret = printerStatus(PRINTER_FOR_KITCHEN);
	// if ($ret <= 0) {
		// die("Can't get priter status");
	// }
	//printReceipt($print, PRINTER_FOE_CHECKEOUT, "客户联");
	printReceipt($print, PRINTER_FOR_KITCHEN, "存根联");
}

function printReceipt($json, $printerIP, $title) {
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
	$json_string = $json;
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$tableName = $obj->tableName;
	$timestamp = $obj->timestamp;
	$total = 0;
	
	if ($dishCount <= 0) {
		header("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
		exit();
	}
	printTitle($socket, "$title\r\n");
	printOrder($socket, $tableName, $timestamp, $obj, $total);
	//printR($socket, "\x1D\x56\x42\5\n");
	printR($socket, "\x1B\x43\1\x13\3\n");
	socket_close($socket);
}

function printJsonDel($print) {
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
	$connection = socket_connect($socket, PRINTER_FOR_KITCHEN, 9100); 
	
	$json_string = $print;
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$timestamp = $obj->timestamp;
	$total = 0;
	
	if ($dishCount <= 0) {
		header("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
		exit();
	}
	printTitle($socket, "存根联 (删除）\r\n");
	printOrder($socket, $tableId, $timestamp, $obj, $total);
	
	//TODO print 1 copy for debug
	exit(0);
	printTitle($socket, "客户联\r\n");
	printOrder($socket, $tableId, $timestamp, $obj, $total);
	socket_close($socket);
}
?>