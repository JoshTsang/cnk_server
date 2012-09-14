var xmlHttp;
var db = new CNK_DB();
var dbName;
var sql;

function CNK_DB() {
	this.loaded = 0;
	
	this.init = function (json) {
		var json_obj = json.parseJSON();
		var i = 0;
		for (; i<json_obj.length; i++) {
			this.settings[i] = new printerSetting(json_obj[i].name, json_obj[i].ip, json_obj[i].type, json_obj[i].title, json_obj[i].usefor);	
		}
		this.loaded = 1;
	}
	
	this.showDBdata = function(content) {
		document.getElementById("content_data").innerHTML = content;
	}
	
}

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

function loadDB(db, table) {
	console.log("loadDB:" + db + "&" + table);
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleShowDB;
	xmlHttp.open("GET", "db_data.php?db=" + db + "&table=" +table);
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send(null);
}

function showSQLEdit() {
	document.getElementById("content_data").innerHTML = '<form name="input" action="javascript:execSQL();" method="get">' +
														'<input type="text" name="db" id="db"/>' +
														'<input type="submit" value="Submit" /><br/>' +
														'<textarea id="sql"></textarea>' +
														'</form>';
	if (dbName != null && sql != null) {													
		document.getElementById("sql").value = sql;
		document.getElementById("db").value = dbName;
	}
}

function isValid(value)
{
		if (value==null || value=="") {
			return false;
		} else {
			return true;
		}
}

function execSQL() {
	sql = document.getElementById("sql").value;
	dbName = document.getElementById("db").value;
	if (!isValid(sql)) {
		alert("非法的sql语句");
		return false;
	}
	if (!isValid(dbName)) {
		alert("非法的dbName");
		return false;
	}
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleShowDB;
	xmlHttp.open("POST", "sql.php?db=" + dbName);
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send("sql=" + sql);
}

function handleShowDB() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			db.showDBdata(xmlHttp.responseText);
		}
	}
}
