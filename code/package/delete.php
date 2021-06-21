<?php
error_reporting(E_ALL);

include('functions.inc.php');
include('language.cfg.php');
include('config.inc.php');


if(isset($para['drive'])){
	$drive = $para['drive'];
	if(file_exists($drive)){
//		if (in_array($drive,$allowedusbdrives)){
			$cmd = 'find '.$drive;
			exec($cmd,$cmddump);
			echo '<form action="delete.php">';
			foreach($cmddump as $file){
				if(strlen($file) <= strlen($drive) + 1) continue;
				echo '<input type="checkbox" name="files[]" value="'.$file.'"  checked="checked">'.$file.'</br>';
			}
			echo '<input type="submit" value="delete"></form>';
//		}
//		else exit('no allowed drive');
	}
	else exit(ttt('no exist drive'));
}
elseif(isset($para['files'])){
	$files = $para['files'];
//	var_dump($files);
	foreach($files as $file){
		unlink($file);
		echo 'delete: '.$file.'<br/>';
	}
}
else exit('no selected drive');
?>