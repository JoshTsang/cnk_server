<?php
    system("rm shopname");
	system("rm *.json");
	system("rm -rf upgrade");
	$res = system("tar -cf ../../cnk.bin orderPad -C ../../", $ret);
	echo $res;
	echo $ret;
	echo "<a href=\"../../cnk.bin\">download</a>";
	system("mkdir upgrade");
?>