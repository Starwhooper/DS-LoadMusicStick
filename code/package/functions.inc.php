<?php

function dbquery($query){
	exec ('psql -d mediaserver -U postgres -c "'.$query.'"',$cmddump);
	$i=0;
	foreach($cmddump as $line){
		if (substr($line,0,1) == '(') continue;
		if (trim(strlen($line)) <= 1) continue;
		$i++;
		if ($i == 1){
			$colums = explode('|',$line);
			$j=0;
			foreach($colums as $col){
				$structure[$j]['title'] = trim($col);
				$j++;
			}
		}
		elseif ($i == 2){
			$colums = explode('+',$line);
			$j=0;
			foreach($colums as $col){
				$structure[$j]['length'] = strlen($col);
				$j++;
			}
		}
		else {
			foreach($structure as $field){
				$result[$i-2][$field['title']] = trim(substr($line,0,$field['length']));
				$line = substr($line,$field['length'] + 1);
			}
		}
	}
	return($result);

}

	

////translate textstrings
function ttt($string){
	global $userdetails;
	global $lang;
	
	$string = strtolower($string);

	if (isset($lang[$userdetails['language']][$string])) $output = $lang[$userdetails['language']][$string];
	elseif (isset($lang['eng'][$string])) $output = $lang['eng'][$string];
	else $output = '<strong>___'.strtoupper(str_replace(' ','_',$string)).'___</strong>';

	return($output);
}

//// check possible destinations
function checkdestinations(){
	exec('df -h|grep /volume',$cmddump);
	foreach($cmddump as $line){
		$folder = trim(substr($line,strrpos($line,' ')));
		if(strlen($folder) <= 8) continue;
		$freegb = round(disk_free_space($folder) / 1024 / 1024 / 1024,1);
		$totalgb = round(disk_total_space($folder) / 1024 / 1024 / 1024,1);
		$freegrade = round(100 / $totalgb * $freegb);
		$writable = is_writable($folder);
		
		$drives[$folder] = array('freegb' => $freegb, 'totalgb' => $totalgb, 'freegrade' => $freegrade, 'writable' => $writable);
	}
	return($drives);
}

function msg($sourse,$destination,$reason,$color){
	$output .= '<h3> '. ttt('File') .' '.$sourse.'</h3><p><font color="'.$color.'">';
	if ($color == 'red') $output .= ' '.ttt('could not copy');
	if ($color == 'green') $output .= ttt('was copied');
	if ($destination != NULL) $output .= ' '.ttt('to').' <strong>'.$destination.'</strong>';
	$output .= ' '.ttt('in reason of').' <strong>'.$reason.'</strong></font></p>'."\n";

	return($output);
}

function sysdetails(){
	foreach(file('/etc.defaults/VERSION') as $line) {
		$line = explode('=',$line);
		$version[$line[0]] = substr(trim($line[1]),1,-1);
	}
	$output['dsmversion'] = $version;
	
	return($output);
}

function userdetails(){
	//get username
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif (isset($_SERVER['HTTP_X_REAL_IP'])) $clientIP = $_SERVER['HTTP_X_REAL_IP'];
	else $clientIP = $_SERVER['REMOTE_ADDR'];
	putenv('HTTP_COOKIE='.$_SERVER['HTTP_COOKIE']);
	putenv('REMOTE_ADDR='.$clientIP);
	$login = shell_exec("/usr/syno/synoman/webman/login.cgi");
	preg_match('/\"SynoToken\"\s*?:\s*?\"(.*)\"/',$login,$synotoken);
	$synotoken = trim($synotoken[1]);
	putenv('QUERY_STRING=SynoToken='.$synotoken); 
	$output['username'] = exec("/usr/syno/synoman/webman/modules/authenticate.cgi");
	
	//get language
	$json='/usr/syno/etc/preference/'.$output['username'].'/usersettings';
	if(file_exists($json)){
		$obj=json_decode(file_get_contents($json));
		$output['language'] = $obj->Personal->lang;
	}
	else $output['language'] = 'eng';

	return($output);
}

function loadsettings($settingsfile){
	$lines = file($settingsfile);
	foreach($lines as $line) {
		$line = explode('=',$line);
		$output[trim($line[0])][] = trim($line[1]);
	}
	
	return($output);
}

function savesettings($settingsfile,$para){
	unlink($settingsfile);
	foreach($para['folders'] as $folder) file_put_contents($settingsfile,'selectedfolder='.$folder."\n",FILE_APPEND);
	foreach($para['genres'] as $genre) file_put_contents($settingsfile,'selectedgenre='.$genre."\n",FILE_APPEND);
}

function sendmail($subject,$message){
	$smtpconfig = parse_ini_file('/usr/syno/etc/synosmtp.conf');
	$subject = $smtpconfig['eventsubjectprefix'].' '.$subject;
	$message = $message;
	$hostname= gethostname();
	$to = $smtpconfig['eventmail1'].','.$smtpconfig['eventmail2'];
	$header = 'From: Load Music Stick <'.$smtpconfig['eventuser'].'>' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
	mail ($to, $subject, $message, $header);
}
?>