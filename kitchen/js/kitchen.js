var table = Array();

$(document).bind('pageinit', function() {
	getTableInfo();
});

function getTableInfo() {
	$.ajax({
		url: "../kitchen_table.php",
		dataType: "json",
		success: function(response) {
			console.log(response);
			$.each(response, function(index, result) {
				table[result.id] = result.name;
			});
			refreshTodo();
			automaticUpdate();
		},
		error: function() {
			alert("很明显，出错了");
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
			updateTodoListView(response);
		},
		error: function() {
			alert("很明显，出错了");
		}
	});
}

function refreshTodo() {
	$.ajax({
		url: "../kitchen.php",
		dataType: "json",
		success: function(response) {
			updateTodoListView(response);
		},
		error: function() {
			alert("很明显，出错了");
		}
	});
}

function updateTodoListView(data) {
	var markup = "";
	$.each(data, function(index, result) {
		var $template = $('<div><li id="dish"><span class="dishInfo"></span><span class="ui-li-count tableName"></span></li></div>');
		$template.find("#dish").attr("onclick", "javascript:startCooking(" + 
					result.id + ",'" + result.unitName + "','" + result.dishName + "'," + result.num + "," + table[result.tid] + ")");
		$template.find(".dishInfo").append(result.dishName + " * "+ result.num + result.unitName);
		$template.find(".tableName").append(table[result.tid]);
		markup += $template.html();
	});
	console.log(markup);
	$('#dishes').html("");
	$('#dishes').append(markup).listview("refresh", true);
}
