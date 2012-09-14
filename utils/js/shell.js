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
		
	shell = document.getElementById("shell");
	display = shell.value;
	}
)

function createXMLHttpRequest() {
	if (window.ActiveXObject) {
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else if (window.XMLHttpRequest){
		xmlHttp = new XMLHttpRequest();
	}
}

function exec(command) {
	console.log("command:" + command);
	if (!isValid(command)) {
		return true;
	}
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleExecResult;
	xmlHttp.open("POST", "shell_do.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send("command=" + command);
}

function isValid(value)
{
		if (value==null || value=="") {
			return false;
		} else {
			return true;
		}
}

function keyPress() {
	if   (window.event.keyCode == 13){ 
		var str = shell.value;
		var command = str.substring(display.length, str.length);
		console.log("dislen:", display.length);
		console.log("strlen", str.length);
		exec(command);
	} 
}

function showExecResult(result) {
	shell.value += result + '\n';	
}

function handleExecResult() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			showExecResult(xmlHttp.responseText);
			display = shell.value;
		}
	}
}
