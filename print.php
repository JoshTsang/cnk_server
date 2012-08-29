<?php

define('PRINTER_COMMAND_ALARM', "\x1B\x43\1\x13\3\n");
define('PRINTER_COMMAND_CUT', "\x1D\x56\x42\5\n");

function printTitle($socket, $str) {
	socket_write($socket,"\x1b\x21\x0");
	//socket_write($socket, "\x1b\x4c");
	$print = iconv("UTF-8","GB18030", $str);
	socket_write($socket, $print);
	socket_write($socket, "\r\n");
}

function printHeader($socket, $table, $timestamp, $printerType) {
	if(file_exists("setting/shopname")) {
		$shopname = file_get_contents("setting/shopname");
	} else {
		$shopname = "菜脑壳电子点菜系统";
	}
	$zhLen = (strlen($shopname) - iconv_strlen($shopname, "UTF-8"))/2;
	$enLen = iconv_strlen($shopname, "UTF-8") - $zhLen;
	$space = $zhLen*2 + $enLen;
	
	if ($printerType == PRINTER_TYPE_80) {
		$spaceLen = (48 - $space)/2;
		$print = sprintf("%".$spaceLen."s%s\r\n", "", $shopname);
		printl($socket, $print);
		$print = sprintf("桌号:%-4d                  %s", $table, $timestamp);
		printl($socket, $print);
		printl($socket, "----------------------------------------------");
		printl($socket, "品名                       单价  数量    小计");
	} else if ($printerType == PRINTER_TYPE_58) {
		$spaceLen = (34 - $space)/2;
		$print = sprintf("%".$spaceLen."s%s\r\n", "", $shopname);
		printl($socket, $print);
		$print = sprintf("桌号:%-4d   %s", $table, $timestamp);
		printl($socket, $print);
		printl($socket, "--------------------------------");
		printl($socket, "品名          单价  数量    小计");
	}
}

function printFooter($socket, $total, $printerType) {
	if ($printerType == PRINTER_TYPE_80) {
		$print = sprintf("----------------------------------------------\r\n".
						 "合计:%34.2f\r\n".
						 "----------------------------------------------\r\n".
						 "\r\n               谢谢惠顾!               \r\n \r\n ", $total);
		printl($socket, $print);
	} else if ($printerType == PRINTER_TYPE_58) {
		$print = sprintf("--------------------------------\r\n".
						 "合计:%27.2f\r\n".
						 "--------------------------------\r\n".
						 "\r\n           谢谢惠顾!          \r\n \r\n ", $total);
		printl($socket, $print);
	}
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

function printOrder($socket, $tableId, $timestamp, $obj, $total, $printerType) {
	printHeader($socket, $tableId, $timestamp, $printerType);
	
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
		if ($printerType == PRINTER_TYPE_80) {
			if ($dishNameSpace > 24) {
				$printString = sprintf("%s\n%24s%7.2f%6d%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
			} else {
				$spaceLen = 24 - $dishNameSpace;
				$printString = sprintf("%s%$spaceLen"."s%7.2f%6d%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
			}
		} else if($printerType == PRINTER_TYPE_58) {
			if ($dishNameSpace > 12) {
				$printString = sprintf("%s\n%12s%6.2f%6d%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
			} else {
				$spaceLen = 12 - $dishNameSpace;
				$printString = sprintf("%s%$spaceLen"."s%6.2f%6d%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
			}
		}
		printl($socket, $printString);
	}
	printFooter($socket, $total, $printerType);
}

function printJson($print) {
	$jsonObj = getPrinterInfo();
	$count = count($jsonObj);
	for ($i=0; $i<$count; $i++) {
		if($jsonObj[$i]->usefor <= PRINT_ORDER) {
			printReceipt($print, $jsonObj[$i]->ip, $jsonObj[$i]->title, $jsonObj[$i]->type);
		}
	}
}

function printReceipt($json, $printerIP, $title, $printerType) {
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
	printOrder($socket, $tableName, $timestamp, $obj, $total, $printerType);
	printR($socket, PRINTER_COMMAND_CUT);
	printR($socket, PRINTER_COMMAND_ALARM);
	socket_close($socket);
}

//TODO 
function printJsonDel($print) {
	printDelReceipt($print, PRINTER_FOR_KITCHEN, "存根联 (删除）\r\n", PRINTER_TYPE_58);
}

function printDelReceipt($json, $printerIP, $title, $printerType) {
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
	$connection = socket_connect($socket, PRINTER_FOR_KITCHEN, 9100); 
	
	$json_string = $json;
	$obj = json_decode($json_string); 
	$dishCount = count($obj->order);
	$tableId = $obj->tableId;
	$timestamp = $obj->timestamp;
	$total = 0;
	
	if ($dishCount <= 0) {
		header("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
		exit();
	}
	printTitle($socket, $title);
	printOrder($socket, $tableId, $timestamp, $obj, $total, $printerType);
	
	socket_close($socket);
}

function printSalesHeader($socket, $timeStart, $timeEnd, $printerType) {
	if($printerType == PRINTER_TYPE_80) {
		$print = sprintf("开始时间:    %s\r\n结束时间:    %s", $timeStart, $timeEnd);
		printl($socket, $print);
		printl($socket, "----------------------------------------------");
		printl($socket, "商品名称         数量       销售额  销售百分比");
	} else if($printerType == PRINTER_TYPE_58) {
		$print = sprintf("开始时间:    %s\r\n结束时间:    %s", $timeStart, $timeEnd);
		printl($socket, $print);
		printl($socket, "-------------------------------");
		printl($socket, " 数量        销售额   销售百分比");
	}
}

function printSalesData($obj, $socket, $printerType) {
	$rowCount = count($obj->rows);
	for ($i=0; $i<$rowCount; $i++) {
		$dishName = $obj->rows[$i]->name;
		$amount = $obj->rows[$i]->amount;
		$count = $obj->rows[$i]->count;
		$percentage = $obj->rows[$i]->percentage;
		$zhLen = (strlen($dishName) - iconv_strlen($dishName, "UTF-8"))/2;
		$enLen = iconv_strlen($dishName, "UTF-8") - $zhLen;
		$dishNameSpace = $zhLen*2 + $enLen;
		if ($printerType == PRINTER_TYPE_80) {
			if ($dishNameSpace > 14) {
				$printString = sprintf("%s\n%14s%7d%13.2f%10.2f%%",$dishName, "", $count, $amount, $percentage*100);
			} else {
				$spaceLen = 14 - $dishNameSpace;
				$printString = sprintf("%s%$spaceLen"."s%7d%13.2f%10.2f%%",$dishName, "", $count, $amount, $percentage*100);
			}
		} else if($printerType == PRINTER_TYPE_58) {
			$printString = sprintf("%s\r\n%6d%14.2f%10.2f%%", $dishName, $count, $amount, $percentage*100);
		}
		printl($socket, $printString);
		
	}
}

function printSalesReceipt($json, $printerIP, $printerType) {
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
	$connection = socket_connect($socket, PRINTER_FOR_KITCHEN, 9100); 
	
	$json_string = $json;
	//$json_string = "{\"total\":3972,\"rows\":[{\"amount\":348,\"count\":29,\"percentage\":0.08761329305135952,\"name\":\"回锅肉\"},{\"amount\":372,\"count\":31,\"percentage\":0.09365558912386707,\"name\":\"红烧肉\"}],\"timeEnd\":\"2012-07-02 22:19\",\"timeStart\":\"2012-06-28 12:59\"}";
	$obj = json_decode($json_string); 
	$rowCount = count($obj->rows);
	$total = $obj->total;
	$timestart = $obj->timeStart;
	$timeend = $obj->timeEnd;
	
	if ($rowCount <= 0) {
		header("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
		exit();
	}
	
	printTitle($socket, "销售统计\r\n");
	printSalesHeader($socket, $timestart, $timeend, $printerType);
	printSalesData($obj, $socket, $printerType);
	printFooter($socket, $total, $printerType);
	printl($socket, "**end-printSalesRectipt");
}

function getPrinterInfo() {
	$file =  "setting/printerInfo.json";
 
	if(file_exists($file)) {
		$json = file_get_contents($file);
	} else {
		$timestamp = "";
	return null;
	}
	return json_decode($json);
}

?>
