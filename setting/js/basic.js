var xmlHttp;
var setting = new settings();
var shopname = new shopnameSetting();
function printerSetting(name, ip, type, title, usefor) {
	this.name = name;
	this.ip = ip;
	this.type = type;
	this.title = title;
	this.usefor = usefor;	
}

function shopnameSetting() {
	this.init = function(name) {
		var shopname = document.getElementById("shopName");
		shopname.value = name;
	}
	
	this.save = function() {
		var shopname = document.getElementById("shopName");
		createXMLHttpRequest();
		xmlHttp.onreadystatechange = handleShopNameSave;
		xmlHttp.open("GET", "setShopName.php?shopname=" + shopname.value);
		xmlHttp.setRequestHeader("cache-control","no-cache"); 
		xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		xmlHttp.send(null);
	}
}

function settings() {
	this.settings = new Array();
	this.loaded = 0;
	
	this.init = function (json) {
		var json_obj = json.parseJSON();
		var i = 0;
		for (; i<json_obj.length; i++) {
			this.settings[i] = new printerSetting(json_obj[i].name, json_obj[i].ip, json_obj[i].type, json_obj[i].title, json_obj[i].usefor);	
		}
		this.loaded = 1;
	}
	
	this.updateView = function() {
		var printerData = "";
		//$("#tablePageId tbody").html(" ");
		for (var i=0; i<this.settings.length; i++) {
			printerData += "<tr><td>" + this.settings[i].name + "</td><td>" + 
				this.settings[i].ip + "</td><td>";
			switch (this.settings[i].type) {
				case 1:
				case "1":
					printerData += "58打印机";
					break;
				case 2:
				case "2":
					printerData += "80打印机";
					break;
				default:
					printerData += this.settings[i].type;
					break;
			}
			
			printerData += "</td><td>" + this.settings[i].title + "</td><td>";
			
			switch(this.settings[i].usefor) {
				case 101:
				case "101":
					printerData += "统计";
					break;
				case 100:
				case "100":
					printerData += "菜单";
					break;
				case 200:
				case "200":
					printerData += "停用";
					break;
				default:
					printerData += this.settings[i].usefor;
					break;
			}
			printerData += "</td><td>";
			printerData += "<span><a href=\"javascript:void(0);\" onclick=\"javascript:modifyPrinter(" + i + ");\" >[修改]</a>" + 
				"<a href=\"javascript:void(0);\" onclick=\"javascript:removePrinter(" + i + ");\" >[删除]</a></span></td></tr>";
		}
		document.getElementById("printList").innerHTML = printerData;
		//$("#tablePageId tbody").html(printerData);
		$("#tablePageId tr").mouseover(function(){
		$(this).css("background-color","#FFF");
		
		});
		$("#tablePageId tr").mouseout(function(){
		$(this).css("background-color","#E0E0E0");
		
		});
	}
	
	this.add = function(name, ip, type, title, usefor) {
		var i = this.settings.length;
		this.settings[i] = new printerSetting(name, ip, type, title, usefor);
		this.updateView();
	}
	
	this.modify = function(index, name, ip, type, title, usefor) {
		this.settings[index] = new printerSetting(name, ip, type, title, usefor);
		this.updateView();
	}
	
	this.remove = function(index) {
		for (var i=index;i<this.settings.length - 1; i++) {
			this.settings[i] = this.settings[i+1];
		}
		this.settings.length = this.settings.length - 1;
		this.updateView();
	}
	
	this.save = function() {
		xmlHttp.onreadystatechange = handlePrinterSettingSave;
		xmlHttp.open("POST", "saveSettings.php");
		xmlHttp.setRequestHeader("cache-control","no-cache"); 
		xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		xmlHttp.send("config=" + this.settings.toJSONString());
	}
}

window.resize = function() {
	adjustBodyHeight();
}

$(document).ready(
	function(){
	$("#menu tr").mouseover(function(){
		$(this).css("background-color","#FFF");
		});
		$("#menu tr").mouseout(function(){
		$(this).css("background-color","#E0E0E0");
		});
		
	
	adjustBodyHeight();
	loadShopName();
	showBaseSetting();
	}
)
	
function showPrinterSetting() {
	$("div.printer_info").css("display","block");
	$("div.shop_name").css("display","none");
	loadPrinterSetting();
}

function showBaseSetting() {
	$("div.printer_info").css("display","none");
	$("div.shop_name").css("display","block");
}

function adjustBodyHeight() {
	$("body").css("height", window.innerHeight);
}

function savePrinterSetting() {
	setting.save();
}

function removePrinter(index) {
	setting.remove(index);
}

function saveShopName() {
	shopname.save();
}

function addPrinter() {
	var nameTag = document.getElementById("nameID");
	var ipTag = document.getElementById("ipID");
	nameTag.value =  "";
	ipTag.value = "";
	document.getElementById("submitPrinterSetting").innerHTML = "<input style=\"width:60px;height:30px;\" type=\"button\" name=\"submit\" value=\"确定\" onClick=\"validFormAndSubmit()\"/>";
	hideLoginBoxOn();
}

function modifyPrinter(index) {
	var nameTag = document.getElementById("nameID");
	var ipTag = document.getElementById("ipID");
	var receiptTitle = document.getElementById("receiptTitle");
	var printerType = document.getElementById("printerType");
	var usefor = document.getElementById("usefor");
	
	nameTag.value =  setting.settings[index].name;
	ipTag.value = setting.settings[index].ip;
	receiptTitle.value = setting.settings[index].title;
	printerType.value = setting.settings[index].type;
	usefor.value = setting.settings[index].usefor;
	document.getElementById("submitPrinterSetting").innerHTML = "<input style=\"width:60px;height:30px;\" type=\"button\" name=\"submit\" value=\"确定\" onClick=\"validFormAndSubmit("+index+")\"/>";
	hideLoginBoxOn();
}

function sendTestingRequest() {
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handlePrinterTest;
	xmlHttp.open("GET", "testPrinter.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send(null);	
}

function createXMLHttpRequest() {
	if (window.ActiveXObject) {
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else if (window.XMLHttpRequest){
		xmlHttp = new XMLHttpRequest();
	}
}

function loadPrinterSetting() {
	if (setting.loaded == 0) {
		createXMLHttpRequest();
		xmlHttp.onreadystatechange = handlePrinterSettingLoad;
		xmlHttp.open("GET", "printerInfo.json");
		xmlHttp.setRequestHeader("cache-control","no-cache"); 
		xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		xmlHttp.send(null);
	}
}

function loadShopName() {
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleShopNameLoad;
	xmlHttp.open("GET", "getShopName.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send(null);
}

//TODO handle err
function handlePrinterSettingLoad() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			setting.init(xmlHttp.responseText);
			setting.updateView();
		}
	}
}

function handlePrinterSettingSave() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			alert("保存成功");
		} else {
			alert("保存失败");
		}
	}
}

function handlePrinterTest() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			alert("测试数据已发送");
		} else {
			alert("发送测试数据失败");
		}
	}
}

function handleShopNameLoad() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			shopname.init(xmlHttp.responseText);
		}
	}
}

function handleShopNameSave() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			alert("保存成功");
		} else {
			alert("保存失败");
		}
	}
}
window.onload = function(){
    var obj = document.getElementById('loginBoxTitle');
	rDrag.init(obj);
}
