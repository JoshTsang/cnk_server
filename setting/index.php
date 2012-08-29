<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/public.js"></script>
<title>打印设置--菜脑壳电子点菜系统</title>
</head>
<body style="position: relative;">

<div id="loginBoxMain">
	<div id="loginBoxStyle">
	</div>
	<div id="loginBoxOutline">
	<div id="loginBoxTitle" onmouseover="this.style.cursor='move'">
		<h2>增加打印机</h2>	</div>
		<div id="loginBoxContent" >
			<form action="#" method="post">
				<table class="posTable">
				<tr><td><span>打印机名:</span></td><td> <input id="posNameID" type="text" name="posName" ></td></tr>
	            <tr><td><span>IP地址:</span></td><td> <input id="posIdID" type="text" name="posAddre"  ></td></tr>
	            <tr><td><span>型号:</span></td><td> <!--<input id="posModelID" type="text" name="posModel"  >-->
	            <select>
	            <option>Volvo</option>
	            <option>Saab</option>
	            <option>Mercedes</option>
	            <option>Audi</option>
	            </select>
	            </td></tr>
	            <tr><td><span>用途:</span></td><td> <!--<input id="posUseID" type="text" name="posUse" >-->
	            <select>
	            <option>Volvo</option>
	            <option>Saab</option>
	            <option>Mercedes</option>
	            <option>Audi</option>
	            </select>
	            </td></tr>
				<tr style="height:80px;">
					<td></td>
					<td><input style="width:60px;height:30px;" type="button" name="submit" value="确定" onClick="hideLoginBox(this)"/></td>
					<td><input style="width:60px;height:30px;" type="button" name="submit" value="取消" onClick="hideLoginBox(this)"/></td>
				</table>
			</form>	
		</div>	
	</div>
</div>

	<div class="header">
		<div class="logo">
		<h1>cainaoke</h1>
		<span>--科技让生活更简单！</span>
		</div>
	</div>
	<div class="content">
		<div class="content_page clearfix">
			<div class="menu_bar">
				<table class="menu_bar" id="menu">
					<tbody>
					<tr><td>基本设置</td></tr>
					<tr><td>打印机管理</td></tr>
					</tbody>
				</table>
			</div>
			<div class="printer_info">
				<div class="printer_info_title">
					<span>打印机设置</span>
					<span class="setting_btn">
						<span class="addPos"><a href="javascript:void(0);" onclick="javascript:hideLoginBoxOn(this);" >[增加]</a></span>
						<span class="save"><a>[保存设置]</a></span>
					</span>
				</div>
				<div class="printer_info_setting">
					<table class="printer_info_table" id="tablePageId">
						<thead>
						<tr>
						<th>打印机名称</th>
						<th>IP地址</th>
						<th>打印机类型</th>
						<th>小票抬头</th>
						<th>打印内容</th>
						</tr>
						</thead>
						<tbody>
						<tr>
						<td>lalala</td>
						<td>192.168.1.1</td>
						<td>58</td>
						<td>lalalal</td>
						<td>lalalal</td>
						
						</tr>
						<tr>
						<td>nanana</td>
						<td>192.168.1.22</td>
						<td>80</td>
						<td>lololo</td>
						<td>hahaha</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

</body>
</html>