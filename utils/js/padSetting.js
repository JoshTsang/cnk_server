var xmlHttp;
var display;
var shell;

$(document).ready(
	function(){
	$("#menu tr").mouseover(function(){
		$(this).css("background-color","#FFF");
		});
		$("#menu tr").mouseout(function(){
		$(this).css("background-color","#E0E0E0");
		});
	}
)

function createXMLHttpRequest() {
	if (window.ActiveXObject) {
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else if (window.XMLHttpRequest){
		xmlHttp = new XMLHttpRequest();
	}
}

function save() {
	var padNum = document.getElementById("padNum");;
	if (!isValid(padNum.value)) {
		alert("pad数目不合法！");
		return ;
	}
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleResult;
	xmlHttp.open("POST", "padSetting.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send("padNum=" + padNum.value);
}

function isValid(value)
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
			alert("保存成功");
		} else {
			alert("保存失败");
		}
	}
}
