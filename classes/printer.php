<?php
	require 'file.php';
	define('PRINTER_COMMAND_ALARM', "\x1B\x43\1\x13\3\n");
	define('PRINTER_COMMAND_CUT', "\x1D\x56\x42\5\n");
	define('PRINTER_COMMAND_2X', "\x1D\x21\x11");
	define('PRINTER_COMMAND_1X', "\x1D\x21\x01");
	define('PRINTER_OPEN_CASHIER', "\x10\x14\1\0\10");
	
	class printer {
		private $printerInfo;
		
		function __construct($param) {
			$file = new file($param);
			$this->printerInfo = json_decode($file->getContent());
			// $obj = json_decode($file->getContent());
			// $count = count($obj);
			// for ($i=0; $i<$count; $i++) {
				// $this->addPrinter($obj[$i]->ip, $obj[$i]->title, $obj[$i]->type, $obj[$i]->usefor);
			// }
			// print_r($this->printerInfo);
		}
		
		private function addPrinter($ip, $title, $type, $usefor) {
			if ($usefor < PRINT_ORDER) {
				$count = count($this->printerInfo);
				for ($i=0; $i<$count; $i++) {
					if ($this->printerInfo[$i]->ip == $ip) {
						$this->updateKitchenPrinter($i, $usefor);
						break;
					}
				}
				if ($i == $count) {
					$this->addKitchenPrinter($ip, $title, $type, $usefor);
				}
			} else {
				$this->addNewPrinter($ip, $title, $type, $usefor);
			}
		}
		
		private function addNewPrinter($ip, $title, $type, $usefor) {
			$count = count($this->printerInfo);
			$this->printerInfo[$count] = array('ip' => $ip,
											   'title' => $title,
											   'type' => $type,
											   'usefor' => $usefor );
		}
		
		private function addKitchenPrinter($ip, $title, $type, $usefor) {
			$count = count($this->printerInfo);
			$this->printerInfo[$count] = array('ip' => $ip,
											   'title' => $title,
											   'type' => $type,
											   'usefor' => PRINT_KITCHEN,
											   'categories' => $usefor );
		}
		
		private function updateKitchenPrinter($index, $usefor) {
			$this->printerInfo[$index]->categories = $this->printerInfo[$index]->categories."," .$usefor;
			}

			public function printOrder($print, $orderId) {
			$count = count($this->printerInfo);
			for ($i=0; $i<$count; $i++) {
				if($this->printerInfo[$i]->usefor == PRINT_CASHIER) {
					$this->printOrderReceipt($print, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title, 
								$this->printerInfo[$i]->type, $orderId);
				} else if ($this->printerInfo[$i]->usefor == PRINT_KITCHEN) {
					$this->printOrderReceiptCategory($print, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title,
							$this->printerInfo[$i]->type, $orderId, $this->printerInfo[$i]->id);
				}
				//TODO kitchen
				
			}
		}
		
		public function printDel($print) {
			$count = count($this->printerInfo);
			for ($i=0; $i<$count; $i++) {
				if($this->printerInfo[$i]->usefor == PRINT_CASHIER) {
					$this->printDelReceipt($print, $this->printerInfo[$i]->ip, 
							$this->printerInfo[$i]->title, $this->printerInfo[$i]->type);
				} else if ($this->printerInfo[$i]->usefor == PRINT_KITCHEN) {
					$this->printDelReceiptCategory($print, $this->printerInfo[$i]->ip,
					 		$this->printerInfo[$i]->title, $this->printerInfo[$i]->type, $this->printerInfo[$i]->id);
				}
				//TODO kitchen
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
		
		public function printChangeTable($print) {
			$count = count($this->printerInfo);
			for ($i=0; $i<$count; $i++) {
				if($this->printerInfo[$i]->usefor == PRINT_CASHIER) {
					$this->printChangeTableReceipt($print, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title, $this->printerInfo[$i]->type);
				}
				//TODO kitchen
			}
		}
		
		public function printCombine($json) {
			$count = count($this->printerInfo);
			for ($i=0; $i<$count; $i++) {
				if($this->printerInfo[$i]->usefor == PRINT_CASHIER) {
					$this->printCombineReceipt($json, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title, $this->printerInfo[$i]->type);
				}
				//TODO kitchen
			}
		}
		
		private function printCombineReceipt($json, $printerIP, $title, $printerType) {
			$db = new CNK_DB();
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
			
			if ($dishCount <= 0) {
				die("NO_ORDERED_DISH 'NO_ORDERED_DISH'");
			}
			$this->printTitle($socket, $title, "(并台)");
			//TODO orderId
			$orderId = $db->getOrderIds($obj->tableId);
			$this->printOrderedDishes($socket, $obj, $printerType, $orderId);
			socket_close($socket);
		}

		private function printCombineSubtitle($socket, $str) {
			$this->printl($socket, $str." 桌：");
		}
		
		public function printCheckout($json) {
			$count = count($this->printerInfo);
			for ($i=0; $i<$count; $i++) {
				if($this->printerInfo[$i]->usefor == PRINT_ORDER) {
					$this->printChecktoutReceipt($json, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title, $this->printerInfo[$i]->type);
				}
			}
		}
		
		private function printTitle($socket, $title, $subTitle) {
			$print = iconv("UTF-8","GB18030", $title.$subTitle);
			if (isset($subTitle)) {
				socket_write($socket, PRINTER_COMMAND_2X);
				socket_write($socket, $print);
				socket_write($socket, "\r\n\r\n");
				socket_write($socket, PRINTER_COMMAND_1X);
			} else {
				socket_write($socket,PRINTER_COMMAND_1X);
				socket_write($socket, $print);
				socket_write($socket, "\r\n\r\n");
			}
			
		}
		
		private function printHeader($socket, $orderId, $table, $waiter, $persons, $timestamp, $printerType) {
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
				$this->printOrderId($socket, $orderId);
				$print = sprintf("桌号:%-4s                %s", $table, $timestamp);
				$this->printl($socket, $print);
				if ($persons == 0) {
					$print = sprintf("人数:未设置           服务员：%s", $waiter);
				} else {
					$print = sprintf("人数:%-4s              服务员：%s", $persons, $waiter);
				}
				$this->printl($socket, $print);
				$this->printl($socket, "----------------------------------------------");
				$this->printl($socket, "品名                       单价  数量    小计");
			} else if ($printerType == PRINTER_TYPE_58) {
				$spaceLen = (34 - $space)/2;
				$print = sprintf("%".$spaceLen."s%s\r\n", "", $shopname);
				$this->printl($socket, $print);
				$this->printOrderId($socket, $orderId);
				$print = sprintf("桌号:%-4s   %s", $table, $timestamp);
				$this->printl($socket, $print);
				if ($persons == 0) {
					$print = sprintf("人数:%-4s   服务员：%s", "未设置", $waiter);
				} else {
					$print = sprintf("人数:%-4s   服务员：%s", $persons, $waiter);
				}
				$this->printl($socket, $print);
				$this->printl($socket, "--------------------------------");
				$this->printl($socket, "品名          单价  数量    小计");
			}
		}

		private function printOrderId($socket, $orderId) {
			if (isset($orderId)) {
				$orders = implode(",", $orderId);
				$print = sprintf("%s", "流水号:".$orders);
				$this->printl($socket, $print);
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
			
			$this->printR($socket, PRINTER_COMMAND_CUT);
			$this->printR($socket, PRINTER_COMMAND_ALARM);
		}
	
		private function printl($socket, $str) {
			$print = iconv("UTF-8","GB18030", $str);
			socket_write($socket, $print);
			socket_write($socket, "\r\n");
		}
	
		private function printR($socket, $str) {
			socket_write($socket, $str);
		}
		
		private function printComment($socket, $commnet, $printerType) {
			if (isset($comment)) {
				if ($printerType == PRINTER_TYPE_80) {
					$this->printl($socket, "**********************************************");
				} else {
					$this->printl($socket, "********************************");
				}
				$this->printl($socket, "#备注:".$comment);
			}
		}

		private function printOrderedDishes($socket, $obj, $printerType, $orderId) {
			$tableId = $obj->tableId;
			$tableName = $obj->tableName;
			$timestamp = $obj->timestamp;
			$waiter = $obj->waiter;
			$persons = $obj->persons;
			
			$this->printHeader($socket, $orderId, $tableName, $waiter, $persons, $timestamp, $printerType);
			
			$total = $this->printDishes($socket, $obj, $printerType);
			$this->printComment($socket, $obj->commnet, $printerType);		
			$this->printFooter($socket, $total, $printerType);
		}

		private function printOrderedDishesByPrinterId($socket, $obj, $printerType, $orderId, $printerId) {
			$tableId = $obj->tableId;
			$tableName = $obj->tableName;
			$timestamp = $obj->timestamp;
			$waiter = $obj->waiter;
			$persons = $obj->persons;
			
			$this->printHeader($socket, $orderId, $tableName, $waiter, $persons, $timestamp, $printerType);
			
			$total = $this->printDishesByPrinterId($socket, $obj, $printerType, $printerId);
			$this->printComment($socket, $obj->commnet, $printerType);
			$this->printFooter($socket, $total, $printerType);
		}
		
		private function printChecktoutReceipt($json, $printerIP, $title, $printerType) {
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
			$checkout = json_decode($json_string);
			$objAll = json_decode($checkout->orderAll); 
			$count = count($objAll);
			if ($count <= 0) {
				die("[MORE TABLE INFO NEED]");
				return 0;
			}
			$obj = json_decode($objAll[0]);
			$waiter = $checkout->waiter;
			
			$this->printTitle($socket, $title, NULL);
			$this->printHeader($socket, null, $checkout->tableName, $waiter, "结账", $timestamp, $printerType);
			
			
			for ($i=0; $i<$count; $i++) {
				$obj = json_decode($objAll[$i]);
				$this->printCheckoutSubtitle($socket, $obj->tableName);
				$subTableTotal[$i] = $this->printDishes($socket, $obj, $printerType);
				$tableName[$i] = $obj->tableName;
			}
			
			$this->printCheckoutFooter($socket, $subTableTotal, $tableName, $checkout->income, $checkout->change, $i, $printerType);
			
			socket_close($socket);
		}
		
		private function printCheckoutSubtitle($socket, $str) {
			$this->printl($socket, $str." 桌：");
		}

		private function printCheckoutFooter($socket, $subTableTotal, $tableName, $income, $change, $tableCount, $printerType) {
			$total = 0;
			if ($printerType == PRINTER_TYPE_80) {
				$this->printl($socket, "----------------------------------------------");
				for ($i=0; $i<$tableCount; $i++) {
					$name = " ".$tableName[$i]." 桌：";
					$zhLen = (strlen($name) - iconv_strlen($name, "UTF-8"))/2;
					$enLen = iconv_strlen($name, "UTF-8") - $zhLen;
					$dishNameSpace = $zhLen*2 + $enLen;
					$spaceLen = 24 - $dishNameSpace;
					$printString = sprintf("%s%$spaceLen"."s%21.2f",$name, "", $subTableTotal[$i]);
					$this->printl($socket, $printString);
					$total += $subTableTotal[$i];
				}
				$print = sprintf("合计:%40.2f\r\n".
								 "实收:%40.2f\r\n".
								 "找零:%40.2f\r\n".
								 "----------------------------------------------\r\n".
								 "\r\n               谢谢惠顾!               \r\n \r\n ", $total, $income, $change);
				$this->printl($socket, $print);
			} else if ($printerType == PRINTER_TYPE_58) {
				$this->printl($socket, "--------------------------------");
				for ($i=0; $i<$tableCount; $i++) {
					$name = " ".$tableName[$i]." 桌：";
					$zhLen = (strlen($name) - iconv_strlen($name, "UTF-8"))/2;
					$enLen = iconv_strlen($name, "UTF-8") - $zhLen;
					$dishNameSpace = $zhLen*2 + $enLen;
					$spaceLen = 12 - $dishNameSpace;
					$printString = sprintf("%s%$spaceLen"."s%20.2f",$name, "", $subTableTotal[$i]);
					$this->printl($socket, $printString);
					$total += $subTableTotal[$i];
				}
				$print = sprintf("合计:%27.2f\r\n".
								 "实收:%27.2f\r\n".
								 "找零:%27.2f\r\n".
								 "--------------------------------\r\n".
								 "\r\n           谢谢惠顾!          \r\n \r\n ", $total, $income, $change);
				$this->printl($socket, $print);
			}
			$this->printR($socket, PRINTER_COMMAND_CUT);
			$this->printR($socket, PRINTER_COMMAND_ALARM);
			$this->printR($socket, PRINTER_OPEN_CASHIER);
		}
		
		private function printDishes($socket, $obj, $printerType) {
			$dishCount = count($obj->order);
			$total = 0;
			for ($i=0; $i<$dishCount; $i++) {
				$dishId = $obj->order[$i]->dishId;
				$price = $obj->order[$i]->price;
				$dishQuantity = $obj->order[$i]->quan;
				$dishName = $obj->order[$i]->name;
				$total += $price * $dishQuantity;
				$zhLen = (strlen($dishName) - iconv_strlen($dishName, "UTF-8"))/2;
				$enLen = iconv_strlen($dishName, "UTF-8") - $zhLen;
				$dishNameSpace = $zhLen*2 + $enLen;
				if ($printerType == PRINTER_TYPE_80) {
					if ($dishNameSpace > 24) {
						$printString = sprintf("%s\n%24s%7.2f%6.2f%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
					} else {
						$spaceLen = 24 - $dishNameSpace;
						$printString = sprintf("%s%$spaceLen"."s%7.2f%6.2f%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
					}
				} else if($printerType == PRINTER_TYPE_58) {
					if ($dishNameSpace > 12) {
						$printString = sprintf("%s\n%12s%6.2f%6.2f%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
					} else {
						$spaceLen = 12 - $dishNameSpace;
						$printString = sprintf("%s%$spaceLen"."s%6.2f%6.2f%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
					}
				}
				$this->printl($socket, $printString);
				if (isset($obj->order[$i]->flavor)) {
					$this->printl($socket, "*口味：".$obj->order[$i]->flavor);
				}
			}
			return $total;
		}
		
		private function printDishesByPrinterId($socket, $obj, $printerType, $printerId) {
			$dishCount = count($obj->order);
			$total = 0;
			for ($i=0; $i<$dishCount; $i++) {
				if ($obj->order[$i]->printer == $printerId) {
					$dishId = $obj->order[$i]->dishId;
					$price = $obj->order[$i]->price;
					$dishQuantity = $obj->order[$i]->quan;
					$dishName = $obj->order[$i]->name;
					$total += $price * $dishQuantity;
					$zhLen = (strlen($dishName) - iconv_strlen($dishName, "UTF-8"))/2;
					$enLen = iconv_strlen($dishName, "UTF-8") - $zhLen;
					$dishNameSpace = $zhLen*2 + $enLen;
					if ($printerType == PRINTER_TYPE_80) {
						if ($dishNameSpace > 24) {
							$printString = sprintf("%s\n%24s%7.2f%6.2f%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
						} else {
							$spaceLen = 24 - $dishNameSpace;
							$printString = sprintf("%s%$spaceLen"."s%7.2f%6.2f%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
						}
					} else if($printerType == PRINTER_TYPE_58) {
						if ($dishNameSpace > 12) {
							$printString = sprintf("%s\n%12s%6.2f%6.2f%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
						} else {
							$spaceLen = 12 - $dishNameSpace;
							$printString = sprintf("%s%$spaceLen"."s%6.2f%6.2f%8.2f",$dishName, "", $price, $dishQuantity, $price*$dishQuantity);
						}
					}
					$this->printl($socket, $printString);
					if (isset($obj->order[$i]->flavor)) {
						$this->printl($socket, "*口味：".$obj->order[$i]->flavor);
					}
				}
			}
			return $total;
		}

		private function printOrderReceipt($json, $printerIP, $title, $printerType, $orderId) {
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
			
			if ($dishCount <= 0) {
				die("NO_ORDERED_DISH 'NO_ORDERED_DISH'");
			}
			$this->printTitle($socket, $title, NULL);
			$oId = array($orderId);
			$this->printOrderedDishes($socket, $obj, $printerType, $oId);
			socket_close($socket);
		}
		
		private function isPrintNeed($obj, $printerId, $dishCount) {
			$isPrintNeed = FALSE;
			if ($dishCount <= 0) {
				return ;
			}
			
			for ($i=0; $i<$dishCount; $i++) {
				if ($obj->order[$i]->printer == $printerId) {
					$isPrintNeed = TRUE;
					break;
				}
			}
			
			return $isPrintNeed;
		}
		
		private function printOrderReceiptCategory($json, $printerIP, $title, $printerType, $orderId, $printerId) {
			$json_string = $json;
			$obj = json_decode($json_string); 
			$dishCount = count($obj->order);
			
			if (!$this->isPrintNeed($obj, $printerId, $dishCount)) {
				return ;
			}
			
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
			if ($socket < 0) {
				echo socket_strerror(socket_last_error())."\n";
				die("Unable to connect printer.ip:$printerIP");
			}
			
			$connection = socket_connect($socket, $printerIP, 9100); 
			if (!$connection) {
				echo socket_strerror(socket_last_error())."\n";
				die("Unable to connect printer.ip:$printerIP");
			}
			
			$this->printTitle($socket, "分单-".$title, NULL);
			$oId = array($orderId);
			$this->printOrderedDishesByPrinterId($socket, $obj, $printerType, $oId, $printerId);
			socket_close($socket);
		}
		
		private function printChangeTableReceipt($json, $printerIP, $title, $printerType) {
			$db = new CNK_DB();
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
			
			if ($dishCount <= 0) {
				die("NO_ORDERED_DISH 'NO_ORDERED_DISH'");
			}
			$this->printTitle($socket, $title, "(转台)");
			$orderId = $db->getOrderIds($obj->tableId);
			$this->printOrderedDishes($socket, $obj, $printerType, $orderId);
			socket_close($socket);
		}
		
		private function printDelReceipt($json, $printerIP, $title, $printerType) {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
			$connection = socket_connect($socket, $printerIP, 9100); 
			$json_string = $json;
			$obj = json_decode($json_string); 
			$dishCount = count($obj->order);
			
			if ($dishCount <= 0) {
				die("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
				exit();
			}
			$this->printTitle($socket, $title, "(退菜)");
			$this->printOrderedDishes($socket, $obj, $printerType, $obj->orderId);
			
			socket_close($socket);
		}
		
		private function printDelReceiptCategory($json, $printerIP, $title, $printerType, $printerId) {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
			$connection = socket_connect($socket, $printerIP, 9100); 
			$json_string = $json;
			$obj = json_decode($json_string); 
			$dishCount = count($obj->order);
			
			if (!$this->isPrintNeed($obj, $printerId, $dishCount)) {
				return;
			}
			$this->printTitle($socket, "分单-".$title, "(退菜)");
			$this->printOrderedDishesByPrinterId($socket, $obj, $printerType, $obj->orderId, $printerId);
			
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
			
			$this->printTitle($socket, "销售统计", NULL);
			$this->printSalesHeader($socket, $timestart, $timeend, $printerType);
			$this->printSalesData($obj, $socket, $printerType);
			$this->printFooter($socket, $total, $printerType);
			
		}
	}
?>