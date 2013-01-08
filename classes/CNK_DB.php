<?php
    function sqlite_last_error($db) {
        return $db->lastErrorMsg();
    }

    class CNK_DB {
        private $debug = FALSE;
        private $menuDB;
        private $orderDB;
        private $salesDB;
        //private $phoneDB;
        private $userinfoDB;
        private $orderInfoDB;
        private $err = array('succ' => false,
                             'error' => 'unknown');

        public function install() {
            $this->connectOrderDB();
            $this->connectSalesDB();
            $order = file_get_contents("db/order.sql");
            $sales = file_get_contents("db/sales.sql");
            
            $this->orderDB->exec($order);
            $this->salesDB->exec($sales);
        }
        
        public function cleanTable($tid, $timestamp) {
            if (!$this->saveSalesData($tid, $timestamp)) {
                return FALSE;
            }
            if (!$this->removeOrder($tid)) {
                return FALSE;
            }
            if (!$this->cleanPhoneOrder($tid)) {
                return FALSE;
            }
            if (!$this->updateTableStatus($tid, 0)) {
                return FALSE;
            }
            if (!$this->deletePersons($tid)) {
                return FALSE;
            }
            if (!$this->removeDine($tid)) {
                return FALSE;
            }

            $this->setErrorNone();
            return TRUE;
        }

        public function getCheckoutNo() {
            $checkoutNo = FALSE;
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            $sign = time().rand(100000, 999999);
            $sql = sprintf("INSERT INTO %s VALUES(null,'%s')", "checkout", $sign);
            if (!$this->orderDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            
            $sql = sprintf("SELECT id FROM %s WHERE sign='%s'", "checkout", $sign);
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
               if($row = $resultSet->fetchArray()) {
                    $checkoutNo = $row[0];
                }
            }
            $this->setErrorNone();
            return $checkoutNo;
        }
        
        public function deletePhoneOrder($tid, $did) {
            if($did < 0 || $did == NULL){
                $sql=sprintf("delete from %s where %s=%s", 
                TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $tid);
            } else {
                $sql=sprintf("delete  from %s where %s=%s and %s = %s", 
                TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $tid,TABLE_PHONE_ORDERED_DID, $did);
            }

            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            if (!$this->orderInfoDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            $this->setErrorNone();
            return TRUE;
        }

        /**
         * undocumented function
         * 
         * @param tableId
         * @return void
         * @author  
         */
        public function cleanPhoneOrder($tid) {
            $sql=sprintf("delete from %s where %s=%s", 
                TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $tid);
            if($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            if (!$this->orderInfoDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return false;
            }
            
            $this->setErrorNone();
            return true;
        }

        public function updateTableStatus($tid, $status) {
            $sql=sprintf("UPDATE %s SET %s = %s where %s = %d",
                         TABLE_INFO,
                         TABLE_STATUS, $status,
                         TABLE_ID, $tid);
            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            if(!$this->orderInfoDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            $this->setErrorNone();
            return TRUE;
        }

        public function getOrderIds($tid) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }

            $sql=sprintf("select %s from %s where %s=%s",
                      TABLE_ORDER_TABLE_COLUM_ID, TABLE_ORDER_TABLE,
                      TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                $i = 0;
                while($row = $resultSet->fetchArray()) {
                    $orderId[$i] = $row[0];
                    $i++;
                }
            }

            return $orderId;
        }
        
        public function getDineId($tid) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            $dineId = 0;
            $sql=sprintf("select %s from %s where %s=%s",
                      TABLE_ORDER_TABLE_COLUM_ID, "dine",
                      TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $dineId = $row[0];
                }
            }

            return $dineId;
        }

        public function saveSalesData($tid, $timestamp) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            if ($this->salesDB == NULL) {
                $this->connectSalesDB();
            }
            $sql=sprintf("select %s.%s,%s.%s,%s.%s,%s.%s,%s,%s.%s from %s,%s where %s.%s=%s.%s and %s=%s",
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_DISH_ID,
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_PRICE,
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
                      TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_TIMESTAMP,
                      TABLE_ORDER_TABLE_COLUM_WAITER,
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
                      TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE,
                      TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE_COLUM_ID,
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
                      TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                while($row = $resultSet->fetchArray()) {
                    $sqlInsert=sprintf("insert into [sales_data] values(null, %s, %s, %s, %s, '%s', %s);", $row[0],$row[1], $row[2], $row[4], $timestamp, $row[5]);
                    if (!$this->salesDB->exec($sqlInsert)) {
                        $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sqlInsert);
                        $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                        return false;
                    }
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return false;
            }

            $persons = $this->getPersons($tid);
            if (!$persons) {
                return FALSE;
            }

            $persons = substr($persons, 1, strlen($persons) - 2);
            $sqlInsert=sprintf("insert into [table_info] values(null, %s, %s, '%s');", $tid, $persons, $timestamp);
            if (!$this->salesDB->exec($sqlInsert)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->salesDB).' #sql:'.$sqlInsert);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            $this->setErrorNone();
            return true;
        }

        public function removeOrder($tid) {
            $sql=sprintf("select %s from %s where %s=%s",
                      ORDER_DETAIL_TABLE_COLUM_ID, TABLE_ORDER_TABLE,
                      TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                while($row = $resultSet->fetchArray()) {
                    $sqlDelete=sprintf("DELETE FROM %s where %s=%s;", ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,$row[0]);
                    $this->orderDB->exec($sqlDelete);
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return false;
            }

            $sqlDelete=sprintf("DELETE FROM %s where %s=%s;", TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            if (!$this->orderDB->exec($sqlDelete)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return false;
            }
            return true;
        }

        public function cleanNotification($tid) {
            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }

            $sql=sprintf("delete from %s where %s=%s", TABLE_NOTIFICATION, NOTIFICATION_COLUM_TID, $tid);

            if (!$this->orderInfoDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            $this->setErrorNone();
            return true;
        }

        public function getTableStatus($tid = -1) {
            if ($tid < 0) {
                return $this->getAllTableStatus();
            } else {
                return $this->getTableStatusByTid($tid);
            }
        }

        public function getNotifications() {
            $sql=sprintf("select %s from %s group by %s", NOTIFICATION_COLUM_TID, TABLE_NOTIFICATION, NOTIFICATION_COLUM_TID);

            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            $resultSet = $this->orderInfoDB->query($sql);
            $table = null;
            if ($resultSet) {
                $j = 0;
                while($row = $resultSet->fetchArray()) {
                    $sql=sprintf("select * from %s where %s=%s", TABLE_NOTIFICATION, NOTIFICATION_COLUM_TID, $row[0]);
                    $resultSet2 = $this->orderInfoDB->query($sql);
                    if ($resultSet2) {
                        $i = 0;
                        while($rowNotification = $resultSet2->fetchArray()) {
                            $notifications[$i] = $rowNotification[2];
                            $i++;
                        }
                    } else {
                        $this->setErrorMsg('query failed:'.$this->orderInfoDB->lastErrorMsg().' #sql:'.$sql);
                        $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                        return FALSE;
                    }
                    $item = array('tid' => $row[0],
                                  'notifications' => $notifications);
                    $table[$j] = $item; 
                    $j++;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            if ($table == null) {
                return "[]";
            } else {
                $jsonString = json_encode($table);
                return $jsonString;
            }

        }

        public function getNotificationTypes() {
            $sql=sprintf("select * from %s", TABLE_NOTIFICATION_TYPES);
            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            @$resultSet = $this->orderInfoDB->query($sql);
            if ($resultSet) {
                $j = 0;
                while($row = $resultSet->fetchArray()) {
                    $item = array('nid' => $row[0],
                                  'value' => $row[1]);
                    $table[$j] = $item; 
                    $j++;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            $jsonString = json_encode($table);
            return $jsonString; 
        }
        
        private function isOrderSubmited($tid, $MD5) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            $sql = sprintf("select * from %s where %s='%s' and %s=%s", 
                        TABLE_ORDER_TABLE, "MD5", $MD5,
                         TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        public function submitOrder($obj, $MD5) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            if ($this->validate() == false) {
                return false;
            }
            
            $tableId = $obj->tableId;
            $timestamp = $obj->timestamp;
            $waiter = $obj->waiterId;
            $ret = 0;
            if (isset($obj->multi)) {
                $tableCount = count($tableId);
                for ($i=0; $i<$tableCount; $i++) {
                    if ($i == ($tableCount -1) && isset($obj->advPayment)) {
                        $ret = $this->submitOrderToDb($tableId[$i], $waiter, $MD5, $obj->order, $obj->persons, $obj->advPayment, $timestamp, $obj->type);
                    } else {
                        $ret = $this->submitOrderToDb($tableId[$i], $waiter, $MD5, $obj->order, $obj->persons, 0, $timestamp, $obj->type);
                    }
                }
            } else {
                if (isset($obj->advPayment)) {
                    $ret = $this->submitOrderToDb($tableId, $waiter, $MD5, $obj->order, $obj->persons, $obj->advPayment, $timestamp, $obj->type);
                } else {
                    $ret = $this->submitOrderToDb($tableId, $waiter, $MD5, $obj->order, $obj->persons, 0, $timestamp, $obj->type);
                }
            }
            
            return $ret;
        }
        
        public function submitMultiOrder($obj, $MD5) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            $tableId = $obj->tableId;
            $timestamp = $obj->timestamp;
            $waiter = $obj->waiterId;
            
        }

        private function submitOrderToDb($tableId, $waiter, $MD5, $dishes, $persons, $advancePayment, $timestamp, $type) {
            $dishCount = count($dishes);
            if ($this->isOrderSubmited($tableId, $MD5)) {
                $this->setErrorNone();
                return -1;
            }
            
             //TODO set table status to 1 directly might cause err
             $tableStatusStr = $this->getTableStatusByTid($tableId);
             if (!$tableStatusStr) {
                 $tableStatus = 1;
                 if (!$this->updateTableStatus($tableId, $tableStatus)) {
                        return FALSE;
                 }
             } else {
                 $tableStatus = substr($tableStatusStr, 1, strlen($tableStatusStr)-2);
                 if ($tableStatus%10 == 0) {
                     $tableStatus += 1;
                 }
                 if ($type == "phone") {
                     if (($tableStatus/10)%10 == 5) {
                         $tableStatus -= 50;
                     }
                     $this->cleanPhoneOrder($tableId);
                 }
                 if (!$this->updateTableStatus($tableId, $tableStatus)) {
                        return FALSE;
                 }
             }
             
             //TODO advancePayment
            if (!$this->dineId($tableId, $advancePayment)) {
                return FALSE;
            }
             
            @$datetime = split(" ", $timestamp);
            if (!$this->orderDB->exec("INSERT INTO ".TABLE_ORDER_TABLE."(".TABLE_ORDER_TABLE_COLUM_TABLE_ID.",".TABLE_ORDER_TABLE_COLUM_WAITER.",".
                                             TABLE_ORDER_TABLE_COLUM_TIMESTAMP.",MD5)".
                                "values('$tableId', '$waiter', '$datetime[0]T$datetime[1]','$MD5')")){
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            $resultSet = $this->orderDB->query("SELECT MAX(".TABLE_ORDER_TABLE_COLUM_ID.") from ".
                                          TABLE_ORDER_TABLE." WHERE ".TABLE_ORDER_TABLE_COLUM_TABLE_ID."=".$tableId);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $orderId = $row[0];
                } else {
                    $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return FALSE;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            for ($i=0; $i<$dishCount; $i++) {
                $dishId = $dishes[$i]->dishId;
                $price = $dishes[$i]->price;
                $dishQuantity = $dishes[$i]->quan;
                $dishName = $dishes[$i]->name;
                $sql = "INSERT INTO ".ORDER_DETAIL_TABLE."(".ORDER_DETAIL_TABLE_COLUM_DISH_ID.",".
                                                                    ORDER_DETAIL_TABLE_COLUM_PRICE.",".
                                                                    ORDER_DETAIL_TABLE_COLUM_QUANTITY.",".
                                                                    ORDER_DETAIL_TABLE_COLUM_ORDER_ID.")".
                                     "values($dishId, $price, $dishQuantity, $orderId)";
                if (!$this->orderDB->exec($sql)) {
                    $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return FALSE;
                }
            }

            if (!$this->deletePersons($tableId)) {
                return FALSE;
            }

            if (!$this->setPersons($tableId, $persons)) {
                return FALSE;
            }
            
            $this->setErrorNone();
            return $orderId;
        }
        
        private function dineId($tableId, $advancePayment) {
            $resultSet = $this->orderDB->query("SELECT * from dine WHERE ".TABLE_PERSONS_COLUM_TID."=".$tableId);
            if ($resultSet) {
                if (!$row = $resultSet->fetchArray()) {
                   if (!$this->orderDB->exec("INSERT INTO dine values(NULL, $tableId, $advancePayment)")) {
                      return FALSE;
                    } 
                }
            }
            return TRUE;
        }
        
        private function setPersons($tid, $persons) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            $this->deletePersons($tid);
            $sql = "INSERT INTO ".TABLE_PERSONS." values(null, $tid, $persons)";
            if (!$this->orderDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            return TRUE;
        }

        private function deletePersons($tid) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }

            $sql = "DELETE FROM ".TABLE_PERSONS." WHERE ".TABLE_PERSONS_COLUM_TID."=".$tid;
            if (!$this->orderDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            return true;
        }

        private function removeDine($tid) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }

            $sql = "DELETE FROM dine WHERE ".TABLE_PERSONS_COLUM_TID."=".$tid;
            if (!$this->orderDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            return true;
        
        }
        public function getPersons($tid) {
            $persons = 0;
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }

            $resultSet = $this->orderDB->query("SELECT ".TABLE_PERSONS_COLUM_PERSONS." from ".
                                          TABLE_PERSONS." WHERE ".TABLE_PERSONS_COLUM_TID."=".$tid);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $persons = $row[0];
                } else {
                    return '['.$persons.']';
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            $this->setErrorNone();
            return '['.$persons.']';
        }

        public function getCurrentPersons() {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }

            $resultSet = $this->orderDB->query("SELECT sum(".TABLE_PERSONS_COLUM_PERSONS.") from ".
                                          TABLE_PERSONS);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $persons = $row[0];
                } else {
                    $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return FALSE;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            $this->setErrorNone();
            return '['.$persons.']';
        }

        public function updateDishStatus($tid, $did, $statusValue) {
            if($this->orderDB == NULL) {
                $this->connectOrderDB();
            }

            $sql = sprintf("select %s from %s where %s in (select %s from %s where %s = %s) and %s < %s limit 1",
                             ORDER_DETAIL_TABLE_COLUM_ID, ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
                             TABLE_ORDER_TABLE_COLUM_ID,TABLE_ORDER_TABLE,
                             TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid,
                             ORDER_DETAIL_TABLE_COLUM_STATUS, ORDER_DETAIL_TABLE_COLUM_QUANTITY);

            $sql = sprintf("select %s,%s from %s where %s = (select %s from %s where %s in (select %s from %s where %s = %s) and %s < %s and %s = %s limit 1) ",
                             ORDER_DETAIL_TABLE_COLUM_ID,
                             ORDER_DETAIL_TABLE_COLUM_STATUS,
                             ORDER_DETAIL_TABLE,
                             ORDER_DETAIL_TABLE_COLUM_ID,
                             ORDER_DETAIL_TABLE_COLUM_ID, ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
                             TABLE_ORDER_TABLE_COLUM_ID,TABLE_ORDER_TABLE,
                             TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid,
                             ORDER_DETAIL_TABLE_COLUM_STATUS, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
                             ORDER_DETAIL_TABLE_COLUM_DISH_ID, $did);

            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $orderId = $row[0];
                    $status = $row[1];
                } else {
                    $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return FALSE;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            if ($statusValue > 0) {
                $status = $statusValue;
            } else {
                $status = $status+1;
            }
            $sql=sprintf("update %s set %s=%s where %s=%s and %s = %s",
                         ORDER_DETAIL_TABLE, /*update*/
                         ORDER_DETAIL_TABLE_COLUM_STATUS,
                         $status,/*set*/
                         ORDER_DETAIL_TABLE_COLUM_ID, $orderId,
                         ORDER_DETAIL_TABLE_COLUM_DISH_ID, $did);
            if(!$this->orderDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            $this->setErrorNone();
            return true;
        }

        public function getPermission($username) {
            if ($this->userinfoDB == NULL) {
                $this->connectUserInfoDB();
            }

            $sql=sprintf("select %s from %s where %s.%s = '%s'",
                         USER_PERMISSION,USER_INFO,USER_INFO,USER_NAME,$username);
            $resultSet = $this->userinfoDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $permission = $row[0];
                } else {
                    $this->setErrorMsg('query failed:'.sqlite_last_error($this->userinfoDB).' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return FALSE;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->userinfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            return '['.$permission.']';
        }

        public function getPhoneOrder($tid) {
            $sql=sprintf("select * from %s where %s=%s", TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $tid);

            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }

            $resultSet = $this->orderInfoDB->query($sql);
            if ($resultSet) {
                $i = 0;
                while($row = $resultSet->fetchArray()) {
                    $item = array('dish_id' => $row[1],
                                  'quantity' => $row[2]);
                    $table[$i] = $item;
                    $i++;
                }
                if (isset($table)) {
                    $jsonString = json_encode($table);
                } else {
                    $jsonString = "null";
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return false;
            }

            return $jsonString;
        }

        public function getPWD($uname) {
            if($this->userinfoDB == NULL) {
                $this->connectUserInfoDB();
            }


            $sql=sprintf("select %s, %s from %s where %s.%s = '%s'",
                         USER_ID, USER_PWD,USER_INFO,USER_INFO,USER_NAME,$uname);
            $resultSet = $this->userinfoDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $id = $row[0];
                    $pwd = $row[1];
                } else {
                    $this->setErrorMsg('query failed:'.$this->userinfoDB->lastErrorMsg().' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return false;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->userinfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return false;
            }
            return $id.",".$pwd;
        }

        public function getFloorNum() {
            if($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }

            $sql=sprintf("SELECT COUNT(distinct %s) FROM %s ",
                         TABLE_FLOOR,TABLE_INFO);
            $resultSet = $this->orderInfoDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $num = $row[0];
                } else {
                    $this->setErrorMsg('query failed:'.$this->orderInfoDB->lastErrorMsg().' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return false;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return false;
            }
            return $num;
        }

        public function getOrderedDishes($tid) {
            if( $this->orderDB == NULL) {
                $this->connectOrderDB();
            }

            $sql=sprintf("select %s,%s,%s,%s,%s,%s,%s.%s,%s from %s,%s where %s.%s = %s.%s and %s.%s = %s",
                 ORDER_DETAIL_TABLE_COLUM_DISH_ID ,ORDER_DETAIL_TABLE_COLUM_PRICE,
                 ORDER_DETAIL_TABLE_COLUM_ORDER_ID,ORDER_DETAIL_TABLE_COLUM_QUANTITY,
                 TABLE_ORDER_TABLE_COLUM_TABLE_ID,TABLE_ORDER_TABLE_COLUM_TIMESTAMP,
                 ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ID,
                 ORDER_DETAIL_TABLE_COLUM_STATUS,/*select*/
                 ORDER_DETAIL_TABLE,TABLE_ORDER_TABLE,
                 ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,/*from*/
                 TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_ID,
                 TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            $table = null;
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                $i = 0;
                while($row = $resultSet->fetchArray()) {
                    $item = array('dish_id' => $row[0],
                                  'price' => $row[1],
                                  'order_id' => $row[2],
                                  'quantity' => $row[3],
                                  'status' => $row[7]);
                    $table[$i] = $item;
                    $i++;
                }
                $jsonString = json_encode($table);
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            return $jsonString;
        }

        public function updatePhoneOrder($tid, $did, $quantity) {
            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            $sql=sprintf("UPDATE %s SET %s = %s where %s = %s and %s = %s",
                 TABLE_PHONE_ORDERED_DISH,
                 TABLE_PHONE_ORDERED_DNUM,$quantity,
                 TABLE_PHONE_ORDERED_DID,$did,
                 PHONE_COLUM_TID,$tid);

            if (!$this->orderInfoDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            $this->setErrorNone();
            return TRUE;
        }

        public  function updateTableOrder($tid, $did, $type){
            if($this->orderDB == NULL){
                $this->connectOrderDB();
            }
			$orderId = FALSE;
            $sql = sprintf("select %s,%s,%s from %s,%s where %s.%s = %s.%s and %s.%s = %s
                            and %s.%s = %s",
                            ORDER_DETAIL_TABLE_COLUM_QUANTITY,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
                            ORDER_DETAIL_TABLE_COLUM_STATUS,ORDER_DETAIL_TABLE,TABLE_ORDER_TABLE,
                            ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
                            TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_ID,
                            TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_TABLE_ID,$tid,
                            ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_DISH_ID,$did);

            if ($ret = $this->orderDB->query($sql)) {
                $i = 0;
                while($row = $ret->fetchArray()) {
                    if(($row[0] < 1.00001 && $row[0] > 0.00001 && $row[2] == 0) || $type == 0 || $type == 2){
                        $sql = sprintf("DELETE from %s where %s.%s = %s and %s.%s = %s",
                                        ORDER_DETAIL_TABLE,
                                        ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,$row[1],
                                        ORDER_DETAIL_TABLE,ORDER_DETAIL_TABLE_COLUM_DISH_ID,$did);
                        if (!$this->orderDB->exec($sql)) {
                            $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                            $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                            return FALSE;
                        }else if($type == 1){
                            $this->setErrorNone();
                            return array($row[1]);
                        }else{
                            $orderId[$i] = $row[1];
                        }
                    }else if($row[0] >= $row[2] ){
                        if($type == 1 && $row[0] > 1){
                            $quan = ($row[0]-1);
                        }else if($type == 1 ){
                            $quan = $row[0];
                        }else{
                            $quan = $row[2];
                        }
                        $sql = sprintf("update %s set %s = %s where %s = %s and %s = %s",
                                        ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
                                        $quan,ORDER_DETAIL_TABLE_COLUM_ORDER_ID,$row[1],
                                        ORDER_DETAIL_TABLE_COLUM_DISH_ID,$did);
                        if (!$this->orderDB->exec($sql)) {
                            $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                            $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                            return FALSE;
                        }else if($type == 1){
                            $this->setErrorNone();
                            return array($row[1]);
                        }else{
                            $orderId[$i] = $row[1];
                        }
                    }
                    $i++; 
                }
            } else {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            $this->setErrorNone();
            return $orderId;
        }

        public function changeTable($src, $dest, $persons) {
            if (!$this->moveDishes($src, $dest)) {
                return false;
            }

            $ret = $this->cleanPhoneOrder($src);
            if (!$ret) {
                return false;
            }

            $ret = $this->cleanPhoneOrder($dest);
            if (!$ret) {
                return false;
            }

            if (!$this->updateTableStatus($dest, 1)) {
                return FALSE;
            }

            if (!$this->updateTableStatus($src, 0)) {
                return FALSE;
            }

            if (!$this->deletePersons($src)) {
                return FALSE;
            }

            if (!$this->setPersons($dest, $persons)) {
                return FALSE;
            }
            
            $this->setErrorNone();
            return TRUE;
        }

        public function removeUnusedPrinter($obj) {
            $count = count($obj);
            $printers = "";
            for ($i=0; $i<$count; $i++) {
                if ($obj[$i]->id != 0) {
                    if ($obj[$i]->usefor == PRINT_KITCHEN || $obj[$i]->usefor == 200) {
                        $printers = $printers.$obj[$i]->id.",";
                    }
                }
            }
            if (strlen($printers) > 1) {
                $sql = "DELETE FROM ".PRINTER_TABLE." WHERE id NOT IN (".substr($printers, 0, strlen($printers)-1).")";
                $this->menuDB->exec($sql);
            }
        }

        public function addPrinter($name) {
            $sql = sprintf("INSERT INTO %s(%s) VALUES('%s')", PRINTER_TABLE, PRINTER_COLUMN_NAME, $name);
            $this->menuDB->exec($sql);
            $resultSet=$this->menuDB->query("Select id"." from ".PRINTER_TABLE
                 ." where ".PRINTER_COLUMN_NAME."="
                 ."'".$name."'");

            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    return $row[0];
                } else {
                    return 0;
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->menuDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return 0;
            }
        }

        public function updatePrinter($id, $name) {
            $sql = sprintf("UPDATE %s SET %s='%s' WHERE id=%s", PRINTER_TABLE, PRINTER_COLUMN_NAME, $name, $id);
            $this->menuDB->exec($sql);
        }

        public function updatePrinterSetting($obj) {
            if ($this->menuDB == null) {
                $this->connectMenuDB();
            }

            $this->removeUnusedPrinter($obj);

            $count = count($obj);
            for ($i=0; $i<$count; $i++) {
                if ($obj[$i]->usefor == PRINT_KITCHEN) {
                    if ($obj[$i]->id == 0) {
                        //TODO imple this
                        $id = $this->addPrinter($obj[$i]->name);
                        if ($id) {
                            $obj[$i]->id = $id;
                        }
                    } else {
                        $this->updatePrinter($obj[$i]->id, $obj[$i]->name);
                    }
                }
            }

            return $obj;
        }

        public function error() {
            return json_encode($this->err);
        }

        private function connectMenuDB() {
            $this->menuDB = new SQLite3(DATABASE_MENU);
            $this->menuDB->busyTimeout(5000);
            if (!$this->menuDB) {
                $this->setErrorMsg('could not connect db:'.DATABASE_MENU);
                return false;
            }
            return true;
        }

        private function setErrorMsg($msg) {
            $this->err['error'] = $msg;
        }

        private function setErrorLocation($file, $func, $line) {
            $this->err['location'] = basename($file)." : $func : $line";    
        }

        private function setErrorNone() {
            $this->err['succ'] = TRUE;
        }

        private function connectOrderDB() {
            $this->orderDB = new SQLite3(DATABASE_ORDER);
            $this->orderDB->busyTimeout(5000);
            if (!$this->orderDB) {
                $this->setErrorMsg('could not connect db:'.DATABASE_ORDER);
                return false;
            }   
            return true;
        }

        private function connectSalesDB() {
            $this->salesDB = new SQLite3(DATABASE_SALES);
            $this->salesDB->busyTimeout(5000);
            if (!$this->salesDB) {
                $this->setErrorMsg('could not connect db:'.DATABASE_SALES);
                return false;
            }   
            return true;
        }

        private function connectUserInfoDB(){
            $this->userinfoDB = new SQLite3(USER_INFO_DB);
            $this->userinfoDB->busyTimeout(5000);
            if (!$this->userinfoDB) {
                $this->setErrorMsg('could not connect db:'.USER_INFO_DB);
                return false;
            }
            return true;
        }

        private function connectOrderInfoDB(){
            $this->orderInfoDB = new SQLite3(ORDER_INFO_DB);
            $this->orderInfoDB->busyTimeout(5000);
            if (!$this->orderInfoDB) {
                $this->setErrorMsg('could not connect db:'.ORDER_INFO_DB);
                return false;
            }
            return true;
        }

        private function moveDishes($src, $dest) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            $sql = sprintf("update dine set %s=%d where %s=%d",
                 TABLE_ORDER_TABLE_COLUM_TABLE_ID,
                 $dest,/*set*/
                 TABLE_ORDER_TABLE_COLUM_TABLE_ID,
                 $src);
            if (!$this->orderDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.$this->orderDB->lastErrorMsg().' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            $sql=sprintf("update %s set %s=%d where %s=%d",
                 TABLE_ORDER_TABLE, /*update*/
                 TABLE_ORDER_TABLE_COLUM_TABLE_ID,
                 $dest,/*set*/
                 TABLE_ORDER_TABLE_COLUM_TABLE_ID,
                 $src);  
            if (!$this->orderDB->exec($sql)) {
                $this->setErrorMsg('exec failed:'.$this->orderDB->lastErrorMsg().' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            return TRUE;
        }


        private function getCategoryNameById($cid) {
            if ($this->menuDB == NULL) {
                $this->connectMenuDB();
            }
            $resultSet=$this->menuDB->query("Select ".CATEGROY_TABLE_COLUM_TABLE_NAME
                 ." from ".CATEGROY_TABLE
                 ." where ".CATEGROY_TABLE_COLUM_ID."="
                 ."'".$cid."'");

            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    return $row[0];
                } else {
                    return "";
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->menuDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
        }

        private function getSoldoutItem($cname) {
            if($this->menuDB == NULL) {
                $this->connectMenuDB();
            }
            $resultSet = $this->menuDB->query("Select ".DISHES_TABLE_COLUM_ID
                 ." from ".$CategoryTableName
                 ." where ".DISHES_TABLE_COLUM_STATUS."="
                 ."'".DISH_STATUS_SOLD_OUT."'");
            if ($resultSet) {
                $items = "[";
                if ($row = $resultSet->fetchArray()) {
                    $DishId = $row[0];
                    $items = $items."$DishId";
                    while($row = $resultSet->fetchArray()) {
                        $DishId = $row[0];
                        $items = $items.",$DishId";
                    }
                }

                $items = $items."]";
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->menuDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            return $items;
        }

        private function getAllTableStatus() {
            $sql=sprintf("select %s,%s,%s,%s,%s,%s,%s from %s",
                         TABLE_ID ,TABLE_STATUS,TABLE_NAME,TABLE_CATEGORY,TABLE_INDEX,TABLE_AREA,TABLE_FLOOR,TABLE_INFO);

            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            @$resultSet = $this->orderInfoDB->query($sql);
            if ($resultSet) {
                $i = 0;
                while($row = $resultSet->fetchArray()) {
                    $item = array('id' => $row[0],
                                  'status' => $row[1],
                                  'name' => $row[2],
                                  'category'=>$row[3],
                                  'index' => $row[4],
                                  'area' => $row[5],
                                  'floor'=>$row[6]);
                    $Table[$i] = $item;
                    $i++;
                }
                $jsonString = json_encode($Table);
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            return $jsonString;
        }

        private function getTableStatusByTid($tid) {
            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            $sql=sprintf("select %s from %s where id = %s",
                         TABLE_STATUS,TABLE_INFO,$tid);
            @$resultSet = $this->orderInfoDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $status = $row[0];
                    return '['.$status.']';
                } else {
                    $this->setErrorMsg('query failed:'.$this->orderInfoDB->lastErrorMsg().' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        public function getKitchenTodo() {
            $sql = sprintf("SELECT %s, %s.%s, %s, (%s-%s) FROM %s, %s "
                        ."Where %s<%s and %s.%s=%s",
                        TABLE_ORDER_TABLE_COLUM_TABLE_ID, 
                        ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ID,
                        ORDER_DETAIL_TABLE_COLUM_DISH_ID,
                        ORDER_DETAIL_TABLE_COLUM_QUANTITY, ORDER_DETAIL_TABLE_COLUM_COOKED,
                        ORDER_DETAIL_TABLE, TABLE_ORDER_TABLE,
                        ORDER_DETAIL_TABLE_COLUM_COOKED, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
                        TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_ID, ORDER_DETAIL_TABLE_COLUM_ORDER_ID);
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            $ret = $this->orderDB->query($sql);
            $todos = null;
            $i = 0;
            if ($ret) {
                $i = 0;
                while($row = $ret->fetchArray()) {
                    $dishInfo = $this->getDishInfo($row[2]);
                    if ($dishInfo) {
                        $todos[$i] = array('id' => $row[1],
                                           'dishId' => $row[2],
                                           'num' => $row[3],
                                           'tid' => $row[0],
                                           'dishName' => $dishInfo[0],
                                           'displayCate' => $dishInfo[1],
                                           'unitName' => $dishInfo[2] );
                       $i++;
                    }
                }
            } else {
                $this->setErrorMsg('exec failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }   
            $this->setErrorNone();
            if ($todos == null) {
                return "[]";
            } else {
                return json_encode($todos);
            }
        }
        
        private function getDishInfo($dishId) {
            $sql = sprintf("SELECT %s, %s, %s FROM %s,%s Where %s.%s=%s and %s.%s=%d",
                        "name", "sortPrintID", "unitName", 
                        "dishInfo", "unit",
                        "unit", "id", "unitID",
                        "dishInfo", "id", $dishId);
            if ($this->menuDB == NULL) {
                $this->connectMenuDB();
            }
            
            @$resultSet = $this->menuDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $dishInfo = array($row[0],$row[1], $row[2]);
                    return $dishInfo;
                } else {
                    $this->setErrorMsg('query failed:'.$this->menuDB->lastErrorMsg().' #sql:'.$sql);
                    $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }
        
        public function getTables() {
            $sql=sprintf("select %s,%s from %s",
                         TABLE_ID , TABLE_NAME, TABLE_INFO);

            if ($this->orderInfoDB == NULL) {
                $this->connectOrderInfoDB();
            }
            $jsonString = "";
            
            @$resultSet = $this->orderInfoDB->query($sql);
            if ($resultSet) {
                $i = 0;
                while($row = $resultSet->fetchArray()) {
                    $item = array('id' => $row[0],
                                  'name' => $row[1]);
                    $table[$i] = $item;
                    $i++;
                }
                $jsonString = json_encode($table);
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderInfoDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }

            return $jsonString;
        }

        public function markCookedDish($id, $flag) {
            $dishStatus = $this->getOrderedDishStatus($id);
            
            $status = 0;
            if ($dishStatus) {
                switch($flag) {
                    case 0:
                        $status = $dishStatus['cooked'] + 1;
                        break;
                    case 1:
                        //TODO round
                        $status = $dishStatus['quan'] - $dishStatus['cooked'];
                        break;
                    default:
                        $status = $dishStatus['cooked'] + 1;
                        break;
                }
            }
            
            $sql = sprintf("UPDATE %s SET %s = %s where %s = %d",
                            ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_COOKED, $status,
                            ORDER_DETAIL_TABLE_COLUM_ID, $id);
                            
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            $ret = $this->orderDB->exec($sql);
            return $ret;
        }
        
        private function getOrderedDishStatus($id) {
            $sql=sprintf("select %s,%s from %s where %s = %s",
                         ORDER_DETAIL_TABLE_COLUM_QUANTITY ,
                         ORDER_DETAIL_TABLE_COLUM_COOKED, ORDER_DETAIL_TABLE,
                         ORDER_DETAIL_TABLE_COLUM_ID, $id);

            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            $status = FALSE;
            @$resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $status = array('quan' => $row[0],
                                  'cooked' => $row[1]);
                }
            } else {
                $this->setErrorMsg('query failed:'.sqlite_last_error($this->orderDB).' #sql:'.$sql);
                $this->setErrorLocation(__FILE__, __FUNCTION__, __LINE__);
                return FALSE;
            }
            
            return $status;
        }
        
        public function statisticsByDish($start, $end) {
            if ($this->salesDB == NULL) {
                $this->connectSalesDB();
            }
            $sql = sprintf("SELECT dish_id, sum(price*quantity), sum(quantity) ".
                            "FROM sales_data".
                            " where DATETIME(timestamp)>='%s' and ".
                            "DATETIME(timestamp)<='%s' GROUP BY dish_id", 
                            $start, $end);
            $resultSet = $this->salesDB->query($sql);
            if (!$resultSet) {
                return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
            }
            $statistics = array();
            $total = 0;

            $i = 0;
            while($row = $resultSet->fetchArray()) {
                $item = array('id' => $row[0],
                              'total' => $row[1],
                              'quantity' => $row[2]);
                $statistics[$i] = $item;
                $i++;
            }
            
            $sql = sprintf("SELECT sum(price*quantity) FROM sales_data".
                            " WHERE DATETIME(timestamp)>='%s' and DATETIME(timestamp)<='%s'",
                            $start, $end);
            $rs = $this->salesDB->query($sql);
            if (!$rs) {
                return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
            }
            if ($row = $rs->fetchArray()) {
                if ($row[0] != NULL) {
                    $total = $row[0]; 
                }
            }
            
            $ret = $this->getTableUsageAndPerson($start, $end);
            $ret = array('data' => $statistics,
                         'total' => $total,
                         'tableCount' => $ret['tableCount'],
                         'personCount' => $ret['persons']);
            return json_encode($ret);
        }
        
        public function statisticsByStuff($start, $end) {
            if ($this->salesDB == NULL) {
                $this->connectSalesDB();
            }
            $sql = sprintf("SELECT waiter_id, sum(price*quantity), sum(quantity)".
                            "FROM sales_data".
                            " where DATETIME(timestamp)>='%s' and ".
                            "DATETIME(timestamp)<='%s' GROUP BY waiter_id", 
                            $start, $end);
            $resultSet = $this->salesDB->query($sql);
            if (!$resultSet) {
                return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
            }
            $statistics = array();
            $total = 0;
            $i = 0;
            while($row = $resultSet->fetchArray()) {
                $item = array('id' => $row[0],
                              'total' => $row[1],
                              'quantity' => $row[2]);
                $statistics[$i] = $item;
                $i++;
            }
            
            $sql = sprintf("SELECT sum(price*quantity) FROM sales_data".
                            " WHERE DATETIME(timestamp)>='%s' and DATETIME(timestamp)<='%s'",
                            $start, $end);
            $rs = $this->salesDB->query($sql);
            if (!$rs) {
                return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
            }
            if ($row = $rs->fetchArray()) {
                if ($row[0] != NULL) {
                    $total = $row[0]; 
                }
            }
            
            $ret = $this->getTableUsageAndPerson($start, $end);
            $ret = array('data' => $statistics,
                         'total' => $total,
                         'tableCount' => $ret['tableCount'],
                         'personCount' => $ret['persons']);
            return json_encode($ret);
        }

        public function statisticsByPrinter($start, $end) {
            if (!file_exists("conf/printer.conf")) {
                return "";
            }
            
            if ($this->salesDB == NULL) {
                $this->connectSalesDB();
            }
            if ($this->menuDB == null) {
                $this->connectMenuDB();
            }
            $printers = json_decode(file_get_contents("conf/printer.conf"));
            $printNum = count($printers);
            $statistics = array();
            $total = 0;
            $index = 0;
            for ($i=0; $i<$printNum; $i++) {
                $item = NULL;
                if ($printers[$i]->id == 0) {
                    continue;
                }
                $sql = sprintf("SELECT id from dishInfo where sortPrintID=%s", $printers[$i]->id);
                $rsOfDishes = $this->menuDB->query($sql);
                if (!$rsOfDishes) {
                    return "err:".sqlite_last_error($this->menuDB)." #sql:$sql";
                }
                $i = 0;      
                while ($dish = $rsOfDishes->fetchArray()) {
                    $dishIds[$i] = $dish[0];
                    $i++;
                }
                
                $sql = sprintf("SELECT sum(price*quantity), sum(quantity) FROM sales_data WHERE dish_id IN (%s) AND DATETIME(timestamp)>='%s' and ".
                            "DATETIME(timestamp)<='%s'", implode(',', $dishIds), $start, $end);
                $rs = $this->salesDB->query($sql);
                if (!$rs) {
                    return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
                }
                if ($row = $rs->fetchArray()) {
                    if ($row[1] == NULL) {
                        $item = array('name' => $printers[$index]->name,
                                 'total' => 0,
                                 'quantity' => 0);
                    } else {
                        $item = array('name' => $printers[$index]->name,
                                 'total' => $row[0],
                                 'quantity' => $row[1] );
                    }
                }
                
                if ($item != NULL) {
                    $statistics[$index] = $item;
                    $index++;
                }
            }
            $sql = sprintf("SELECT sum(price*quantity) FROM sales_data".
                            " WHERE DATETIME(timestamp)>='%s' and DATETIME(timestamp)<='%s'",
                            $start, $end);
            $rs = $this->salesDB->query($sql);
            if (!$rs) {
                return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
            }
            if ($row = $rs->fetchArray()) {
                if ($row[0] != NULL) {
                    $total = $row[0]; 
                }
            }
            
            $ret = $this->getTableUsageAndPerson($start, $end);
            $ret = array('data' => $statistics,
                         'total' => $total,
                         'tableCount' => $ret['tableCount'],
                         'personCount' => $ret['persons']);
            return json_encode($ret);
            return json_encode($ret);
        }
        
        public function statisticsByCategory($start, $end) {
            $sql = "SELECT categoryID, categoryName FROM category";
            if ($this->menuDB == null) {
                $this->connectMenuDB();
            }
            
            if ($this->salesDB == null) {
                $this->connectSalesDB();
            }
            
            $rsOfCategory = $this->menuDB->query($sql);
            if (!$rsOfCategory) {
                return "err:".sqlite_last_error($this->menuDB)." #sql:$sql";
            }
            $statistics = array();
            $total = 0;
            $index = 0;
            while ($category = $rsOfCategory->fetchArray()) {
                $item = null;
                $sql = sprintf("SELECT dishID FROM dishCategory WHERE categoryID=%s",
                        $category[0]);
                $rsOfDishes = $this->menuDB->query($sql);
                if (!$rsOfDishes) {
                    return "err:".sqlite_last_error($this->menuDB)." #sql:$sql";
                }
                $i = 0;      
                while ($dish = $rsOfDishes->fetchArray()) {
                    $dishIds[$i] = $dish[0];
                    $i++;
                }
                
                $sql = sprintf("SELECT sum(price*quantity), sum(quantity) FROM sales_data WHERE dish_id IN (%s) AND DATETIME(timestamp)>='%s' and ".
                            "DATETIME(timestamp)<='%s'", implode(',', $dishIds), $start, $end);
                $rs = $this->salesDB->query($sql);
                if (!$rs) {
                    return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
                }
                if ($row = $rs->fetchArray()) {
                    if ($row[1] == NULL) {
                        $item = array('id' => $category[0],
                                 'name' => $category[1],
                                 'total' => 0,
                                 'quantity' => 0);
                    } else {
                        $item = array('id' => $category[0],
                                 'name' => $category[1],
                                 'total' => $row[0],
                                 'quantity' => $row[1] );
                    }
                }
                if ($item != null) {
                    $statistics[$index] = $item;
                    $index++;
                }
                $dishIds = null;
            }
            
            $sql = sprintf("SELECT sum(price*quantity) FROM sales_data".
                            " WHERE DATETIME(timestamp)>='%s' and DATETIME(timestamp)<='%s'",
                            $start, $end);
            $rs = $this->salesDB->query($sql);
            if (!$rs) {
                return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
            }
            if ($row = $rs->fetchArray()) {
                if ($row[0] != NULL) {
                    $total = $row[0]; 
                }
            }
            $ret = $this->getTableUsageAndPerson($start, $end);
            $ret = array('data' => $statistics,
                         'total' => $total,
                         'tableCount' => $ret['tableCount'],
                         'personCount' => $ret['persons']);
            return json_encode($ret);
        }
        
        public function statisticsCategoryDetail($start, $end, $id) {
            if ($this->menuDB == null) {
                $this->connectMenuDB();
            }
            
            if ($this->salesDB == null) {
                $this->connectSalesDB();
            }
            
            $statistics = array();
            $index = 0;
            $sql = sprintf("SELECT dishID FROM dishCategory WHERE categoryID=%s", $id);
            $rsOfDishes = $this->menuDB->query($sql);
            if (!$rsOfDishes) {
                return "err:".sqlite_last_error($this->menuDB)." #sql:$sql";
            }
            $i = 0;      
            while ($dish = $rsOfDishes->fetchArray()) {
                $dishIds[$i] = $dish[0];
                $i++;
            }
            
            $sql = sprintf("SELECT dish_id, sum(price*quantity), sum(quantity) FROM sales_data WHERE dish_id IN (%s) AND DATETIME(timestamp)>='%s' and ".
                        "DATETIME(timestamp)<='%s' group by dish_id", implode(',', $dishIds), $start, $end);
            $rs = $this->salesDB->query($sql);
            if (!$rs) {
                return "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
            }
            while ($row = $rs->fetchArray()) {
                $item = array('id' => $row[0],
                         'name' => 0,
                         'total' => $row[1],
                         'quantity' => $row[2] );
                $statistics[$index] = $item;
                $index++;
            }
            
            $ret = array('data' => $statistics,
                         'total' => 0,
                         'tableCount' => 0,
                         'personCount' => 0);
            return json_encode($ret);
        }
               
        public function getStuff() {
            if ($this->userinfoDB == null) {
                $this->connectUserInfoDB();
            }
            $sql = sprintf("select * from %s",USER_INFO);
            $rs = $this->userinfoDB->query($sql);
            if (!$rs) {
                return "err:".sqlite_last_error($this->menuDB)." #sql:$sql";
            }
            $index = 0;
            $stuffs = null;
            while ($stuff = $rs->fetchArray()) {
                $stuffs[$index] = array('id' => $stuff[0],
                                        'name' => $stuff[1]);
                $index++;
            }
            
            return json_encode($stuffs);
        }
        
        public function getTableUsageAndPerson($start, $end) {
            if ($this->salesDB == null) {
                $this->connectSalesDB();
            }
            $sql = sprintf("SELECT count(), sum(persons) FROM table_info".
                        " WHERE DATETIME(timestamp)>='%s' and DATETIME(timestamp)<='%s'",
                        $start, $end);
            $rs = $this->salesDB->query($sql);
            if (!$rs) {
                echo "err:".sqlite_last_error($this->salesDB)." #sql:$sql";
                return fasle;
            }
            if ($row = $rs->fetchArray()) {
                if ($row[0] == NULL) {
                    $row[0] = 0;
                }
                if ($row[1] == NULL) {
                    $row[1] = 0;
                }
                return array('tableCount' => $row[0],
                                'persons' => $row[1] );
            }
            return false;
        }
        
        function getMacAddr(){
              $mac = array();  
              exec("ifconfig -a", $mac, $ret);
              if ($ret != 0) {
                  return "";
              }
              $temp_array = array();  
              foreach ( $mac as $value ){
                  if (preg_match("/[0-9a-f][0-9a-f][:-]".
                                  "[0-9a-f][0-9a-f][:-]".
                                  "[0-9a-f][0-9a-f][:-]".
                                  "[0-9a-f][0-9a-f][:-]".
                                  "[0-9a-f][0-9a-f][:-]".
                                  "[0-9a-f][0-9a-f]/i",$value, $temp_array ) ){  
                       $mac_addr = $temp_array[0];  
                       return $mac_addr;  
                   }  
              }  
              return "";
        }
        
        public function register($msg) {
            $md51 = md5($msg);
            $md5 = md5($md51.$this->getMacAddr());
            file_put_contents("conf/lisence.conf", $md51.$md5);
        }
        
        public function validate() {
            if (file_exists("conf/lisence.conf")) {
                $str = file_get_contents("conf/lisence.conf");
                $md51 = substr($str, 0, 32);
                $macAddr = $this->getMacAddr();
                if (strlen($macAddr) < 1) {
                    return false;
                }
                $md5 = md5($md51.$macAddr);
                if ($md5 == substr($str, 32, 64)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        
        public function getAdvancePayment($tid) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            $payment = 0;
            $sql=sprintf("select %s from %s where %s=%s",
                      "advance_payment", "dine",
                      TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                if ($row = $resultSet->fetchArray()) {
                    $payment = $row[0];
                }
            }

            return $payment;
        }
        
        public function setAdvancePayment($tid, $payment) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            
            $sql=sprintf("UPDATE %s SET %s=%s where %s=%s",
                       "dine", "advance_payment", $payment,
                      TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            $resultSet = $this->orderDB->exec($sql);
            if ($resultSet) {
                return $payment;
            } else {
                echo $this->orderDB->lastErrorMsg()."#sql:$sql";
                return 0;
            }

       }
        
        function __destruct() {
            if (isset($this->menuDB)) {
                $this->menuDB->close();
            }
            if (isset($this->orderDB)) {
                $this->orderDB->close();
            }
            if (isset($this->salesDB)) {
                $this->salesDB->close();
            }
            if (isset($this->orderInfoDB)) {
                $this->orderInfoDB->close();
            }
            if (isset($this->userinfoDB)) {
                $this->userinfoDB->close();
            }
            if (isset($this->orderInfoDB)) {
                $this->orderInfoDB->close();
            }
        }
    }
?>