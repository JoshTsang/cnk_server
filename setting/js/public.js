$(document).ready(function(){
	$("#tablePageId tr").mouseover(function(){
		$(this).css("background-color","#FFF");
		
		});
		$("#tablePageId tr").mouseout(function(){
		$(this).css("background-color","#E0E0E0");
		
		});
	
	})
	
	
	function hideLoginBox(object){
		
			$("div#loginBoxMain").css("display","none");
		
	}
	function hideLoginBoxOn(object){
		
			$("div#loginBoxMain").css("display","block");
		
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
window.onload = function(){
        var obj = document.getElementById('loginBoxTitle');
	rDrag.init(obj);
}
