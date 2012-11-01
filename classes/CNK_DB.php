<?php
    function sqlite_last_error($db) {
        return $db->lastErrorMsg();
    }

    class CNK_DB {
        private $menuDB;
        private $orderDB;
        private $salesDB;
        //private $phoneDB;
        private $userinfoDB;
        private $orderInfoDB;
        private $err = array('succ' => false,
                             'error' => 'unknown');

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

            $this->setErrorNone();
            return TRUE;
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

        public function saveSalesData($tid, $timestamp) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }
            if ($this->salesDB == NULL) {
                $this->connectSalesDB();
            }
            $sql=sprintf("select %s.%s,%s.%s,%s.%s,%s.%s, %s from %s,%s where %s.%s=%s.%s and %s=%s",
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_DISH_ID,
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_PRICE,
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
                      TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_TIMESTAMP,
                      TABLE_ORDER_TABLE_COLUM_WAITER,
                      TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE,
                      TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE_COLUM_ID,
                      ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
                      TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
            $resultSet = $this->orderDB->query($sql);
            if ($resultSet) {
                while($row = $resultSet->fetchArray()) {
                    $sqlInsert=sprintf("insert into [sales_data] values(null, %s, %s, %s, %s, '%s');", $row[0],$row[1], $row[2], $row[4], $timestamp);
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

        public function submitOrder($obj) {
            if ($this->orderDB == NULL) {
                $this->connectOrderDB();
            }

            $dishCount = count($obj->order);
            $tableId = $obj->tableId;
            $timestamp = $obj->timestamp;
            $waiter = $obj->waiterId;
            @$datetime = split(" ", $timestamp);
            if (!$this->orderDB->exec("INSERT INTO ".TABLE_ORDER_TABLE."(".TABLE_ORDER_TABLE_COLUM_TABLE_ID.",".TABLE_ORDER_TABLE_COLUM_WAITER.",".
                                             TABLE_ORDER_TABLE_COLUM_TIMESTAMP.")".
                                "values('$tableId', '$waiter', '$datetime[0]T$datetime[1]')")){
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
                $dishId = $obj->order[$i]->dishId;
                $price = $obj->order[$i]->price;
                $dishQuantity = $obj->order[$i]->quan;
                $dishName = $obj->order[$i]->name;
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

            if (!$this->setPersons($tableId, $obj->persons)) {
                return FALSE;
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
                 if ($obj->type == "phone") {
                     if (($tableStatus/10)%10 == 5) {
                         $tableStatus -= 50;
                     }
                     $this->cleanPhoneOrder($tableId);
                 }
                 if (!$this->updateTableStatus($tableId, $tableStatus)) {
                        return FALSE;
                 }
             }
             
            $this->setErrorNone();
            return $orderId;
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
                $jsonString = json_encode($table);
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

            //TODO check for delete
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
                        //TODO imple this
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
            $this->menuDB->busyTimeout(2000);
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
            $this->orderDB->busyTimeout(2000);
            if (!$this->orderDB) {
                $this->setErrorMsg('could not connect db:'.DATABASE_ORDER);
                return false;
            }   
            return true;
        }

        private function connectSalesDB() {
            $this->salesDB = new SQLite3(DATABASE_SALES);
            $this->salesDB->busyTimeout(2000);
            if (!$this->salesDB) {
                $this->setErrorMsg('could not connect db:'.DATABASE_SALES);
                return false;
            }   
            return true;
        }

        private function connectUserInfoDB(){
            $this->userinfoDB = new SQLite3(USER_INFO_DB);
            $this->userinfoDB->busyTimeout(2000);
            if (!$this->userinfoDB) {
                $this->setErrorMsg('could not connect db:'.USER_INFO_DB);
                return false;
            }
            return true;
        }

        private function connectOrderInfoDB(){
            $this->orderInfoDB = new SQLite3(ORDER_INFO_DB);
            $this->orderInfoDB->busyTimeout(2000);
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

        //TODO return
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