/**
 * @author Josh
 */
var xmlHttp;
var step;

function createXMLHttpRequest() {
	if (window.ActiveXObject) {
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else if (window.XMLHttpRequest){
		xmlHttp = new XMLHttpRequest();
	}
}

function savePadNum(s) {
	var padNum = document.getElementById("padNum");;
	if (!isValidPadNum(padNum.value)) {
		alert("pad数目不合法！");
		return ;
	}
	
	step = s + 1;
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleResult;
	xmlHttp.open("POST", "../orderPad/utils/padSetting.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send("padNum=" + padNum.value);
}

function loadMenu(s) {
	step = s + 1;
	var menu = document.getElementById("menu");
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleResult;
	xmlHttp.open("POST", "menu.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send("menu=" + menu.value);
}

function loadPrinterConfig(s) {
	step = s + 1;
	var printer = document.getElementById("printer");
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleResult;
	xmlHttp.open("POST", "printer.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send("printer=" + printer.value);
}

function finish(s) {
	step = s + 1;
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleResult;
	xmlHttp.open("GET", "clean.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send(null);
}

function isValidPadNum(value)
{
		if (value==null || value=="") {
			return false;
		} else {
			if (parseInt(value)==value) {
				return true;
			} else {
				return false;
			}
		}
}


function handleResult() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			//window.location.href = "index.php?step=" + step;	
		} else {
			alert("保存失败");
		}
	}
}