<?php
    function get(){
 		$file =  "shopname";
 
		// 判断文件是否存在，不存在则创建
		if(file_exists($file)) {
			$content = file_get_contents($file);
		} else {
			$content = "菜脑壳电子点菜系统";
		}
		return $content;
	}
	
	$shopname = get();
	echo $shopname;
?>