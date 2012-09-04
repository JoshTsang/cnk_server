<?php
	require 'file.php';
	define('PRINTER_COMMAND_ALARM', "\x1B\x43\1\x13\3\n");
	define('PRINTER_COMMAND_CUT', "\x1D\x56\x42\5\n");
	
	class printer {
		private $printerInfo;
		
		function __construct($param) {
			$file = new file($param);
			$this->printerInfo = json_decode($file->getContent());
		}
		
		public function printOrder($print) {
			$count = count($this->printerInfo);
			for ($i=0; $i<$count; $i++) {
				if($this->printerInfo[$i]->usefor <= PRINT_ORDER) {
					$this->printOrderReceipt($print, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title, $this->printerInfo[$i]->type);
				}
			}
		}
		
		public function printDel($print) {
			$count = count($this->printerInfo);
			for ($i=0; $i<$count; $i++) {
				if($this->printerInfo[$i]->usefor <= PRINT_ORDER) {
					$this->printDelReceipt($print, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title, $this->printerInfo[$i]->type);
				}
			}
		}
		
		public function printSales($json) {
			$count = count($this->printerInfo);
			for ($i=0; $i<$count; $i++) {
				if($this->printerInfo[$i]->usefor == PRINT_STATISTICS) {
					$this->printSalesReceipt($json, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->type);
				}
			}
		}
		
		private function printTitle($socket, $str) {
			socket_write($socket,"\x1b\x21\x0");
			//socket_write($socket, "\x1b\x4c");
			$print = iconv("UTF-8","GB18030", $str);
			socket_write($socket, $print);
			socket_write($socket, "\r\n");
		}
		
		private function printHeader($socket, $table, $timestamp, $printerType) {
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
				$this->printl($socket, $print);
				$print = sprintf("桌号:%-4d                  %s", $table, $timestamp);
				$this->printl($socket, $print);
				$this->printl($socket, "----------------------------------------------");
				$this->printl($socket, "品名                       单价  数量    小计");
			} else if ($printerType == PRINTER_TYPE_58) {
				$spaceLen = (34 - $space)/2;
				$print = sprintf("%".$spaceLen."s%s\r\n", "", $shopname);
				$this->printl($socket, $print);
				$print = sprintf("桌号:%-4d   %s", $table, $timestamp);
				$this->printl($socket, $print);
				$this->printl($socket, "--------------------------------");
				$this->printl($socket, "品名          单价  数量    小计");
			}
		}

		private function printFooter($socket, $total, $printerType) {
			if ($printerType == PRINTER_TYPE_80) {
				$print = sprintf("----------------------------------------------\r\n".
								 "合计:%40.2f\r\n".
								 "----------------------------------------------\r\n".
								 "\r\n               谢谢惠顾!               \r\n \r\n ", $total);
				$this->printl($socket, $print);
			} else if ($printerType == PRINTER_TYPE_58) {
				$print = sprintf("--------------------------------\r\n".
								 "合计:%27.2f\r\n".
								 "--------------------------------\r\n".
								 "\r\n           谢谢惠顾!          \r\n \r\n ", $total);
				$this->printl($socket, $print);
			}
			//socket_write($socket, "FF");
		}
	
		private function printl($socket, $str) {
			$print = iconv("UTF-8","GB18030", $str);
			socket_write($socket, $print);
			socket_write($socket, "\r\n");
		}
	
		private function printR($socket, $str) {
			socket_write($socket, $str);
		}
		
		private function printc($socket, $str) {
			$print = iconv("UTF-8","GB18030", $str);
			socket_write($socket, $print);
			socket_write($socket, "\x09");
		}

		private function printOrderedDishes($socket, $tableId, $timestamp, $obj, $total, $printerType) {
			$this->printHeader($socket, $tableId, $timestamp, $printerType);
			
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
				$this->printl($socket, $printString);
			}
			$this->printFooter($socket, $total, $printerType);
		}

		
		
		private function printOrderReceipt($json, $printerIP, $title, $printerType) {
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
				die("NO_ORDERED_DISH 'NO_ORDERED_DISH'");
			}
			$this->printTitle($socket, "$title\r\n");
			$this->printOrderedDishes($socket, $tableName, $timestamp, $obj, $total, $printerType);
			$this->printR($socket, PRINTER_COMMAND_CUT);
			$this->printR($socket, PRINTER_COMMAND_ALARM);
			socket_close($socket);
		}
		
		private function printDelReceipt($json, $printerIP, $title, $printerType) {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
			$connection = socket_connect($socket, $printerIP, 9100); 
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
			$this->printTitle($socket, $title."(删除)");
			$this->printOrderedDishes($socket, $tableId, $timestamp, $obj, $total, $printerType);
			
			$this->printR($socket, PRINTER_COMMAND_CUT);
			$this->printR($socket, PRINTER_COMMAND_ALARM);
			socket_close($socket);
		}
		
		private function printSalesHeader($socket, $timeStart, $timeEnd, $printerType) {
			if($printerType == PRINTER_TYPE_80) {
				$print = sprintf("开始时间:    %s\r\n结束时间:    %s", $timeStart, $timeEnd);
				$this->printl($socket, $print);
				$this->printl($socket, "----------------------------------------------");
				$this->printl($socket, "商品名称         数量       销售额  销售百分比");
			} else if($printerType == PRINTER_TYPE_58) {
				$print = sprintf("开始时间:    %s\r\n结束时间:    %s", $timeStart, $timeEnd);
				$this->printl($socket, $print);
				$this->printl($socket, "-------------------------------");
				$this->printl($socket, " 数量        销售额   销售百分比");
			}
		}
		
		private function printSalesData($obj, $socket, $printerType) {
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
				$this->printl($socket, $printString);
			}
		}
		
		private function printSalesReceipt($json, $printerIP, $printerType) {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
			$connection = socket_connect($socket, $printerIP, 9100); 
			
			$obj = json_decode($json);
			$rowCount = count($obj->rows);
			$total = $obj->total;
			$timestart = $obj->timeStart;
			$timeend = $obj->timeEnd;
			
			if ($rowCount <= 0) {
				die("NO_ORDERED_DISH 'NO_ORDERED_DISH'");
			}
			
			$this->printTitle($socket, "销售统计\r\n");
			$this->printSalesHeader($socket, $timestart, $timeend, $printerType);
			$this->printSalesData($obj, $socket, $printerType);
			$this->printFooter($socket, $total, $printerType);
			
			$this->printR($socket, PRINTER_COMMAND_CUT);
			$this->printR($socket, PRINTER_COMMAND_ALARM);
		}
	}
?>