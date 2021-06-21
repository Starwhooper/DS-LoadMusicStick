<?php
////SET FIX VARIABLES
$para = 			$_GET;
$settingsfile = 	'loadmusicstick.setting';
$settings =			loadsettings($settingsfile);
$userdetails = 		userdetails();
$sysdetails = 		sysdetails();
//$timestamp = date('YmdGis');

if ($sysdetails['dsmversion']['majorversion'] == 6) $approot = substr($_SERVER["REQUEST_URI"],0,strrpos($_SERVER['REQUEST_URI'],'/'));
else $approot = 			substr($_SERVER['REDIRECT_SCRIPT_URI'],0,strrpos($_SERVER['REDIRECT_SCRIPT_URI'],'/'));

//$dsmurl = substr($_SERVER['SCRIPT_URI'],0,strpos($_SERVER['SCRIPT_URI'],'/',10));
//
//$shellscript = '/tmp/lms.copy.'.$timestamp.'.sh';
//$shellscriptlog = getcwd().'/log/lms.copy.'.$timestamp.'.sh.log.html';
if ($userdetails['username'] === '') exit("403 Forbidden");
$allowedbitrate = array(96,128,192,256,320);
?>