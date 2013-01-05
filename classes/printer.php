<?php
    require 'file.php';
    
    define('PRINTER_COMMAND_ALARM', "\x1B\x43\1\x13\3\n");
    define('PRINTER_COMMAND_CUT', "\x1D\x56\x42\5\n");
    //define('PRINTER_COMMAND_1X', "\x1D\x21\x01");
    define('PRINTER_COMMAND_2X', "\x1D\x21\x11");
    define('PRINTER_COMMAND_3X', "\x1D\x21\x22");
    define('PRINTER_OPEN_CASHIER', "\x10\x14\1\0\10");
    define('PRINTER_COMMAND_1X', "\x1D\x21\x1");
    define('RECEIPT_DB', "../db/temporary/receipt.db");
    
    define('RECEIPT_ORDER', 1);
    define('RECEIPT_DEL', 2);
    
    class printer {
        private $printerInfo;
        private $paddingDish;
        private $fontSize;
        private $dishFontSize;
        private $categrayPrintFontSize;
        private $receiptDB;
        
        function __construct($param) {
            $file = new file($param);
            $this->printerInfo = json_decode($file->getContent());
            $this->paddingDish = FALSE;
            $this->fontSize = PRINTER_COMMAND_1X;
            $this->dishFontSize = PRINTER_COMMAND_1X;
            $this->categrayPrintFontSize = PRINTER_COMMAND_2X;
        }
        
        function __destruct() {
            if ($this->receiptDB != null) {
                $this->receiptDB->close();
            }
        }
        
        private function connectReceiptDB() {
            $this->receiptDB = new SQLite3(RECEIPT_DB);
            $this->receiptDB->busyTimeout(5000);
            if (!$this->receiptDB) {
                $this->setErrorMsg('could not connect db:'.RECEIPT_DB);
                return false;
            }
            return true;  
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

        public function savePrintOrder($print, $orderId, $isAdd) {
            if($this->receiptDB == NULL) {
                $this->connectReceiptDB();
            }
    
            $count = count($this->printerInfo);
            for ($i=0; $i<$count; $i++) {
                if($this->printerInfo[$i]->usefor == PRINT_ORDER || 
                    $this->printerInfo[$i]->usefor == PRINT_KITCHEN ||
                    $this->printerInfo[$i]->usefor == PRINT_ORDER_NO_PRICE) {
                    $this->saveOrder($print, $i, $orderId, $isAdd, $this->printerInfo[$i]->usefor);
                }
            }
        }

        private function saveOrder($json, $index, $orderId, $isAdd, $usefor) {
            $sql = sprintf("INSERT INTO [receipt] values(null, %s, '%s', \"%s\", %s, %s, %d, %d)", 
                    $index, $json, $orderId, $isAdd?1:0, $usefor, 0, RECEIPT_ORDER);
            $this->receiptDB->exec($sql);
        }

        public function printReservation($print) {
            $this->paddingDish = FALSE;
            $dishFontSize = $this->dishFontSize;
            $this->dishFontSize = PRINTER_COMMAND_1X;
            $count = count($this->printerInfo);
            for ($i=0; $i<$count; $i++) {
                if($this->printerInfo[$i]->usefor == PRINT_CASHIER) {
                    $this->printReservationReceipt($print, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title, $this->printerInfo[$i]->type);
                }
            }
            $this->dishFontSize = $dishFontSize;
        }
        
        private function printReservationReceipt($json, $printerIP, $title, $printerType) {
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
            
            $this->printTitle($socket, $title."-预订", NULL);
            @$tableName = $obj->tableName;
            $timestamp = $obj->timestamp;
            $waiter = $obj->waiter;
            if (isset($obj->persons)) {
                $persons = $obj->persons;
            } else {
                $persons = 0;
            }
            
            $this->printReservationHeader($socket, $obj, $tableName, $waiter, $persons, $timestamp, $printerType, TRUE);
            $total = $this->printDishes($socket, $obj, $printerType);
            
            if (isset($obj->comment)) {
                $this->printComment($socket, $obj->comment, $printerType);   
            }
            if (isset($obj->addr)) {
                $this->printFooter($socket, $total, 0, $printerType, $obj->addr);
            } else {
                $this->printFooter($socket, $total, 0, $printerType);
            }
            
            socket_close($socket);
        }

        private function printOrder($print, $orderId, $isAdd, $printerIndex, $usefor) {
            try {
                if($usefor == PRINT_ORDER || $usefor == PRINT_ORDER_NO_PRICE) {
                    $this->printOrderReceipt($print, $this->printerInfo[$printerIndex]->ip, $this->printerInfo[$printerIndex]->title,
                    $this->printerInfo[$printerIndex]->type, $orderId, $isAdd, $usefor==PRINT_ORDER_NO_PRICE?FALSE:TRUE);
                } else if ($usefor == PRINT_KITCHEN) {
                    $this->printOrderReceiptCategory($print, $this->printerInfo[$printerIndex],
                    $orderId, $isAdd);
                }
            } catch (Exception $e){
                return -1;
            }
            
            return 0;
        }
        
        public function printSavedOrder() {
            if ($this->receiptDB == null) {
                $this->connectReceiptDB();
            }
            
            $this->receiptDB->busyTimeout(40000);
            $sql = "select * from receipt limit 5";
            $ret = $this->receiptDB->query($sql);
            if ($ret) {
                while($row = $ret->fetchArray()) {
                    switch($row[7]) {
                        case RECEIPT_DEL:
                            $printerRet = $this->printDel($row[2], $row[1], $row[5]);
                            break;
                        case RECEIPT_ORDER:
                            $printerRet = $this->printOrder($row[2], $row[3], $row[4], $row[1], $row[5]);
                            break;
                        default:
                            $printerRet = -1;
                    }
                    if ($printerRet >= 0) {
                        $sql = sprintf("DELETE FROM receipt WHERE id=%s", $row[0]);
                        //TODO db locked
                        $this->receiptDB->exec($sql);
                    }
                }
            }
        }

        private function printDel($print, $index, $usefor) {
            try {
                if($usefor == PRINT_ORDER || $usefor == PRINT_ORDER_NO_PRICE) {
                    $this->printDelReceipt($print, $this->printerInfo[$index]->ip,
                    $this->printerInfo[$index]->title, $this->printerInfo[$index]->type,
                         $this->printerInfo[$index]->usefor==PRINT_ORDER_NO_PRICE?FALSE:TRUE);
                } else if ($usefor == PRINT_KITCHEN) {
                    $this->printDelReceiptCategory($print, $this->printerInfo[$index]);
                }
            } catch (Exception $e){
                return -1;
            }
            
            return 0;
        }

        public function savePrintDel($print) {
            if($this->receiptDB == NULL) {
                $this->connectReceiptDB();
            }
            
            $count = count($this->printerInfo);
            for ($i=0; $i<$count; $i++) {
                if($this->printerInfo[$i]->usefor == PRINT_ORDER ||
                     $this->printerInfo[$i]->usefor == PRINT_ORDER_NO_PRICE ||
                     $this->printerInfo[$i]->usefor == PRINT_KITCHEN) {
                    $this->saveDel($print, $i, $this->printerInfo[$i]->usefor);
                }
            }
        }
        
        private function saveDel($json, $index, $usefor) {
            $sql = sprintf("INSERT INTO [receipt] values(null, %s, '%s', \"%s\", %s, %s, %d, %d)", 
                    $index, $json, 0, 0, $usefor, 0, RECEIPT_DEL);
            $this->receiptDB->exec($sql);
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
            if (ENABLED_CHANGE_TABLE_RECEIPT) {
                $count = count($this->printerInfo);
                for ($i=0; $i<$count; $i++) {
                    if($this->printerInfo[$i]->usefor == PRINT_ORDER || $this->printerInfo[$i]->usefor == PRINT_ORDER_NO_PRICE) {
                        $this->printChangeTableReceipt($print, $this->printerInfo[$i]->ip,
                        $this->printerInfo[$i]->title, $this->printerInfo[$i]->type, $this->printerInfo[$i]->usefor==PRINT_ORDER_NO_PRICE?FALSE:TRUE);
                    } else if($this->printerInfo[$i]->usefor == PRINT_KITCHEN) {
                        $this->printChangeTableReceiptCategory($print, $this->printerInfo[$i]);
                    }
                }
            }
        }

        public function printCombine($json) {
            if (ENABLED_COMBINE_TABLE_RECEIPT) {
                $count = count($this->printerInfo);
                for ($i=0; $i<$count; $i++) {
                    if($this->printerInfo[$i]->usefor == PRINT_ORDER || $this->printerInfo[$i]->usefor == PRINT_ORDER_NO_PRICE) {
                        $this->printCombineReceipt($json, $this->printerInfo[$i]->ip,
                        $this->printerInfo[$i]->title, $this->printerInfo[$i]->type, $this->printerInfo[$i]->usefor==PRINT_ORDER_NO_PRICE?FALSE:TRUE);
                    } else if ($this->printerInfo[$i]->usefor == PRINT_KITCHEN) {
                        $this->printCombineReceiptCategory($json, $this->printerInfo[$i]);
                    }
                }
            }
        }

        private function printCombineReceipt($json, $printerIP, $title, $printerType, $printPrice = true) {
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
            
            $orderId = $db->getOrderIds($obj->tableId);
            $this->printTitle($socket, $title, "(并台)", $this->convertOrderId($orderId));
            
            $dineId = $db->getDineId($obj->tableId);
            $this->printOrderedDishes($socket, $obj, $printerType, $dineId, $printPrice);
            socket_close($socket);
        }

        private function printCombineReceiptCategory($json, $printer) {
            $json_string = $json;
            $obj = json_decode($json_string); 
            $dishCount = count($obj->order);
            
            if (!$this->isPrintNeed($obj, $printer->id, $dishCount)) {
                return;
            }
            
            $db = new CNK_DB();
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
            if ($socket < 0)
            {
                echo socket_strerror(socket_last_error())."\n";
                die("Unable to connect printer.ip:$printerIP");
            }
            $connection = socket_connect($socket, $printer->ip, 9100); 
            if (!$connection) {
                echo socket_strerror(socket_last_error())."\n";
                die("Unable to connect printer.ip:$printerIP");
            }
            
            $orderId = $db->getOrderIds($obj->tableId);
            $this->printTitle($socket,  $printer->name, "(并台)", $this->convertOrderId($orderId));
            $dineId = $db->getDineId($obj->tableId);
            $this->printOrderedDishesByPrinterId($socket, $obj, $printer->type, $dineId, $printer->id);
            socket_close($socket);
        }
        
        private function printCombineSubtitle($socket, $str) {
            $this->printl($socket, $str." 桌：");
        }
        
        public function printCheckout($json, $checkoutNo) {
            $this->paddingDish = FALSE;
            $dishFontSize = $this->dishFontSize;
            $this->dishFontSize = PRINTER_COMMAND_1X;
            $count = count($this->printerInfo);
            for ($i=0; $i<$count; $i++) {
                if($this->printerInfo[$i]->usefor == PRINT_CASHIER) {
                    $this->printChecktoutReceipt($json, $this->printerInfo[$i]->ip, $this->printerInfo[$i]->title, $this->printerInfo[$i]->type, $checkoutNo);
                }
            }
            $this->dishFontSize = $dishFontSize;
        }
        
        private function printTitle($socket, $title, $subTitle, $appdix = "") {
            if (isset($subTitle)) {
                $print = iconv("UTF-8","GB18030", $title.$subTitle."  ".$appdix);
                socket_write($socket, PRINTER_COMMAND_2X);
                socket_write($socket, $print);
                socket_write($socket, "\r\n\r\n");
                socket_write($socket, $this->fontSize);
            } else {
                $print = iconv("UTF-8","GB18030", $title."  ".$appdix);
                socket_write($socket, PRINTER_COMMAND_2X);
                socket_write($socket, $print);
                socket_write($socket, "\r\n\r\n");
                socket_write($socket, $this->fontSize);
            }
            
        }
        
        private function printReservationHeader($socket, $reservation, $table, $waiter, $persons, $timestamp, $printerType, $printPrice = true) {
            if(file_exists(SHOPNAME_CONF)) {
                $shopname = file_get_contents(SHOPNAME_CONF);
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
                $this->printTableId($socket, $table);
                $print = sprintf("%s", $timestamp);
                $this->printl($socket, $print);
                
                $print = sprintf("\n吃饭时间：%s", $reservation->reservation);
                $this->printl($socket, $print);
                
                $print = sprintf("定金：%s", $reservation->deposit);
                $this->printl($socket, $print);
                
                $print = sprintf("联系方式：%s  %s", $reservation->name, $reservation->tel);
                $this->printl($socket, $print);
                if ($persons == 0) {
                    $print = sprintf("人数:未设置           服务员：%s", $waiter);
                } else {
                    $print = sprintf("人数:%-4s              服务员：%s", $persons, $waiter);
                }
                $this->printl($socket, $print);
                $this->printl($socket, "----------------------------------------------");
                if ($printPrice) {
                    $this->printl($socket, "品名                       单价  数量    小计");
                } else {
                    $this->printl($socket, "品名                                 数量");
                }
            } else if ($printerType == PRINTER_TYPE_58) {
                $spaceLen = (34 - $space)/2;
                $print = sprintf("%".$spaceLen."s%s\r\n", "", $shopname);
                $this->printl($socket, $print);
                
                $this->printTableId($socket, $table);
                $print = sprintf("%s", $timestamp);
                $this->printl($socket, $print);
                $print = sprintf("\n吃饭时间：%s", $reservation->reservation);
                $this->printl($socket, $print);
                
                $print = sprintf("定金：%s", $reservation->deposit);
                $this->printl($socket, $print);
                
                $print = sprintf("联系方式：%s  %s", $reservation->name, $reservation->tel);
                $this->printl($socket, $print);
                if ($persons == 0) {
                    $print = sprintf("人数:%-4s   服务员：%s", "未设置", $waiter);
                } else {
                    $print = sprintf("人数:%-4s   服务员：%s", $persons, $waiter);
                }
                $this->printl($socket, $print);
                $this->printl($socket, "--------------------------------");
                if ($printPrice) {
                    $this->printl($socket, "品名          单价  数量    小计");
                } else {
                    $this->printl($socket, "品名                     数量");
                }
            }
        }

        private function printHeader($socket, $dineId, $table, $waiter, $persons, $timestamp, $printerType, $printPrice = true) {
            if(file_exists(SHOPNAME_CONF)) {
                $shopname = file_get_contents(SHOPNAME_CONF);
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
                $this->printTableId($socket, $table);
                $this->printDineId($socket, $dineId);
                $print = sprintf("%s", $timestamp);
                $this->printl($socket, $print);
                if ($persons == 0) {
                    $print = sprintf("人数:未设置           服务员：%s", $waiter);
                } else {
                    $print = sprintf("人数:%-4s              服务员：%s", $persons, $waiter);
                }
                $this->printl($socket, $print);
                $this->printl($socket, "----------------------------------------------");
                if ($printPrice) {
                    $this->printl($socket, "品名                       单价  数量    小计");
                } else {
                    $this->printl($socket, "品名                                 数量");
                }
            } else if ($printerType == PRINTER_TYPE_58) {
                $spaceLen = (34 - $space)/2;
                $print = sprintf("%".$spaceLen."s%s\r\n", "", $shopname);
                $this->printl($socket, $print);
                
                $this->printTableId($socket, $table);
                $this->printDineId($socket, $dineId);
                $print = sprintf("%s", $timestamp);
                $this->printl($socket, $print);
                if ($persons == 0) {
                    $print = sprintf("人数:%-4s   服务员：%s", "未设置", $waiter);
                } else {
                    $print = sprintf("人数:%-4s   服务员：%s", $persons, $waiter);
                }
                $this->printl($socket, $print);
                $this->printl($socket, "--------------------------------");
                if ($printPrice) {
                    $this->printl($socket, "品名          单价  数量    小计");
                } else {
                    $this->printl($socket, "品名                     数量");
                }
            }
        }

        private function printHeaderForKichen($socket, $dineId, $table, $waiter, $persons, $timestamp, $printerType) {
            if ($printerType == PRINTER_TYPE_80) {
                $this->printTableId($socket, $table);
                $this->printDineId($socket, $dineId);
                $print = sprintf("%s", $timestamp);
                $this->printl($socket, $print);
                $this->printl($socket, "----------------------------------------------");
                $this->printl($socket, "品名                                  数量");
            } else if ($printerType == PRINTER_TYPE_58) {
                $this->printTableId($socket, $table);
                $this->printDineId($socket, $dineId);
                $print = sprintf("%s", $timestamp);
                $this->printl($socket, $print);
                $this->printl($socket, "--------------------------------");
                $this->printl($socket, "品名                     数量");
            }
        }

        private function printDineId($socket, $dineId) {
            if (isset($dineId)) {
                $print = sprintf("%s", "流水号:".$dineId);
                $this->printl($socket, $print);
            }
        }
        
        private function convertOrderId($orderId) {
            if (isset($orderId)) {
                if ($orderId[0] != 0) {
                    $orders = implode(",", $orderId);
                    $print = sprintf("%s", "No.".$orders);
                    return $print;
                }
            }
        }
        
        private function printTableId($socket, $table) {
            if (isset($table)) {
                socket_write($socket, PRINTER_COMMAND_3X);
                $print = sprintf("桌号:%-4s", $table);
                $this->printl($socket, $print);
                socket_write($socket, $this->fontSize);
            }
        }
        
        private function printFooter($socket, $total, $advPayment,$printerType, $addr = null) {
            if ($printerType == PRINTER_TYPE_80) {
                if ($advPayment > 0) {
                    $print = sprintf("----------------------------------------------\r\n".
                                 "合计:%40.2f\r\n".
                                 "预付:%40.2f\r\n".
                                 "----------------------------------------------\r\n".
                                 "\r\n               谢谢惠顾!               \r\n \r\n ", $total, $advPayment);
                } else {
                    $print = sprintf("----------------------------------------------\r\n".
                                 "合计:%40.2f\r\n".
                                 "----------------------------------------------\r\n".
                                 "\r\n               谢谢惠顾!               \r\n \r\n ", $total);
                }
                
                $this->printl($socket, $print);
            } else if ($printerType == PRINTER_TYPE_58) {
                if ($advPayment > 0) {
                    $print = sprintf("--------------------------------\r\n".
                                     "合计:%27.2f\r\n".
                                     "预付:%27.2f\r\n".
                                     "--------------------------------\r\n".
                                     "\r\n           谢谢惠顾!          \r\n \r\n ", $total, $advPayment);
                } else {
                    $print = sprintf("--------------------------------\r\n".
                                     "合计:%27.2f\r\n".
                                     "--------------------------------\r\n".
                                     "\r\n           谢谢惠顾!          \r\n \r\n ", $total);
                }
                $this->printl($socket, $print);
            }
            if ($addr != null) {
                $this->printR($socket, PRINTER_COMMAND_2X);
                $this->printl($socket, "送餐地址:$addr".($printerType == PRINTER_TYPE_58?"\r\n\r\n":""));
                $this->printR($socket, $this->fontSize);
            }
            $this->printR($socket, PRINTER_COMMAND_CUT);
            $this->printR($socket, PRINTER_COMMAND_ALARM);
        }
        
        private function printFooterForKichen($socket, $printerType) {
            if ($printerType == PRINTER_TYPE_80) {
                $print = sprintf("----------------------------------------------\r\n".
                                 "\r\n               谢谢惠顾!               \r\n \r\n ");
                $this->printl($socket, $print);
            } else if ($printerType == PRINTER_TYPE_58) {
                $print = sprintf("--------------------------------\r\n".
                                 "\r\n           谢谢惠顾!          \r\n \r\n ");
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
        
        private function printComment($socket, $comment, $printerType) {
            if (isset($comment)) {
                if ($printerType == PRINTER_TYPE_80) {
                    $this->printl($socket, "**********************************************");
                } else {
                    $this->printl($socket, "********************************");
                }
                $this->printl($socket, "#备注:".$comment);
                $this->printR($socket, $this->fontSize);
            }
        }

        private function printOrderedDishes($socket, $obj, $printerType, $dineId, $printPrice) {
            $tableId = $obj->tableId;
            $tableName = $obj->tableName;
            $timestamp = $obj->timestamp;
            $waiter = $obj->waiter;
            if (isset($obj->persons)) {
                $persons = $obj->persons;
            } else {
                $persons = 0;
            }
            
            if (isset($obj->reservation)) {
                $this->printReservationHeader($socket, $obj, $tableName, $waiter, $persons, $timestamp, $printerType);
            } else {
                $this->printHeader($socket, $dineId, $tableName, $waiter, $persons, $timestamp, $printerType, $printPrice);
            }
            
            if ($printPrice) {
                $total = $this->printDishes($socket, $obj, $printerType);
            } else {
                $this->printDishesForKitchen($socket, $obj, $printerType);
            }
            
            if (isset($obj->comment)) {
                $this->printComment($socket, $obj->comment, $printerType);   
            }
            $advPayment = 0;
            if (isset($obj->advPayment)) {
                $advPayment = $obj->advPayment;
            }
            
            if ($printPrice) {
                if (isset($obj->addr)) {
                    $this->printFooter($socket, $total, $advPayment, $printerType, $obj->addr);
                } else {
                     $this->printFooter($socket, $total,$advPayment, $printerType);
                }
            } else {
                $this->printFooterForKichen($socket, $printerType);
            }
        }

        private function printOrderedDishesByPrinterId($socket, $obj, $printerType, $dineId, $printerId) {
            $tableId = $obj->tableId;
            $tableName = $obj->tableName;
            $timestamp = $obj->timestamp;
            $waiter = $obj->waiter;
            if (isset($obj->persons)) {
                $persons = $obj->persons;
            } else {
                $persons = 0;
            }
            
            $this->printHeaderForKichen($socket, $dineId, $tableName, $waiter, $persons, $timestamp, $printerType);
            
            $total = $this->printDishesByPrinterId($socket, $obj, $printerType, $printerId);
            $this->printR($socket, $this->fontSize);
            if (isset($obj->comment)) {
                $this->printComment($socket, $obj->comment, $printerType);
            }
            $this->printFooterForKichen($socket, $printerType);
        }
        
        private function printChecktoutReceipt($json, $printerIP, $title, $printerType, $no) {
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
            
            $this->printTitle($socket, $title, NULL, "No.".$no);
            $this->printHeader($socket, null, $checkout->tableName, $waiter, "结账", $checkout->timestamp, $printerType);
            
            
            for ($i=0; $i<$count; $i++) {
                $obj = json_decode($objAll[$i]);
                $this->printCheckoutSubtitle($socket, $obj->tableName);
                $subTableTotal[$i] = $this->printDishes($socket, $obj, $printerType);
                $tableName[$i] = $obj->tableName;
            }
            $advPayment = 0;
            if (isset($checkout->advPayment)) {
                $advPayment = $checkout->advPayment;
            }
            $this->printCheckoutFooter($socket, $subTableTotal, $tableName, $checkout->income, $checkout->change, $checkout->receivable, $i, $advPayment, $printerType);
            
            socket_close($socket);
        }
        
        private function printCheckoutSubtitle($socket, $str) {
            $this->printl($socket, $str." 桌：");
        }

        private function printCheckoutFooter($socket, $subTableTotal, $tableName, $income, $change, $receivable, $tableCount, $advPayment, $printerType) {
            $total = 0;
            if ($printerType == PRINTER_TYPE_80) {
                $this->printl($socket, "----------------------------------------------");
                for ($i=0; $i<$tableCount; $i++) {
                    $name = " ".$tableName[$i]." 桌：";
                    if (strstr($name, ",")) {
                        $printString = sprintf("%s\r\n%45.2f", $name, $subTableTotal[$i]);
                    } else {
                        $zhLen = (strlen($name) - iconv_strlen($name, "UTF-8"))/2;
                        $enLen = iconv_strlen($name, "UTF-8") - $zhLen;
                        $dishNameSpace = $zhLen*2 + $enLen;
                        $spaceLen = 24 - $dishNameSpace;
                        $printString = sprintf("%s%$spaceLen"."s%21.2f",$name, "", $subTableTotal[$i]);                        
                    }
                    $this->printl($socket, $printString);
                    $total += $subTableTotal[$i];
                }
                if ($advPayment > 0) {
                    $print = sprintf("合计:%40.2f\r\n".
                                 "预付:%40.2f\r\n".
                                 "实收:%40.2f\r\n".
                                 "找零:%40.2f\r\n".
                                 "----------------------------------------------\r\n".
                                 "\r\n               谢谢惠顾!               \r\n \r\n ", $receivable, $advPayment, $income, $change);
                    
                } else {
                    $print = sprintf("合计:%40.2f\r\n".
                                 "实收:%40.2f\r\n".
                                 "找零:%40.2f\r\n".
                                 "----------------------------------------------\r\n".
                                 "\r\n               谢谢惠顾!               \r\n \r\n ", $receivable, $income, $change);
                }
                $this->printl($socket, $print);
            } else if ($printerType == PRINTER_TYPE_58) {
                $this->printl($socket, "--------------------------------");
                for ($i=0; $i<$tableCount; $i++) {
                    $name = " ".$tableName[$i]." 桌：";
                    if (strstr($name, ",")) {
                        $printString = sprintf("%s\r\n%32.2f", $name, $subTableTotal[$i]);
                    } else {
                        $zhLen = (strlen($name) - iconv_strlen($name, "UTF-8"))/2;
                        $enLen = iconv_strlen($name, "UTF-8") - $zhLen;
                        $dishNameSpace = $zhLen*2 + $enLen;
                        $spaceLen = 12 - $dishNameSpace;
                        $printString = sprintf("%s%$spaceLen"."s%20.2f",$name, "", $subTableTotal[$i]);
                    }
                    $this->printl($socket, $printString);
                    $total += $subTableTotal[$i];
                }
                if ($advPayment > 0) {
                    $print = sprintf("合计:%27.2f\r\n".
                                 "预付:%27.2f\r\n".
                                 "实收:%27.2f\r\n".
                                 "找零:%27.2f\r\n".
                                 "--------------------------------\r\n".
                                 "\r\n           谢谢惠顾!          \r\n \r\n ", $receivable, $advPayment, $income, $change);
                    
                } else {
                    $print = sprintf("合计:%27.2f\r\n".
                                 "实收:%27.2f\r\n".
                                 "找零:%27.2f\r\n".
                                 "--------------------------------\r\n".
                                 "\r\n           谢谢惠顾!          \r\n \r\n ", $receivable, $income, $change);
                }
                $this->printl($socket, $print);
            }
            $tolerant = abs($receivable - $total);
            if ($tolerant > 1) {
                $this->printR($socket, PRINTER_COMMAND_3X);
                $this->printl($socket, "打印错误，此单作废");
                $this->printR($socket, $this->fontSize);
                echo "receivable:$receivable,total:$total";
            }
            $this->printR($socket, PRINTER_COMMAND_CUT);
            $this->printR($socket, PRINTER_COMMAND_ALARM);
            $this->printR($socket, PRINTER_OPEN_CASHIER);
        }
        
        private function printDishes($socket, $obj, $printerType) {
            $dishCount = count($obj->order);
            $total = 0;
            $this->printR($socket, $this->dishFontSize);
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
                    $this->printR($socket, PRINTER_COMMAND_1X);
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
                if ($this->paddingDish) {
                    $this->printl($socket, "");
                }
            }
            $this->printR($socket, $this->fontSize);
            return $total;
        }
        
        private function printDishesByPrinterId($socket, $obj, $printerType, $printerId) {
            $dishCount = count($obj->order);
            $total = 0;
            $this->printR($socket, $this->categrayPrintFontSize);
            for ($i=0; $i<$dishCount; $i++) {
                if ($obj->order[$i]->printer == $printerId) {
                    $this->printDishNameAndQuan($socket, $obj, $i, $printerType);
                }
            }

            $this->printR($socket, $this->fontSize);
            return $total;
        }

        private function printDishesForKitchen($socket, $obj, $printerType) {
            $dishCount = count($obj->order);
            $total = 0;
            $this->printR($socket, $this->categrayPrintFontSize);
            for ($i=0; $i<$dishCount; $i++) {
                $this->printDishNameAndQuan($socket, $obj, $i, $printerType);
            }

            $this->printR($socket, $this->fontSize);
            return $total;
        }
        
        private function printDishNameAndQuan($socket, $dishes, $index, $printerType) {
            $dishId = $dishes->order[$index]->dishId;
            $dishQuantity = $dishes->order[$index]->quan;
            $dishName = $dishes->order[$index]->name;
            $zhLen = (strlen($dishName) - iconv_strlen($dishName, "UTF-8"))/2;
            $enLen = iconv_strlen($dishName, "UTF-8") - $zhLen;
            $dishNameSpace = $zhLen*2 + $enLen;
            if ($printerType == PRINTER_TYPE_80) {
                $dishNameLen = 36;
                if ($this->categrayPrintFontSize == PRINTER_COMMAND_2X) {
                    $dishNameLen = 16;
                }
                if ($dishNameSpace > $dishNameLen) {
                    $printString = sprintf("%s\n%".$dishNameLen."s%6.2f",$dishName, "", $dishQuantity);
                } else {
                    $spaceLen = $dishNameLen - $dishNameSpace;
                    $printString = sprintf("%s%$spaceLen"."s%6.2f",$dishName, "", $dishQuantity);
                }
            } else if($printerType == PRINTER_TYPE_58) {
                $dishNameLen = 10;
                if ($dishNameSpace > $dishNameLen) {
                    $printString = sprintf("%s\n%".$dishNameLen."s%5.2f",$dishName, "", $dishQuantity);
                } else {
                    $spaceLen = $dishNameLen - $dishNameSpace;
                    $printString = sprintf("%s%$spaceLen"."s%5.2f",$dishName, "", $dishQuantity);
                }
            }
            $this->printl($socket, $printString);
            if (isset($dishes->order[$index]->flavor)) {
                $this->printl($socket, "*口味：".$dishes->order[$index]->flavor);
            }
            if ($this->paddingDish) {
                $this->printl($socket, "");
            }
        }
        
        private function printOrderReceipt($json, $printerIP, $title, $printerType, $orderId, $isAdd, $printPrice = true) {
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
            if (isset($obj->timeType)) {
                $orderType = $obj->timeType;
            } else {
                $orderType = "即单";
            }
            
            if ($dishCount <= 0) {
                die("NO_ORDERED_DISH 'NO_ORDERED_DISH'");
            }
            
            if (isset($obj->multi)) {
                $tableCount = count($obj->tableId);
                for ($i=0; $i<$dishCount; $i++) {
                    $obj->order[$i]->quan *= $tableCount;
                }
            }
            $oId = array($orderId);
            if ($isAdd) {
                 $this->printTitle($socket, $title, "(加菜)", $orderType."  ".$this->convertOrderId($oId));
            } else {
                 $this->printTitle($socket, $title, NULL, $orderType."  ".$this->convertOrderId($oId));
            }
            $db = new CNK_DB();

            $dineId = 0;
            if (isset($obj->multi)) {
                for ($i=0; $i<$tableCount; $i++) {
                    $dine[$i] = $db->getDineId($obj->tableId[$i]);
                }
                $dineId = implode(',', $dine);
            } else {
                $dineId = $db->getDineId($obj->tableId);
            }
            $this->printOrderedDishes($socket, $obj, $printerType, $dineId, $printPrice);
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
        
        private function printOrderReceiptCategory($json, $printer, $orderId, $isAdd) {
            $json_string = $json;
            $obj = json_decode($json_string); 
            $dishCount = count($obj->order);
            
            if (!$this->isPrintNeed($obj, $printer->id, $dishCount)) {
                return ;
            }
            
             if (isset($obj->timeType)) {
                $orderType = $obj->timeType;
            } else {
                $orderType = "即单";
            }
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
            if ($socket < 0) {
                echo socket_strerror(socket_last_error())."\n";
                die("Unable to connect printer.ip:$printerIP");
            }
            
            $connection = socket_connect($socket, $printer->ip, 9100); 
            if (!$connection) {
                echo socket_strerror(socket_last_error())."\n";
                die("Unable to connect printer.ip:$printer->ip");
            }
            
            $oId = array($orderId);
            if ($isAdd) {
                 $this->printTitle($socket, $printer->name, "(加菜)", $orderType."  ".$this->convertOrderId($oId));
            } else {
                 $this->printTitle($socket, $printer->name, NULL, $orderType."  ".$this->convertOrderId($oId));
            }
            
            if (isset($obj->multi)) {
                $tableCount = count($obj->tableId);
                echo $tableCount;
                for ($i=0; $i<$dishCount; $i++) {
                    $obj->order[$i]->quan *= $tableCount;
                }
            }
            
            $db = new CNK_DB();
            $dineId = 0;
            if (isset($obj->multi)) {
                for ($i=0; $i<$tableCount; $i++) {
                    $dine[$i] = $db->getDineId($obj->tableId[$i]);
                }
                $dineId = implode(',', $dine);
            } else {
                $dineId = $db->getDineId($obj->tableId);
            }
            $this->printOrderedDishesByPrinterId($socket, $obj, $printer->type, $dineId, $printer->id);
            socket_close($socket);
        }
        
        private function printChangeTableReceipt($json, $printerIP, $title, $printerType, $printPrice = true) {
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
            
            $orderId = $db->getOrderIds($obj->tableId);
            $this->printTitle($socket, $title, "(转台)", $this->convertOrderId($orderId));
            $dineId = $db->getDineId($obj->tableId);
            $this->printOrderedDishes($socket, $obj, $printerType, $dineId, $printPrice);
            socket_close($socket);
        }
        
        private function printChangeTableReceiptCategory($json, $printer) {
            $json_string = $json;
            $obj = json_decode($json_string); 
            $dishCount = count($obj->order);
            
            if (!$this->isPrintNeed($obj, $printer->id, $dishCount)) {
                return ;
            }
            
            $db = new CNK_DB();
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
            if ($socket < 0)
            {
                echo socket_strerror(socket_last_error())."\n";
                die("Unable to connect printer.ip:$printerIP");
            }
            $connection = socket_connect($socket, $printer->ip, 9100); 
            if (!$connection) {
                echo socket_strerror(socket_last_error())."\n";
                die("Unable to connect printer.ip:$printerIP");
            }
            
            $orderId = $db->getOrderIds($obj->tableId);
            $this->printTitle($socket, $printer->name, "(转台)", $this->convertOrderId($orderId));
            $dineId = $db->getDineId($obj->tableId);
            $this->printOrderedDishesByPrinterId($socket, $obj, $printer->type, $dineId, $printer->id);
            
            socket_close($socket);
        }

        private function printDelReceipt($json, $printerIP, $title, $printerType, $printPrice = true) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
            $connection = socket_connect($socket, $printerIP, 9100); 
            $json_string = $json;
            $obj = json_decode($json_string); 
            $dishCount = count($obj->order);
            
            if ($dishCount <= 0) {
                die("HTTP/1.1 NO_ORDERED_DISH 'NO_ORDERED_DISH'");
            }
            
            $db = new CNK_DB();
            $dineId = $db->getDineId($obj->tableId);
            $this->printTitle($socket, $title, "(退菜)", $this->convertOrderId($obj->orderId));
            $this->printOrderedDishes($socket, $obj, $printerType, $dineId, $printPrice);
            
            socket_close($socket);
        }
        
        private function printDelReceiptCategory($json, $printer) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
            $connection = socket_connect($socket, $printer->ip, 9100); 
            $json_string = $json;
            $obj = json_decode($json_string); 
            $dishCount = count($obj->order);
            
            if (!$this->isPrintNeed($obj, $printer->id, $dishCount)) {
                return;
            }
            
            $db = new CNK_DB();
            $dineId = $db->getDineId($obj->tableId);
            $this->printTitle($socket, $printer->name, "(退菜)", $this->convertOrderId($obj->orderId));
            $this->printOrderedDishesByPrinterId($socket, $obj, $printer->type, $dineId, $printer->id);
            
            socket_close($socket);
        }
        
        private function printSalesHeader($socket, $timeStart, $timeEnd, $printerType) {
            if($printerType == PRINTER_TYPE_80) {
                $print = sprintf("开始时间:    %s\r\n结束时间:    %s", $timeStart, $timeEnd);
                $this->printl($socket, $print);
                $this->printl($socket, "----------------------------------------------");
                $this->printl($socket, "名称             数量       销售额  销售百分比");
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
            $this->printFooter($socket, $total, 0, $printerType);
            
        }

        public function printTestPage($printerIP, $title, $usefor, $type) {
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
            $this->printl($socket, "菜脑壳电子点菜系统\r\n打印机测试\r\n");
            
            $printerType = $type==PRINTER_TYPE_58?"58打印机":"80打印机";
            $this-> printl($socket, "打印机IP:".$printerIP);
            $this->printl($socket, "打印机类型:".$printerType);
            $this->printl($socket, "小票抬头:".$title);
            $this->printl($socket, "打印内容码:".$usefor);
            $this->printl($socket, "\r\n");
            
            $this->printR($socket, PRINTER_COMMAND_CUT);
            $this->printR($socket, PRINTER_COMMAND_ALARM);
            socket_close($socket);
        }
    }
?>
