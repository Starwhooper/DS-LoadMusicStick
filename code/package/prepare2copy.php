<?php
error_reporting(E_ALL);

include('functions.inc.php');
include('language.cfg.php');
include('config.inc.php');

//var_dump($para);

////////////////////////////////////////////////////////////////////////IN CASE OF OPTION "START TO COPY", that will you get in case you clicked on the button on the default page
if($para['option'] == ttt('Start to copy')){
	
	$jobscript = getcwd().'/log/jobscript_'.date('YmdGis').'.txt';

	////CANCEL IN CASE OF NO DESTINATION	
	if ($para['volume'] == NULL) {
		echo msg('*',$para['volume'],ttt('destination is not allowed, maybe you forgot to choose a destination or the Stick is not recognize from DS'),'red');
		continue;
	}
	else $destvolume = $para['volume'].'/';

//	foreach($para['folders'] as $folder) $dest .= $folder.', ';
	echo '<h1>'.ttt('startcopyprocess').'</h1><p><a href="index.php">'.ttt('back to startpage').'</a><small>'.ttt('please check progressinformation on startpage').'</small></p><h2>'.ttt('Protocol').'</h2>';

	if (!isset($para['folders'])){
		echo msg('*',$para['volume'],ttt('destination is not allowed, maybe you forgot to choose a destination or the Stick is not recognize from DS'),'red');
		continue;
	}

	if (!isset($para['exts'])){
		echo msg('*','*',ttt('no file extension choosen'),'red');
		continue;
	}
		
	//SAVE SETTINGS
	savesettings($settingsfile,$para);

	////LIST ALL USABLE FILES FROM SOURCE FOLDERS
	$query = "SELECT right(path,strpos(reverse(path),'/')-1) as filename, path, concat('/',split_part(path,'/',2),'/',split_part(path,'/',3),'/',split_part(path,'/',4)) as folderpath, lower(right(path,strpos(reverse(path),'.')-1)) as extension, filesize, genre FROM music ORDER by random()";
	$files = dbquery($query);

	////LIST ALL USABLE FILES FROM SOURCE FOLDERS
	//CREATE PHP SCRIPT TO LOAD STICK
	if($para['leftfreespace'] == 'true') $copyjobheader['leftfreespace'] = '$para[\'leftfreespacemb\'] = '.$para['leftfreespacemb'].';';


	if ($para['CD16'] == true) {
		for($i=1;$i<=6;$i++) $copyjobscript[] = 'if (is_dir("'.$destvolume.'CD0'.$i.'") == false) mkdir("'.$destvolume.'CD0'.$i.'");';
	}
	$i = 0;

	foreach ($files as $key => $file){
		
		$destionationfile = $destinationfolder = NULL;
		
		if(!in_array($file['folderpath'],$para['folders'])) continue;
		if(!in_array($file['genre'],$para['genres'])) continue;
		if(!in_array($file['extension'],$para['exts'])) continue;
		
		$j=0;
		
		if ($para['CD16'] == true and $j == 50) continue(2);
//		echo $usablespaceofonstick.':'.$file['filesize']."<br>";
		
//		if($usablespaceofonstick) < $file['filesize']) {
//			if ($para['baftbf'] == true) continue(2);
//			continue;
//		}
		
		$destionationfile = $file['filename'];

		$destionationfile = str_pad($key+1, 5, 0, STR_PAD_LEFT).'_'.$destionationfile;

		if ($para['CD16'] == true) {
			$i++;
			$destinationfolder = 'CD0'.$i.'/';
			if ($i == 6) {
				$i = 0;
				$j++;
			}
		}
		elseif ($para['differbygenre'] == true) {
			if ($file['genre'] == '') $file['genre'] = '_none';
			$copyjobscript[] = 'if (is_dir("'.$destvolume.$file['genre'].'") == false) mkdir("'.$destvolume.$file['genre'].'");';
			$destinationfolder = $file['genre'].'/';
		}
		else $destinationfolder = NULL;

		if ($file['extension'] != 'mp3' and $para['convert2mp3'] == true) {
			if(isset($para['bitrate']) and $para['bitrate'] != 'no') $bitrate='-b:a'.$para['bitrate'].'k';
			else $bitrate = NULL;
			$newfilename .= '.mp3';
			//$copyjobscript[] = 'exec("/usr/syno/bin/ffmpeg -n -i '.escapeshellarg($file['path']).' '.$bitrate.' '.escapeshellarg($destvolume.'/'.$destinationfolder.'/'.$destionationfile).'.mp3")';
			$copyjobscript[] = 'exec("/usr/syno/bin/ffmpeg -n -i '.$file['path'].' '.$bitrate.' '.$destvolume.$destinationfolder.$destionationfile.'.mp3");';
//			$script[] = 'echo $(date +%Y-%m-%d\ %H:%M:%S)'.escapeshellarg($destvolume.'/'.$newfilename).' done (convert)'."\n".'>>'.$shellscriptlog;
//			$output .= '<li>'.ttt('Added to job to convert').': '.$file['path'].' to '.$destvolume.'/'.$newfilename.'</li>';
			$j++;
		}
		else {
//			$copyjobscript[] = 'copy '.escapeshellarg($file['path']).' '.escapeshellarg($destvolume.'/'.$newfilename);
			$copyjobscript[] = 'if (disk_free_space("'.$destvolume.'") >= '.$file['filesize'].') copy("'.$file['path'].'","'.$destvolume.$destinationfolder.$destionationfile.'");';
//			$script[] = 'echo $(date +%Y-%m-%d\ %H:%M:%S)'.escapeshellarg($destvolume.'/'.$newfilename).' done'."\n".'>>'.$shellscriptlog;
//			$usablespaceofonstick = $usablespaceofonstick - $file['filesize'];
//			$output .= '<li>'.ttt('Added to job').': '.$file['path'].' to '.$destvolume.'/'.$newfilename.'</li>';
			$j++;
		}
	}

//	$copyjobscript[] = 'rm -f '.$shellscript;


	$handler = fopen($jobscript, 'w');
	foreach($copyjobheader as $line) fwrite($handler , $line."\n");
	foreach($copyjobscript as $line) fwrite($handler , $line."\n");
	fclose($handler);

	//$cmd = 'chmod 777 '.$shellscript.' && sh '.$shellscript.' >/dev/null 2>/dev/null &';
//	$cmd = 'php copy.php '.$jobscript.' > /dev/null &';
	$cmd = 'php copy.php '.$jobscript.' > '.$jobscript.'.log';
	exec($cmd);

	echo ttt('work_in_progress').': '.$jobscript;	
//	echo $output;	
	

}

?>