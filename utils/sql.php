<table class="db_data_table" id="tablePageId">
	<thead>
		<tr>
<?php
    require '../macros.php';
	if(!isset($_POST['sql']) || !isset($_GET['db'])) {
		die("sql?");
	}
	$sql = $_POST['sql'];
	$dbName = $_GET['db'];
	
	if ($dbName == "order") {
		$dbName = "../".DATABASE_ORDER;
	} else if($dbName == "sales"){
		$dbName = "../".DATABASE_SALES;
	} else {
		die("unknown db");
	}
	
	$db = new SQLite3($dbName);
	$results = $db->query($sql) or die("query failed, sql:".$sql);
	$num_columns = $results->numColumns();
	for ($i=0; $i<$num_columns; $i++) {
		echo "<th>".$results->columnName($i)."</th>";
	}
	echo "</thead></tr><tbody id=\"printList\">";
	
	while($row = $results->fetchArray()) {
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