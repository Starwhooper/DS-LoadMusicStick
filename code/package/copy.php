<?php
include('functions.inc.php');
include('language.cfg.php');
include('config.inc.php');

$lines = file($argv[1]);

foreach($lines as $line) {
	eval($line);
}

sendmail(ttt('done'),ttt('done'));
?>