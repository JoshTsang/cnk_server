<?php
    exec("ls *.apk", $str, $res);
	if ($res == 0) {
		$verStart = strpos($str[0], "ver") + 3;
		$versionInfo = array('ver' => substr($str[0], $verStart, -4),
							 'name' => $str[0] );
		echo json_encode($versionInfo);
	}
?>