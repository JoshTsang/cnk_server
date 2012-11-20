<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/json.js"></script>
<script type="text/javascript" src="js/saveConfig.js"></script>
<title>菜脑壳电子点菜系统-安装</title>
</head>
<?php
    if (isset($_GET['step'])) {
        $step = $_GET['step'];
    } else {
        $step = 1;
    }
    
    function getDirFiles($dir)   
    {   
        if ($handle = opendir($dir)){   
            while (false !== ($file = readdir($handle))) {   
                if ($file != "." && $file != "..") {
                    $files[]=$file;
                }
             }   
        }
        
        closedir($handle);   
        if(isset($files)) {
            return $files;   
        } else {
          return false;      
        }
    }  

    function showMenuChoices() {
        $menus = getDirFiles("../data/");
        echo "选择需要加载的菜谱： ";
        if ($menus) {
            echo '<select id="menu">';
            foreach ($menus as $key => $menu) {
                echo '<option value="'.$menu.'">'.$menu.'</option>';
            }
            echo '</select>';
        } else {
            echo "没有找到菜谱文件";
        }
    }
    
    function showPadNumSetting() {
        echo '<form name="input">'.
                    '授权Pad数量：'.
                    '<input type="text" name="padNum" id="padNum" value="10"/>'.
             '</form>';
    }
    
    function showPrinterChoices() {
        echo "选择需要加载的打印机配置文件： ";
        $printers = getDirFiles("../config/printer");
        if ($printers) {
            echo '<select id="printer">';
            foreach ($printers as $key => $printer) {
                echo '<option value="'.$printer.'">'.$printer.'</option>';
            }
            echo '</select>';
        } else {
            echo "没有找到打印机配置文件";
        }
    }
    
    function showFinish() {
        echo "请点击确定，完成菜脑壳电子点菜系统安装";    
    }
?>
<body style="position: relative;">
	<div class="header">
		<div class="logo">
		<h1>cainaoke</h1>
		<span>--科技让生活更简单！</span>
		</div>
        <div class="navbar">安装菜脑壳服务器</div>
	</div>
	<div class="content">
	    <div class="progress">
    		<div class="progress_bar">
    		    <div class="item">
    		        <em class="icon <?php if($step >= 1) echo "complete"; ?>"></em>
    		        <span class="line <?php if($step >= 2) echo "complete"; ?>"></span>
    		    </div>
                <div class="item">
                    <em class="icon <?php if($step >= 2) echo "complete"; ?>"></em>
                    <span class="line <?php if($step >= 3) echo "complete"; ?>""></span>
                </div>
                <div class="item">
                    <em class="icon <?php if($step >= 3) echo "complete"; ?>"></em>
                    <span class="line <?php if($step >= 4) echo "complete"; ?>""></span>
                </div>
                <div class="item">
                    <em class="icon <?php if($step >= 4) echo "complete"; ?>"></em>
                </div>
    		</div>
            <div class="progress_text">
                    <div class="text">菜谱选择</div>
                    <div class="text">授权设置</div>
                    <div class="text">打印设置</div>
                    <div class="text">完成安装</div>
            </div>
		</div>
		<div class="setting">
            <div class="setting_title_bar">
                <span id="setting_title">
                    <?php
                        switch($step) {
                            case 1:
                                echo "选择菜谱";
                                break;
                            case 2:
                                echo "授权设置";
                                break;
                            case 3:
                                echo "打印设置";
                                break;
                            case 4:
                                echo "完成安装";
                                break;
                            default:
                        }
                    ?>
                </span>
                <span id="save_btn">
                    <?php
                        $onClickLisener = ""; 
                        switch($step) {
                            case 1:
                                $onClickLisener = "loadMenu($step)";
                                break;
                            case 2:
                                $onClickLisener = "savePadNum($step)";
                                break;
                            case 3:
                                $onClickLisener = "loadPrinterConfig($step)";
                                break;
                            case 4:
                                $onClickLisener = "finish($step)";
                                break;
                            default:
                        }
                        echo '<a href="javascript:void(0);" onclick="'.$onClickLisener.';">[确定]</a>';
                    ?>
               </span>
            </div>
            <div class="setting_content">
                <?php
                    switch($step) {
                            case 1:
                                showMenuChoices();
                                break;
                            case 2:
                                showPadNumSetting();
                                break;
                            case 3:
                                showPrinterChoices();
                                break;
                            case 4:
                                showFinish();
                                break;
                            default:
                        }
                ?>
            </div>
       </div>
	</div>
</body>
</html>