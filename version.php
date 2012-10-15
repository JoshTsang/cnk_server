<?php
	require('macros.php');
	
	$db=new SQLite3(DATABASE_MENU);
	if (!$db) {
	  die(ERR_COULD_NOT_CONECT_DB);
	}
	
	$rs=$db->query('select * from  version');
	if ($rs) {
		if ($row=$rs->fetchArray()) {
			$version=$row['version'];
			echo "[$version]";
			$db->close();
			die(0);
		}
		echo "[1]";
	} else {
		echo "query failed";
		$db->close();
		die(ERR_DB_QUERY);
	}
?>