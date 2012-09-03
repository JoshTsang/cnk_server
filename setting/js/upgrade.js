XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
            console.log("XMLHttpRequest.prototype.sendAsBinary");
            var bb = new WebKitBlobBuilder();
            var data = new ArrayBuffer(1);
            var ui8a = new Uint8Array(data, 0);
            for (var i in datastr) {
                if (datastr.hasOwnProperty(i)) {
                    var chr = datastr[i];
                    var charcode = chr.charCodeAt(0)
                    var lowbyte = (charcode & 0xff)
                    ui8a[0] = lowbyte;
                    bb.append(data);
                }
            }
            var blob = bb.getBlob();
            this.send(blob);
}
        
function uploadAndSubmit() { 
	var form = document.forms["uploadUpgradePack"]; 
	$("#progress").css("display", "block");
	$("#uploadUpgradePack").css("padding-bottom", "0px");
	if (form["file"].files.length > 0) { 
		// 寻找表单域中的 <input type="file" ... /> 标签
		var file = form["file"].files[0]; 
		// try sending 
		var reader = new FileReader(); 
		
		reader.onloadstart = function() { 
			// 这个事件在读取开始时触发
			console.log("onloadstart"); 
			// var percentage = Math.round((event.loaded * 100) / event.total);
			// $("#done").css("width", "");
		} 
		reader.onprogress = function(p) { 
			// 这个事件在读取进行中定时触发
			console.log("onprogress");
			var percentage = Math.round((p.loaded * 100) / file.size);
			var width = 200 * percentage;
			$("#done").css("width", "width"+"px");
			document.getElementById("percentage").textContent = percentage + "%";
		}
		reader.onload = function() { 
		   // 这个事件在读取成功结束后触发
			console.log("load complete"); 
		} 
		
		reader.onloadend = function() { 
		   // 这个事件在读取结束后，无论成功或者失败都会触发
			if (reader.error) { 
				console.log(reader.error); 
			} else { 
				// 构造 XMLHttpRequest 对象，发送文件 Binary 数据
				var xhr = new XMLHttpRequest(); 
				xhr.open(/* method */ "POST", 
				/* target url */ "upgrade_file.php" 
				/*, async, default to true */); 
				xhr.overrideMimeType("application/octet-stream"); 
				xhr.sendAsBinary(reader.result); 
				xhr.onreadystatechange = function() { 
					if (xhr.readyState == 4) { 
						if (xhr.status == 200) { 
							console.log("upload complete"); 
							alert("系统升级成功，请重新启动服务器！");
						} else {
							console.log("upload complete:" + xhr.status);
							document.getElementById("percentage").textContent = "更新失败";
							alert("系统升级失败！");
						}
					} 
				} 
			} 
		} 
		
		reader.readAsBinaryString(file); 
	} else { 
		alert ("请选择文件！"); 
	} 
} 