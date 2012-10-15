<?php
	/* Error Num */
	define('ERR_COULD_NOT_CONECT_DB', "501");
	define("ERR_DB_QUERY", "502");
	define("NO_ORDERED_DISH", "503");
	define('MORE_PARAM_NEEDED', "504");
	define('ERR_DB_EXEC', "505");
	
	define('DEL_ORDER','0');
	define('UPDATE_ODRER','1');
	define('DEL_ITME_ORDER','2');
	/* Database */
    define('DATABASE_MENU', "../db/dish.db3");
	
	define('CATEGROY_TABLE', "table_show");
	define('CATEGROY_TABLE_COLUM_TABLE_NAME', "tablename");
	define('CATEGROY_TABLE_COLUM_ID', "id");
	define('DISHES_TABLE_COLUM_ID', "id");
	define('DISHES_TABLE_COLUM_STATUS', "status");
	
	define('DATABASE_ORDER', "../db/temporary/order.db");
	define('TABLE_ORDER_TABLE', "table_order");
	define('TABLE_ORDER_TABLE_COLUM_ID', "id");
	define('TABLE_ORDER_TABLE_COLUM_TABLE_ID', "table_id");
	define('TABLE_ORDER_TABLE_COLUM_TIMESTAMP', "timestamp");
	define('TABLE_ORDER_TABLE_COLUM_WAITER', "waiter_id");
	define('TABLE_PERSONS', 'table_persons');
	define('TABLE_PERSONS_COLUM_TID', "table_id");
	define('TABLE_PERSONS_COLUM_PERSONS', "persons");
	
	define('ORDER_DETAIL_TABLE', "order_detail");
	define('ORDER_DETAIL_TABLE_COLUM_ID', "id");
	define('ORDER_DETAIL_TABLE_COLUM_DISH_ID', "dish_id");
	define('ORDER_DETAIL_TABLE_COLUM_PRICE', "price");
	define('ORDER_DETAIL_TABLE_COLUM_STATUS', "status");
	define('ORDER_DETAIL_TABLE_COLUM_ORDER_ID', "order_id");
	define('ORDER_DETAIL_TABLE_COLUM_QUANTITY', "quantity");
	
	define('DATABASE_SALES', "../db/sales.db");
	// define('TABLE_INFO', "table_info");
	// define('TABLE_NAME', "tablenum");
	// define('TABLE_ID', "id");
	// define('TABLE_STATUS', "status");
	

	// define('USER_TABLE',"administrator");
	// define('USER_ID', "id");
	// define('USER_NAME',"username");
	// define('USER_PWD',"password");
	// define('USER_PERMISSION',"permission");
	
	//TODO update db path
	define('DATABASE_PHONE', '../db/temporary/temp.db3');
	define('TABLE_PHONE_ORDERED_DISH', 'temporaryMainDish');

	define('PHONE_COLUM_TID', 'tableID');
	
	// define('TABLE_NOTIFICATION', 'callWaiter');
	// define('NOTIFICATION_COLUM_TID', 'tableID');
// 
	// define('NOTIFICATION_COLUM_STATUS', 'callStatus');
// 	
	// define('TABLE_NOTIFICATION_TYPES', 'serviceList');
	// define('NOTIFICATION_TYPE_COLUM_ID', 'id');
	// define('NOTIFICATION_TYPE_COLUM_VALUE', 'serviceName');

	define('DATABASE_TEMP', '../db/temporary/temp.db3');
	
	define('TABLE_PHONE_ORDERED_DID','dishId');
	define('TABLE_PHONE_ORDERED_DNUM','dishnum');
	/* Dish status */
	define('DISH_STATUS_SOLD_OUT', "0");
	
	/* printers */
	define('PRINTER_TYPE_58', 1);
	define('PRINTER_TYPE_80', 2);
	define('PRINTER_FOR_KITCHEN', '192.168.0.8');
	define('PRINTER_FOE_CHECKEOUT', '192.168.0.9');
	
	//orderInfo
	define('ORDER_INFO_DB', "../db/temporary/orderInfo.db3");
	/*tableInfo*/
	define('TABLE_INFO', "tableInfo");
	define('TABLE_NAME', "tableName");
	define('TABLE_ID', "id");
	define('TABLE_STATUS', "status");
	define('TABLE_CATEGORY',"tableCategory");
	define('TABLE_INDEX',"tableOrder");
	define('TABLE_AREA',"tableArea");
	define('TABLE_FLOOR',"tableFloor");
	/*callWaiter*/
	define('TABLE_NOTIFICATION', 'callWaiter');
	define('NOTIFICATION_COLUM_TID', 'tableID');
	define('NOTIFICATION_COLUM_STATUS', 'callStatus');
	
	define('TABLE_NOTIFICATION_TYPES', 'serviceList');
	define('NOTIFICATION_TYPE_COLUM_ID', 'id');
	define('NOTIFICATION_TYPE_COLUM_VALUE', 'serviceName');

	//userInfo
	define('USER_INFO_DB', "../db/temporary/userInfo.db3");
	define('USER_INFO',"userInfo");
	define('USER_ID', "id");
	define('USER_NAME',"username");
	define('USER_PWD',"password");
	define('USER_PERMISSION',"permission");	
?>
