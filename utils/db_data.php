<?php
    require '../macros.php';
	if (!isset($_GET['db']) || !isset($_GET['table'])) {
		die("err");
	}
	
	$dbName = $_GET['db'];
	$table = $_GET['table'];
	echo "<span id=\"dbTitle\">$table@$dbName</span>";
	if ($dbName == "order") {
		$dbName = "../".DATABASE_ORDER;
	} else if($dbName == "sales"){
		$dbName = "../".DATABASE_SALES;
	} else {
		die("err");
	}
?>
<table class="db_data_table" id="tablePageId">
	<thead>
		<tr>

<?php	
	$db = new SQLite3($dbName);
	$sql = "select * from $table";
	$reslut = $db->query($sql) or die("Error in query: <span style='color:red;'>$sql</span>");
	$num_columns = $reslut->numColumns();
	for ($i=0; $i<$num_columns; $i++) {
		echo "<th>".$reslut->columnName($i)."</th>";
	}
	echo "</thead></tr><tbody id=\"printList\">";
	
	while($row = $reslut->fetchArray()) {
		echo "<tr>";
		for ($i=0; $i<$num_columns; $i++) {
			echo "<td>".$row[$i]."</td>";
		}
		echo "</tr>";
	}
	$db->close();
?>

	</tbody>
</table>