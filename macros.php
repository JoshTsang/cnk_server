<?php
	/* Error Num */
	define('ERR_COULD_NOT_CONECT_DB', "501");
	define("ERR_DB_QUERY", "502");
	define("NO_ORDERED_DISH", "503");
	define('MORE_PARAM_NEEDED', "504");
	define('ERR_DB_EXEC', "505");
	
	
	/* Database */
    define('DATABASE_MENU', "../db/menu.db");
	
	define('CATEGROY_TABLE', "table_show");
	define('CATEGROY_TABLE_COLUM_TABLE_NAME', "tablename");
	define('CATEGROY_TABLE_COLUM_ID', "id");
	define('DISHES_TABLE_COLUM_ID', "id");
	define('DISHES_TABLE_COLUM_STATUS', "status");
	
	define('DATABASE_ORDER', "../db/order.db");
	define('TABLE_ORDER_TABLE', "table_order");
	define('TABLE_ORDER_TABLE_COLUM_ID', "id");
	define('TABLE_ORDER_TABLE_COLUM_TABLE_ID', "table_id");
	define('TABLE_ORDER_TABLE_COLUM_TIMESTAMP', "timestamp");
	
	define('ORDER_DETAIL_TABLE', "order_detail");
	define('ORDER_DETAIL_TABLE_COLUM_ID', "id");
	define('ORDER_DETAIL_TABLE_COLUM_DISH_ID', "dish_id");
	define('ORDER_DETAIL_TABLE_COLUM_PRICE', "price");
	define('ORDER_DETAIL_TABLE_COLUM_ORDER_ID', "order_id");
	define('ORDER_DETAIL_TABLE_COLUM_QUANTITY', "quantity");
	
	define('DATABASE_SALES', "../db/sales.db");
	/* Dish status */
	define('DISH_STATUS_SOLD_OUT', "0");
	
?>