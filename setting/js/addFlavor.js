var setting = new settings();
var xmlHttp;
var path = "";

function hideFlavorBox(object){
		$("div#flavorBoxMain").css("display","none");
		$("body").css("height", "auto");
}
function hideFlavorBoxOn(object){
		$("body").css("height", window.innerHeight);
		$("div#flavorBoxMain").css("display","block");
}

function setPath(pa) {
	path = pa;
}

function settings() {
	this.settings = new Array();
	this.loaded = 0;
	
	this.init = function (json) {
		var json_obj = json.parseJSON();
		var i = 0;
		for (; i<json_obj.length; i++) {
			this.settings[i] = json_obj[i];	
		}
		this.loaded = 1;
	}
	
	this.updateView = function() {
		var printerData = "";
		//$("#tablePageId tbody").html(" ");
		for (var i=0; i<this.settings.length; i++) {
			printerData += "<tr><td>" + this.settings[i] + "</td><td id=\"option\">";
			printerData += "<span><a href=\"javascript:void(0);\" onclick=\"javascript:modifyFlavor(" + i + ");\" >[修改]</a>" + 
				"<a href=\"javascript:void(0);\" onclick=\"javascript:removeFlavor(" + i + ");\" >[删除]</a></span></td></tr>";
		}
		document.getElementById("flavorList").innerHTML = printerData;
		//$("#tablePageId tbody").html(printerData);
		$("#tablePageId tr").mouseover(function(){
		$(this).css("background-color","#FFF");
		
		});
		$("#tablePageId tr").mouseout(function(){
		$(this).css("background-color","#E0E0E0");
		
		});
	}
	
	this.add = function(name) {
		var i = this.settings.length;
		this.settings[i] = name;
		this.updateView();
	}
	
	this.modify = function(index, name) {
		this.settings[index] = name;
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
		alert(JSON.stringify(this.settings));
		createXMLHttpRequest();
		xmlHttp.onreadystatechange = handleFlavorSettingSave;
		xmlHttp.open("POST", path + "saveFlavor.php");
		xmlHttp.setRequestHeader("cache-control","no-cache"); 
		xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		xmlHttp.send("config=" + JSON.stringify(this.settings));
	}
}

function addFlavor() {
	var nameTag = document.getElementById("nameID");
	
	nameTag.value =  "";
	document.getElementById("addFlavor").innerHTML = "<input style=\"width:60px;height:30px;\" type=\"button\" name=\"submit\" value=\"确定\" onClick=\"validFormAndSubmit()\"/>";
	hideFlavorBoxOn();
}

function saveSetting() {
	setting.save();
}

function validate_required(field, errMsg)
{
		if (field.value==null || field.value=="") {
			alert(errMsg);
			return false;
		} else {
			return true;
		}
}

function modifyFlavor(index) {
	var nameTag = document.getElementById("nameID");
	
	nameTag.value =  setting.settings[index];
	document.getElementById("addFlavor").innerHTML = "<input style=\"width:60px;height:30px;\" type=\"button\" name=\"submit\" value=\"确定\" onClick=\"validFormAndSubmit("+index+")\"/>";
	hideFlavorBoxOn();
}

function removeFlavor(index) {
	setting.remove(index);
}

function validFormAndSubmit(index) {
	var nameTag = document.getElementById("nameID");
	
	if (validate_required(nameTag, "口味不能为空") == false) {
		return false;
	}
	
	if (index == null) {
		setting.add(nameTag.value);
	} else {
		setting.modify(index, nameTag.value);
	}
	
	hideFlavorBox();
	return true;
}

function handleFlavorSettingSave() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			alert("保存成功");
		} else {
			alert("保存失败");
		}
	}
}

function createXMLHttpRequest() {
	if (window.ActiveXObject) {
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else if (window.XMLHttpRequest){
		xmlHttp = new XMLHttpRequest();
	}
}

function loadSetting() {
	createXMLHttpRequest();
	xmlHttp.onreadystatechange = handleSettingLoad;
	xmlHttp.open("GET", path + "../getFlavor.php");
	xmlHttp.setRequestHeader("cache-control","no-cache"); 
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.send(null);
}

function handleSettingLoad() {
	if (xmlHttp.readyState == 4) {
		if (xmlHttp.status == 200) {
			setting.init(xmlHttp.responseText);
			setting.updateView();
		}
	}
}

window.onload = function(){
    var obj = document.getElementById('flavorBoxTitle');
	rDrag.init(obj);
}

$(document).ready(
	function(){
	$("#menu tr").mouseover(function(){
		$(this).css("background-color","#FFF");
		});
		$("#menu tr").mouseout(function(){
		$(this).css("background-color","#E0E0E0");
		});
		
		loadSetting();
	}
)

var rDrag = {
	
	o:null,
	
	init:function(o){
		o.onmousedown = this.start;
	
	},
	start:function(e){
		var o;
		e = rDrag.fixEvent(e);
               e.preventDefault && e.preventDefault();
               rDrag.o = o = document.getElementById('flavorBoxOutline');
		o.x = e.clientX - rDrag.o.offsetLeft;
        o.y = e.clientY - rDrag.o.offsetTop;
		document.onmousemove = rDrag.move;
		document.onmouseup = rDrag.end;
	},
	move:function(e){
		e = rDrag.fixEvent(e);
		var oLeft,oTop;
		oLeft = e.clientX - rDrag.o.x;
		oTop = e.clientY - rDrag.o.y;
		rDrag.o.style.left = oLeft + 'px';
		rDrag.o.style.top = oTop + 'px';
	},
	end:function(e){
		e = rDrag.fixEvent(e);
		rDrag.o = document.onmousemove = document.onmouseup = null;
	},
    fixEvent: function(e){
        if (!e) {
            e = window.event;
            e.target = e.srcElement;
            e.layerX = e.offsetX;
            e.layerY = e.offsetY;
        }
        return e;
    }
}
