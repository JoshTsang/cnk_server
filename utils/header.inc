<div class="header">
	<div class="logo">
	<h1>cainaoke</h1>
	<span>--科技让生活更简单！</span>
	</div>
	<div class="navbar">
		<ul>
		<?php
			$filename = basename($_SERVER['PHP_SELF']);
			if ($filename == "shell.php") {
			    echo "<li><a href=\"log.php\">LOG</a></li>";
				echo "<li><a href=\"db.php\">数据库</a></li>";
                echo "<li><a href=\"pad_setting.php\">Pad设置</a></li>";
				echo "<li><a href=\"#\" id=\"currentPage\">Shell</a></li>";
			} else if ($filename == "db.php"){
                echo "<li><a href=\"log.php\">LOG</a></li>";
				echo "<li><a href=\"#\" id=\"currentPage\">数据库</a></li>";
                echo "<li><a href=\"pad_setting.php\">Pad设置</a></li>";
				echo "<li><a href=\"shell.php\">Shell</a></li>";
            } else if ($filename == "log.php") {
                echo "<li><a href=\"#\">LOG</a></li>";
                echo "<li><a href=\"db.php\">数据库</a></li>";
                echo "<li><a href=\"#\" id=\"currentPage\">Pad设置</a></li>";
                echo "<li><a href=\"shell.php\">Shell</a></li>";
			} else {
                echo "<li><a href=\"log.php\">LOG</a></li>";
                echo "<li><a href=\"db.php\">数据库</a></li>";
                echo "<li><a href=\"pad_setting.php\" id=\"currentPage\">Pad设置</a></li>";
                echo "<li><a href=\"shell.php\">Shell</a></li>";
			}
		?>
		<li><a href="../setting/index.php">打印机</a></li>
		<li><a href="../setting/getUpgradePack.php">获得升级包</a></li>
		</ul>
	</div>
</div>