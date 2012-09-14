<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/db.js"></script>
<title>调试工具</title>
</head>
<body style="position: relative;">
<?php
	require("../macros.php");
	include 'header.inc';
?>
	<div class="content">
		<div class="content_page clearfix">
			<div class="menu_bar">
				<table class="menu_bar" id="menu">
					<thead>
						<tr><th>数据库工具</th></tr>
					</thead>
					<tbody>
					<tr onclick="javascript:loadDB('order', 'table_order');"><td>orderDB.order</td></tr>
					<tr onclick="javascript:loadDB('order', 'order_detail');"><td>orderDB.detail</td></tr>
					<tr onclick="javascript:loadDB('order', 'table_persons');"><td>orderDB.persons</td></tr>
					<tr onclick="javascript:loadDB('sales', 'sales_data');"><td>salesDB.sales</td></tr>
					<tr onclick="javascript:loadDB('sales', 'table_info');"><td>salesDB.table</td></tr>
					<tr onclick="javascript:showSQLEdit();"><td>SQL</td></tr>
					</tbody>
				</table>
			</div>
			
			<div class="db_data" id="content_data">
				
			</div>
		</div>
	</div>

</body>
</html>