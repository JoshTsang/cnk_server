var table = Array();
// var dishes = Array();
var printers;
var showPart;
var printerIds = [];

$('#todoDishes').live('pageinit',function(event){
	console.log("initCalled");
	loadSetting();
	getTableInfo();
});
$('#setting').live('pagebeforecreate',function(event){
	loadPrinterSetting();
});

function getTableInfo() {
	$.ajax({
		url: "../kitchen_table.php",
		dataType: "json",
		success: function(response) {
			$("#popupErr").popup("close");
			$.each(response, function(index, result) {
				table[result.id] = result.name;
			});
			refreshTodo();
			automaticUpdate();
		},
		error: function() {
			console.log("err");
			$("#popupErr").popup("open");
		}
	});
}

function automaticUpdate() {
	refreshTodo();
	setTimeout("automaticUpdate()", 5000);
}

function startCooking(id, unitName, dishName, num, table) {
	var url = "../kitchen.php?id="+id+"&flag=" + (unitName=="斤"?1:0);
	console.log(url);
	$("#nowCooking").html(dishName + " * " + num + unitName + " -> " + table);
	$.ajax({
		url: url,
		dataType: "json",
		success: function(response) {
			$("#popupErr").popup("close");
			updateTodoListView(response);
		},
		error: function() {
			$("#popupErr").popup("open");
		}
	});
}

function refreshTodo() {
	$.ajax({
		url: "../kitchen.php",
		dataType: "json",
		success: function(response) {
			$("#popupErr").popup("close");
			updateTodoListView(response);
		},
		error: function() {
			$("#popupErr").popup("open");
		}
	});
}

function updateTodoListView(data) {
	var markup = "";
	$.each(data, function(index, result) {
		if (showPart == "true") {
			var ret = isSelectedPrinter(result.displayCate);
			if (ret == false) {
				return;
			}
		}
		var $template = $('<div><li id="dish"><span class="dishInfo"></span><span class="ui-li-count tableName"></span></li></div>');
		$template.find("#dish").attr("onclick", "javascript:startCooking(" + 
					result.id + ",'" + result.unitName + "','" + result.dishName + "'," + result.num + "," + table[result.tid] + ")");
	    // dishes.push(result);
		$template.find(".dishInfo").append(result.dishName + " * "+ result.num + result.unitName);
		$template.find(".tableName").append(table[result.tid]);
		markup += $template.html();
	});
	$('#dishes').html("");
	$('#dishes').append(markup).listview("refresh", true);
}

function loadPrinterSetting() {
	$.ajax({
		url: "../setting/getPrinterSetting.php",
		dataType: "json",
		success: function(response) {
			printers = response;
			loadSetting();
			showPrinterOption();
		},
		error: function() {
		}
	});
}

function showPrinterOption() {
	var markup = "";
	if (showPart == "true") {
		console.log("toggleOn");
		var myswitch = $("#slider");
		myswitch[0].selectedIndex = 1;
		myswitch.slider("refresh");
	}
	
	$.each(printers, function(index, printer) {
		if (printer.id != 0 && printer.usefor != 200) {
			var $template= $('<div><input type="checkbox" class="custom" /><label></label></div>');
			$template.find("input").attr("id", printer.id);
			$template.find("input").attr("name", printer.id);
			$template.find("label").attr("for", printer.id);
			$template.find("label").html(printer.name);
			markup += $template.html() + "\n";
		}
	});
	var $checkboxes = '<fieldset data-role="controlgroup"><legend>分单设置：</legend>\n' + markup + '</fieldset>';
	
	console.log($checkboxes);
	
	$("#choosePrinter").html($checkboxes).trigger('create');
	$.each(printerIds, function(index, id) {
		$('#'+id).prop("checked", true).checkboxradio("refresh");
	});
}

function isSelectedPrinter(printerId) {
	console.log("pLen:" + printerIds.length);
	for (var i=0; i < printerIds.length; i++) {
		if (Number(printerIds[i]) == Number(printerId)) {
			return true;
		}
	};
	
	return false;
}

function saveSetting() {
	showPart = $("#slider option:selected").attr("value")=="on"?true:false;
	localStorage.showPart = showPart;
	
	printIds = [];
	$.each(printers, function(index, printer) {
		if (printer.id != 0 && printer.usefor != 200) {
			if ($('#'+printer.id).prop("checked") == true) {
				printIds.push(printer.id);
			}
		}
	});
	localStorage.printers = JSON.stringify(printIds);
	
	console.log("printers:" + localStorage.printers);
	refreshTodo();
}

function loadSetting() {
	if (localStorage.showPart)
	{
		showPart = localStorage.showPart;
	} else {
		showPart = false;
	}
	console.log("showPart:" + localStorage.showPart);
	
	printerIds = [];
	if (localStorage.printers) {
		var obj = jQuery.parseJSON(localStorage.printers);
		$.each(obj, function(index, printer) {
			printerIds.push(printer);
			console.log("printer:" + printer);
		});
	}
	console.log("saved printer:" + localStorage.printers);
}
