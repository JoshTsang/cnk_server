<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/padSetting.js"></script>
<title>调试工具</title>
</head>
<body style="position: relative;">
<?php
    require("../macros.php");
    include 'header.inc';
?>
    <div class="content">
        <div class="content_page clearfix">
            <div class="menu_bar">
                <table class="menu_bar" id="menu">
                    <thead>
                        <tr><th>Pad设置</th></tr>
                    </thead>
                    <tbody>
                    <tr onclick="#"><td>木有了</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="db_data" id="content_data">
                <form name="input" action="javascript:save();" method="get">
                    <input type="text" name="padNum" id="padNum" value="<?php @$ret = file_get_contents("../".LISENCE_CONF);echo $ret;?>"/>
                    <input type="submit" value="保存" /><br/>
                </form>
            </div>
        </div>
    </div>

</body>
</html>