/**
 * @author Josh
 */
function hideLoginBox(object){
		$("div#loginBoxMain").css("display","none");
}
function hideLoginBoxOn(object){
		$("div#loginBoxMain").css("display","block");
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

function validFormAndSubmit(index) {
	var nameTag = document.getElementById("nameID");
	var ipTag = document.getElementById("ipID");
	var receiptTitle = document.getElementById("receiptTitle");
	var printerType = document.getElementById("printerType");
	var usefor = document.getElementById("usefor");
	
	if (validate_required(nameTag, "打印机名不能为空") == false) {
		return false;
	}
	if (validate_required(ipTag, "IP地址不能为空") == false) {
		return false;
	}
	
	if (isIP(ipTag.value) == false) {
		return false;
	}
	if (index == null) {
		setting.add(nameTag.value, ipTag.value, printerType.value, receiptTitle.value, usefor.value);
	} else {
		setting.modify(index, nameTag.value, ipTag.value, printerType.value, receiptTitle.value, usefor.value);
	}
	
	hideLoginBox();
	return true;
}

function isIP(strIP) {
	if (strIP ==null || strIP=="") {
		return false;
	}
	var re=/^(\d+)\.(\d+)\.(\d+)\.(\d+)$/g //匹配IP地址的正则表达式
	if(re.test(strIP))
	{
		if(RegExp.$1 <256 && RegExp.$2<256 && RegExp.$3<256 && RegExp.$4<256 && RegExp.$4>0) {
			return true;
		}
	}
	
	alert("非法的ip地址");
	return false;
}

var rDrag = {
	
	o:null,
	
	init:function(o){
		o.onmousedown = this.start;
	
	},
	start:function(e){
		var o;
		e = rDrag.fixEvent(e);
               e.preventDefault && e.preventDefault();
               rDrag.o = o = document.getElementById('loginBoxOutline');
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
