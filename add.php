<?php
header("Content-type: text/html; charset=utf-8");
session_start();
include 'sae.php';

$mysql = ConnectMysqli::getIntance();

function upload()
{
    // 允许上传的图片后缀
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    $temp = explode(".", $_FILES["file"]["name"]);
	echo $temp;

    $extension = end($temp);     // 获取文件后缀名
    if ((($_FILES["file"]["type"] == "image/gif")
            || ($_FILES["file"]["type"] == "image/jpeg")
            || ($_FILES["file"]["type"] == "image/jpg")
            || ($_FILES["file"]["type"] == "image/pjpeg")
            || ($_FILES["file"]["type"] == "image/x-png")
            || ($_FILES["file"]["type"] == "image/png"))
        && ($_FILES["file"]["size"] < 4 * 1024 * 1024)   // 小于 4 mb
        && in_array($extension, $allowedExts)) {
        if ($_FILES["file"]["error"] > 0) {
            echo "<script>alert('" . $_FILES["file"]["error"] . "');</script>";
            exit;
        } else {
            $file_name = date('YmdHis', time()) . '.' . $extension;
            if (file_exists("pic/" . $file_name)) {
                echo "<script>alert('" . $_FILES["file"]["name"] . " 文件已经存在 " . "');</script>";
                exit;
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], "pic/" . $file_name);
                return "/pic/" . $file_name;
            }
        }
    } else {
        echo "<script>alert('不支持的格式或图片尺寸不能大于4MB');history.go(-1);</script>";
        exit;
    }
}

if ($_GET['action'] == 'edit' && $_GET['id']) {
    $data = $mysql->getAll('SELECT * FROM `data` where id=' . $_GET['id']);
    $data = $data[0];
    $text = '编辑花型';
    $act = "update&id=" . $_GET['id']."&file=" . $data['file'];	
	
} else {
    $act = "add";
    $text = '添加花型';
}


if ($_POST && $_GET['action'] == 'update') {
	echo $file;
    $str = "id=" . $_GET['id'];
    //图片上传
    if ($_FILES["file"]['name']) {
        $_POST['file'] = upload();
    }else{
		$_POST['file'] = $_GET['file'];
	}

    foreach ($_POST as $key => $val) {
		if($key != "pic"){
			$str .= ",{$key} = '{$val}'";
		}	
    }
    $sql = "UPDATE data SET " . $str . " where id=" . $_GET['id'];
	//echo " sql:".$sql."<br> file:".$_POST['file']."<br> pic:".$_POST['pic']."<br> hxh:".$_POST['hxh'];
	
    if (!$mysql->query($sql)) {
        die("Error: mysql错误");
    }
	if($_POST['pic'] != ""){
		$sql = "UPDATE data SET file = '" .$_POST['file']. "' WHERE hxh = '" .$_POST['hxh']. "'";
		//echo $sql;
		$mysql->query($sql);			
	}	
    echo "<script>alert('编辑成功');history.go(-1);</script>";
    exit;
}
if ($_GET['action'] == 'add_') {
    if (!empty($_SESSION['sql'])) {
        var_dump($_SESSION['sql']);
        if (!$mysql->query($_SESSION['sql'])) {
            die("Error: mysql错误");
        }
		
		//应用到相同花型号
		if($_SESSION['pic'] != ""){
			$sql = "UPDATE data SET file = '" .$_SESSION['file']. "' WHERE hxh = '" .$_SESSION['hxh']. "'";
			//echo $sql;
			$mysql->query($sql);
		}
        unset($_SESSION['sql']);
		unset($_SESSION['pic']);
        echo "<script>alert('添加成功');history.go(-1);</script>";
        exit;

    } else {
        echo "<script>alert('没有SESSION，添加失败');history.go(-1);</script>";
    }

}

if ($_POST && $_GET['action'] == 'add') {
    if (empty($_POST['hxh']) || (empty($_POST['hlcm']) && empty($_POST['hlinch']))) {
        echo "<script>alert('至少填写花型号和一个横列！');history.go(-1);</script>";
        exit;
    }

    //图片上传
    if ($_FILES["file"]['name']) {
        $_POST['file'] = upload();		
    }

    $data = $mysql->getAll("SELECT * FROM `data` where hxh='" . $_POST['hxh'] . "' and hlcm = '" . $_POST['hlcm'] . "' and jqh = '" . $_POST['jqh'] . "'");

    // $sql = "INSERT  INTO `data` " . array_to_sql($_POST);
	// echo " ADD:".$sql."<br>";
	
	$sql = "INSERT  INTO `data` set hxh='".$_POST['hxh']."',hxhl='".$_POST['hxhl']."',hxmc='".$_POST['hxmc']."',jqh='".$_POST['jqh']."',hlcm='".$_POST['hlcm']."',hlinch='".$_POST['hlinch']."',gb1='".$_POST['gb1']."',gb2='".$_POST['gb2']."',gb3='".$_POST['gb3']."',file='".$_POST['file']."'";
	
	//echo " ADD:".$sql."<br> pic:".$_POST['pic'];
	
    if (empty($data)) {
        if (!$mysql->query($sql)) {
            die("Error: mysql错误");
        }
		if($_POST['pic'] != ""){
			$sql = "UPDATE data SET file = '" .$_POST['file']. "' WHERE hxh = '" .$_POST['hxh']. "'";
			//echo $sql;
			$mysql->query($sql);			
		}
        echo "<script>alert('添加成功');history.go(-1);</script>";
        exit;

    } else {
        $_SESSION['sql'] = $sql;
		$_SESSION['pic'] = $_POST['pic'];
		$_SESSION['hxh'] = $_POST['hxh'];
		$_SESSION['file'] = $_POST['file'];
        echo "<script>
             if(window.confirm('" . $_POST['hxh'] . "\u000d" . $_POST['jqh'] . "\u000d" . $_POST['hlcm'] . "\u000d该花型已存在，是否仍然添加？')){
                 location.href='?action=add_';
             }else{
             	history.go(-1);
             }
             </script>
             ";
    }

}


?>


<html>
<head>
    <meta name="viewport"
          content="width=device-width, initial-scale=0.7, user-scalable=yes, minimum-scale=0.7, maximum-scale=3.0"/>
    <meta charset="utf-8">
    <title><?php echo $text; ?></title>

    <link rel="stylesheet" href="/css/amazeui.min.css">

    <link rel="shortcut icon" href="/images/favicon.ico"/>
    <link rel="icon" sizes="32x32" href="/images/favicon.ico">
    <link rel="Bookmark" href="/images/favicon.ico"/>

</head>
<body>
<form class="am-form" action="?action=<?php echo $act; ?>" method="POST" enctype="multipart/form-data">
    <fieldset>
        <legend style="text-align: center;">
            <a href="search.php"><span style="font-size:22px;">查找花型</span></a>&nbsp; &nbsp; &nbsp;
            <a href="/chilun/index.html" target="_blank"><span style="font-size:22px;">齿轮计算</span></a>&nbsp; &nbsp; &nbsp;
            <a href="/boma/index.html" target="_blank"><span style="font-size:22px;">拨码查询</span></a>&nbsp; &nbsp; &nbsp;
            <a href="/jingzhou/index.html" target="_blank"><span style="font-size:22px;">经轴计算</span></a>
        </legend>
        <div class="am-form-group">
            <label style="text-align: center;">花型号</label>
            <input type="text" name="hxh" class="" placeholder="" value="<?php echo $data['hxh']; ?>">
        </div>

        <div class="am-form-group">
            <label style="text-align: center;">横列/张数</label>
            <input type="text" name="hxhl" class="" placeholder="张数/横列数" value="<?php echo $data['hxhl']; ?>">
        </div>

        <div class="am-form-group">
            <label style="text-align: center;">花型描述</label>
            <input type="text" name="hxmc" class="" placeholder="4针距" value="<?php echo $data['hxmc']; ?>">
        </div>

        <div class="am-form-group">
            <label style="text-align: center;">机器号</label>
            <input type="text" name="jqh" class="" placeholder="16-1" value="<?php echo $data['jqh']; ?>">
        </div>
        <div class="am-form-group">
            <label style="text-align: center;">横列/CM</label>
            <input type="text" name="hlcm" class="" placeholder="公分" value="<?php echo $data['hlcm']; ?>">
        </div>

        <div class="am-form-group">
            <label style="text-align: center;">横列/IN</label>
            <input type="text" name="hlinch" class="" placeholder="英寸" value="<?php echo $data['hlinch']; ?>">
        </div>

        <div class="am-form-group">
            <label style="text-align: center;">GB1</label>
            <input type="text" name="gb1" class="" placeholder="根据织物填写！" value="<?php echo $data['gb1']; ?>">
        </div>


        <div class="am-form-group">
            <label style="text-align: center;">GB2</label>
            <input type="text" name="gb2" class="" placeholder="根据织物填写！" value="<?php echo $data['gb2']; ?>">
        </div>


        <div class="am-form-group">
            <label style="text-align: center;">GB3</label>
            <input type="text" name="gb3" class="" placeholder="根据织物填写！" value="<?php echo $data['gb3']; ?>">
        </div>
        <div class="am-form-group">
            <label style="text-align: center;">花型图片 (≤4MB)</label>
            <input type="file" name="file" style="display: inline-block">
            <?php if ($data['file']) echo '<img src=".' . $data['file'] . '" width="100" height="100">' ?>
        </div>

        <div class="am-form-group">
            <label style="font-size:14px;"><input type="checkbox" name="pic" value="1"> 图片应用到所有相同花型号</label>
        </div>
		
        <p>
            <button type="submit" class="am-btn am-btn-default"><?php echo $text; ?></button>
        </p>

    </fieldset>
</form>


</body>
</html>