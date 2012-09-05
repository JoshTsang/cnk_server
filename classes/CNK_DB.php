<?php
    class CNK_DB {
    	private $menuDB;
		private $orderDB;
		private $salesDB;
		private $phoneDB;
		
		function connectMenuDB() {
			$this->menuDB = new SQLite3(DATABASE_MENU);
			if (!$this->menuDB) {
				return false;
			}
			return true;
		}
		
		public function connectOrderDB() {
			$this->orderDB = new SQLite3(DATABASE_ORDER);
			if (!$this->orderDB) {
				return false;
			}	
			return true;
		}
		
		public function connectSalesDB() {
			$this->salesDB = new SQLite3(DATABASE_SALES);
			if (!$this->salesDB) {
				return false;
			}	
			return true;
		}
		
		public function connectPhoneDB() {
			$this->phoneDB = new SQLite3(DATABASE_PHONE);
			if (!$this->phoneDB) {
				return false;
			}	
			return true;
		}
		
		public function cleanTable($tid, $timestamp) {
			$this->saveSalesData($tid, $timestamp);
			$this->removeOrder($tid);
			$this->cleanPhoneOrder($tid);
			$this->updateTableStatus($tid, 0);
		}
		
		private function moveDishes($src, $dest) {
			$sql=sprintf("update %s set %s=%d where %s = %d",
				 TABLE_ORDER_TABLE, /*update*/
				 TABLE_ORDER_TABLE_COLUM_TABLE_ID,
				 $destTID,/*set*/
				 TABLE_ORDER_TABLE_COLUM_TABLE_ID,
				 $srcTID);	 
			$this->orderDB->query($sql);
		}
		
		public function cleanPhoneOrder($tid) {
			$sql=sprintf("delete from %s where %s=%s", 
				TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $tid);
			if($this->phoneDB == NULL) {
				$this->connectPhoneDB();
			}
			if (!$this->phoneDB->exec($sql)) {
				return false;
			}	
			return true;
		}
		
		public function updateTableStatus($tid, $status) {
			$sql=sprintf("UPDATE %s SET %s = %s where %s = %d",
						 TABLE_INFO,
						 TABLE_STATUS, $status,
						 TABLE_ID, $tid);
			if ($this->phoneDB == NULL) {
				$this->connectPhoneDB();
			}
			$this->phoneDB->exec($sql);
		}
		
		public function saveSalesData($tid, $timestamp) {
			$sql=sprintf("select %s.%s,%s.%s,%s.%s,%s.%s from %s,%s where %s.%s=%s.%s and %s=%s",
					  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_DISH_ID,
					  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_PRICE,
					  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
					  TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_TIMESTAMP,
					  TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE,
					  TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE_COLUM_ID,
					  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
					  TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
			if ($this->orderDB == NULL) {
				$this->connectOrderDB();
			}
			if ($this->salesDB == NULL) {
				$this->connectSalesDB();
			}
			$resultSet = $this->orderDB->query($sql);
			if ($resultSet) {
				while($row = $resultSet->fetchArray()) {
					$sqlInsert=sprintf("insert into [sales_data] values(null, %s, %s, %s, '%s');", $row[0],$row[1],$row[2],$timestamp);
					$this->salesDB->exec($sqlInsert);
				}
			} else {
				// header("HTTP/1.1 ERR_DB_QUERY 'ERR_DB_QUERY'");
				// die(ERR_DB_QUERY);
				return false;
			}
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
				// header("HTTP/1.1 ERR_DB_QUERY 'ERR_DB_QUERY'");
				// die(ERR_DB_QUERY);
				return false;
			}
			
			$sqlDelete=sprintf("DELETE FROM %s where %s=%s;", TABLE_ORDER_TABLE,TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid);
			if (!$this->orderDB->exec($sqlDelete)) {
					// echo "[ERR_DB_EXEC:";
					// die(ERR_DB_EXEC."]");
				return false;
			}
			return true;
		}
		
		public function cleanNotification($tid) {
			$sql=sprintf("delete from %s where %s=%s", TABLE_NOTIFICATION, NOTIFICATION_COLUM_TID, $tid);

			if (!$this->phoneDB->exec($sql)) {
					// echo "[ERR_DB_EXEC:";
					// die(ERR_DB_EXEC."]");
				return FALSE;
			}
			return true;
		}
		
		public function deletePhoneOrder($tid, $did) {
			if($did < 0 || $did == NULL){
				$sql=sprintf("delete from %s where %s=%s", 
				TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $tid);
			} else {
				$sql=sprintf("delete  from %s where %s=%s and %s = %s", 
				TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $tid,TABLE_PHONE_ORDERED_DID, $did);
			}
			
			if ($this->phoneDB == NULL) {
				$this->connectPhoneDB();
			}
			if (!$this->phoneDB->exec($sql)) {
					// echo "[ERR_DB_EXEC:";
					// die(ERR_DB_EXEC."]");
					return FALSE;
			}
			return TRUE;
		}
		
		private function getCategoryNameById($cid) {
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
				//$resultSet->free();
			} else {
				// die(ERR_DB_QUERY);
				return "";
			}
		}
		
		private function getSoldoutItem($cname) {
			$resultSet = $db->query("Select ".DISHES_TABLE_COLUM_ID
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
				// die(ERR_DB_QUERY);
				$items = "[]";
			}
			return $items;
		}
		
		public function getTableStatus($tid = -1) {
			if ($tid < 0) {
				return $this->getAllTableStatus();
			} else {
				return $this->getTableStatusByTid($tid);
			}
		}
		
		private function getAllTableStatus() {
			$sql=sprintf("select %s,%s,%s from %s",
						 TABLE_ID ,TABLE_STATUS,TABLE_NAME,TABLE_INFO);
			if ($this->phoneDB == NULL) {
				$this->connectPhoneDB();
			}
			$resultSet = $this->phoneDB->query($sql);
			if ($resultSet) {
				$i = 0;
				while($row = $resultSet->fetchArray()) {
					$item = array('id' => $row[0],
					 			  'status' => $row[1],
								  'name' => $row[2]);
					$Table[$i] = $item;
					$i++;
				}
				$jsonString = json_encode($Table);
			} else {
				//die(ERR_DB_QUERY);
				return FALSE;
			}
			
			return $jsonString;
		}
		
		private function getTableStatusByTid($tid) {
			$sql=sprintf("select %s from %s where id = %s",
						 TABLE_STATUS,TABLE_INFO,$tid);
			if ($this->phoneDB == NULL) {
				$this->connectPhoneDB();
			}
			$resultSet = $this->phoneDB->query($sql);
			if ($resultSet) {
				if ($row = $resultSet->fetchArray()) {
					$status = $row[0];
					return $status;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}
		
		public function getNotifications() {
			$sql=sprintf("select %s from %s group by %s", NOTIFICATION_COLUM_TID, TABLE_NOTIFICATION, NOTIFICATION_COLUM_TID);
			
			if ($this->phoneDB == NULL) {
				$this->connectPhoneDB();
			}
			$resultSet = $this->phoneDB->query($sql);
			if ($resultSet) {
				$j = 0;
				while($row = $resultSet->fetchArray()) {
					$sql=sprintf("select * from %s where %s=%s", TABLE_NOTIFICATION, NOTIFICATION_COLUM_TID, $row[0]);
					$resultSet2 = $this->phoneDB->query($sql);
					if ($resultSet2) {
						$i = 0;
						while($rowNotification = $resultSet2->fetchArray()) {
							$notifications[$i] = $rowNotification[2];
							$i++;
						}
					} else {
						return FALSE;
					}
					$item = array('tid' => $row[0],
								  'notifications' => $notifications);
					$table[$j] = $item;	
					$j++;
				}
			} else {
				// die(ERR_DB_QUERY);
				return FALSE;
			}
			$jsonString = json_encode($table);
		
			return $jsonString;
		}
		
		public function getNotificationTypes() {
			$sql=sprintf("select * from %s", TABLE_NOTIFICATION_TYPES);
			if ($this->menuDB == NULL) {
				$this->connectMenuDB();
			}
			$resultSet = $this->menuDB->query($sql);
			if ($resultSet) {
				$j = 0;
				while($row = $resultSet->fetchArray()) {
					$item = array('nid' => $row[0],
								  'value' => $row[1]);
					$table[$j] = $item;	
					$j++;
				}
			} else {
				// die(ERR_DB_QUERY);
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
			$datetime = split(" ", $timestamp);
			if (!$this->orderDB->exec("INSERT INTO ".TABLE_ORDER_TABLE."(".TABLE_ORDER_TABLE_COLUM_TABLE_ID.",". 
											 TABLE_ORDER_TABLE_COLUM_TIMESTAMP.")".
								"values('$tableId', '$datetime[0]T$datetime[1]')")){
				//echo "[ERR_COULD_NOT_CONECT_DB:";
				//die(ERR_COULD_NOT_CONECT_DB."]");
				return FALSE;
			}
			
			$resultSet = $this->orderDB->query("SELECT MAX(".TABLE_ORDER_TABLE_COLUM_ID.") from ".
										  TABLE_ORDER_TABLE." WHERE ".TABLE_ORDER_TABLE_COLUM_TABLE_ID."=".$tableId);
			if ($resultSet) {
				if ($row = $resultSet->fetchArray()) {
					$orderId = $row[0];
				} else {
					// echo "[ERR_DB_QUERY:";
					// die(ERR_DB_QUERY."]");
					return FALSE;
				}
			} else {
				// echo "[ERR_DB_QUERY:";
				// die(ERR_DB_QUERY."]");
				return FALSE;
			}
			
			for ($i=0; $i<$dishCount; $i++) {
				$dishId = $obj->order[$i]->id;
				$price = $obj->order[$i]->price;
				$dishQuantity = $obj->order[$i]->quan;
				$dishName = $obj->order[$i]->name;
				if (!$this->orderDB->exec("INSERT INTO ".ORDER_DETAIL_TABLE."(".ORDER_DETAIL_TABLE_COLUM_DISH_ID.",".
																	ORDER_DETAIL_TABLE_COLUM_PRICE.",".
																	ORDER_DETAIL_TABLE_COLUM_QUANTITY.",".
																	ORDER_DETAIL_TABLE_COLUM_ORDER_ID.")".
									 "values($dishId, $price, $dishQuantity, $orderId)")) {
					// echo "[ERR_DB_EXEC:";
					// die(ERR_DB_EXEC."]");
					return FALSE;
				}
			}
		}
		
		public function updateDishStatus($tid, $did, $status) {
			if($this->orderDB == NULL) {
				$this->connectOrderDB();
			}
		
			$sql=sprintf("update %s set %s=%s where %s in (select %s from %s where %s = %s) and %s = %s",
						 ORDER_DETAIL_TABLE, /*update*/
						 ORDER_DETAIL_TABLE_COLUM_STATUS,
						 $status,/*set*/
						 ORDER_DETAIL_TABLE_COLUM_ORDER_ID,
						 TABLE_ORDER_TABLE_COLUM_ID,TABLE_ORDER_TABLE,
						 TABLE_ORDER_TABLE_COLUM_TABLE_ID, $tid,
						 ORDER_DETAIL_TABLE_COLUM_DISH_ID, $did);	 

			$this->orderDB->query($sql);
		}
		
		public function getPermission($username) {
			if ($this->menuDB == NULL) {
				$this->connectMenuDB();
			}
			
			$sql=sprintf("select %s from %s where %s.%s = '%s'",
						 USER_PERMISSION,USER_TABLE,USER_TABLE,USER_NAME,$username);
			$resultSet = $this->menuDB->query($sql);
			if ($resultSet) {
				if ($row = $resultSet->fetchArray()) {
					$permission = $row[0];
				} else {
					return FALSE;
				}
			} else {
				// die(ERR_DB_QUERY);
				return FALSE;
			}
			
			return $permission;
		}
		
		public function getPhoneOrder($tid) {
			$sql=sprintf("select * from %s where %s=%s", TABLE_PHONE_ORDERED_DISH, PHONE_COLUM_TID, $tid);
			
			if ($this->phoneDB == NULL) {
				$this->connectPhoneDB();
			}
			
			$resultSet = $this->phoneDB->query($sql);
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
				//die(ERR_DB_QUERY);
			}
			
			return $jsonString;
		}
		
		public function getPWD($uname) {
			if($this->menuDB == NULL) {
				$this->connectMenuDB();
			}
			
		
			$sql=sprintf("select %s from %s where %s.%s = '%s'",
						 USER_PWD,USER_TABLE,USER_TABLE,USER_NAME,$uName);
			$resultSet = $this->menuDB->query($sql);
			if ($resultSet) {
				if ($row = $resultSet->fetchArray()) {
					$pwd = $row[0];
				} else {
					return false;
				}
			} else {
				return false;
			}
			return $pwd;
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
				// die(ERR_DB_QUERY);
				return FALSE;
			}
			return $jsonString;
		}
		
		public function updatePhoneOrder($tid, $did, $quantity) {
			if ($this->phoneDB == NULL) {
				$this->connectPhoneDB();
			}
			$sql=sprintf("UPDATE %s SET %s = %s where %s = %s and %s = %s",
				 TABLE_PHONE_ORDERED_DISH,
				 TABLE_PHONE_ORDERED_DNUM,$quantity,
				 TABLE_PHONE_ORDERED_DID,$did,
				 PHONE_COLUM_TID,$tid);

			if (!$this->phoneDB->exec($sql)) {
					// echo "[ERR_DB_EXEC:";
					// die(ERR_DB_EXEC."]");
					return FALSE;
			}
			return TRUE;
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
			if (isset($this->phoneDB)) {
				$this->phoneDB->close();
			}
		}
    }
?>