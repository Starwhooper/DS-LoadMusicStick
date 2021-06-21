<?php
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
////        YOU NEED TO INSTALL "Init 3rdparty" on your DS.       ////
////         http://www.cphub.net/index.php?id=40&pid=293         ////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////

error_reporting(E_ALL);

include('functions.inc.php');
include('language.cfg.php');
include('config.inc.php');

//phpinfo();


////OUTPUT HEADER
echo '<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<link rel="stylesheet" href="format.css" type="text/css">';

echo '	<script type="text/javascript" language="JavaScript">
			function togglefolders(source) {
				checkboxes = document.getElementsByName(\'folders[]\');
				for(var i=0, n=checkboxes.length;i<n;i++) {
					checkboxes[i].checked = source.checked;
				}
			}			
			function togglegenres(source) {
				checkboxes = document.getElementsByName(\'genres[]\');
				for(var i=0, n=checkboxes.length;i<n;i++) {
					checkboxes[i].checked = source.checked;
				}
			}
		</script>';

echo '</head><body>';

////////////////////////////////////////////////////////////////////////IN CASE OF NO OPTION (DEFAULT PAGE)
if (!isset($para['option'])){
	echo '<h1>'.ttt('Copy Soundfiles to USB Stick').'</h1>';
	
//////Show details, ins case that copyprogress on work
	if ($sysdetails['dsmversion']['majorversion'] == 6) exec('ps ax|grep /tmp/lms.copy',$cmddump);
	else exec('ps|grep /tmp/lms.copy',$cmddump);
	foreach($cmddump as $dump){
		if((strpos($dump,'sh /tmp/lms.copy') != false) and (strpos($dump,'&&') == false)) {
			echo '<p style="color: red;">'.ttt('actual in progress') . ': '.$dump;
			$scriptname = substr($dump,strrpos($dump,' '));
//			echo ' <a href="log/'.basename($scriptname).'.log.html" target="_blank">log</a>';
	echo ' &lt;-- '.ttt('copy in progress').'</p>';
		}
	}

	mkdir('log');
	
	echo '<form name="input" action="prepare2copy.php?" method="get">';

////POSIBLE FOLDER
	if ($sysdetails['dsmversion']['majorversion'] == 6) $sizemultiplier = 1000000000;
	else $sizemultiplier = 1000000;
	
	$folderlist = dbquery("select round(sum(filesize) / ".$sizemultiplier." ,2) as size, count(id) as count, split_part(path,'/',2) as volume, split_part(path,'/',3) as folder,split_part(path,'/',4) as subfolder, concat('/',split_part(path,'/',2),'/',split_part(path,'/',3),'/',split_part(path,'/',4)) as folderpath from music group by volume, folder, subfolder order by folderpath");
	
	foreach($folderlist as $key => $folder){
//		$folderlist[$key]['folderpath'] = '/'.$folder['volume'].'/'.$folder['folder'].'/'.$folder['subfolder'];
		if (in_array($folderlist[$key]['folderpath'],$settings['selectedfolder'])) $folderlist[$key]['checked'] = ' checked';
		else $folderlist[$key]['checked'] = NULL;
	}
	
	echo '<h2>'.ttt('source').'</h2>';
	echo '<table><tr><td>';
	echo '<p>'.ttt('Select the recognize folders from').' <strong>'.$sourceroot.'</strong> '.ttt('that contains the files that you want copy randomized to the USB Stick').'.</p>';
	
	echo '<table><tr><th>'.ttt('folder').'</th><th>'.ttt('size').'</th><th>'.ttt('count').'</th></tr>';
	if(count($folderlist) >= 1) {
		foreach($folderlist as $folder) {
			echo '<tr><td nowrap><input type="checkbox" name="folders[]" value="'.$folder['folderpath'].'"'.$folder['checked'].'><div style="display:inline;font-size:xx-small">/'.$folder['volume'].'/'.$folder['folder'].'/</div>'.$folder['subfolder'].'</td><td class="right">'.$folder['size'].'GB</td><td class="right">'.$folder['count'].'</td></tr>';
		}
		echo '<tr><td nowrap><input type="checkbox" onClick="togglefolders(this)" /><strong>'.ttt('all').'/'.ttt('none').'</strong></td></tr>';	
	}
	else echo msg('','',ttt('no source avaiable, please check if folder').' "'.$sourceroot.'" '.ttt('contains subfolders'),'red');

	echo '</table>';
	echo '</p>';	

////POSSIBLE EXTENSIONS	
	echo '</td><td><p>'.ttt('audioformats').':<p>';
	$formats = dbquery("select lower(right(path,strpos(reverse(path),'.')-1)) as extension, count(id) as count from music group by extension order by extension");
	foreach($formats as $format) {
		echo '<input type="checkbox" name="exts[]" value="'.$format['extension'].'"';
		if ($format['extension'] == 'mp3') echo ' checked';
		echo '>'.$format['extension'].' ('.$format['count'].')<br />';
	}
	echo '</p></td>';
	
//POSSIBLE GENRES
	echo '</td><td><p>'.ttt('genres').':<p>';
	$genres = dbquery("select genre, count(id) as count from music group by genre order by lower(genre)");

	$i = 0;
	foreach($genres as $genre) {
		$genrelists[$i] .= '<input type="checkbox" name="genres[]" value="'.$genre['genre'].'"';
		if (in_array($genre['genre'],$settings['selectedgenre'])) $genrelists[$i] .= ' checked';
		else $genrelists[$i] .= '';
		$genrelists[$i] .= '>'.$genre['genre'].' ('.$genre['count'].')<br />';
		$i++;
		if ($i == 4) $i = 0;
	}
	
	echo '<table><tr>';
	foreach($genrelists as $genrelist) echo '<td nowrap>'.$genrelist.'</td>';
	echo '<tr><td nowrap><input type="checkbox" onClick="togglegenres(this)" /><strong>'.ttt('all').'/'.ttt('none').'</strong></td></tr>';	
	echo '</tr></table>';
	echo '</p></td></tr></table>';
	
	


////LIST AVAIABLE DESTINATIONS
	echo '<h2>'.ttt('destionations').'	</h2><p>'.ttt('Select the destination of your music files').'.</p>';
	
	$drives = checkdestinations();

	$check = ' checked';
	
	if ($drives == NULL) echo msg('','',ttt('NO PORTABLE DRIVE AS DESTINATION FOUND'),'red');	
	else{
		foreach($drives as $folder => $detail){
			echo '<p><input type="radio" name="volume" value="'.$folder.'"'.$check.'>'.ttt('free space on destination').' <strong>'.$folder.':</strong> '.$detail['freegb'].'Gbyte of '.$detail['totalgb'].'Gbyte - '.ttt('That means').' '.$detail['freegrade'] .'% '.ttt('are available');
		//	if($detail['writeable'] == false) echo ' '.ttt('But').' <font color="red">'.ttt('NO WRITE PERMISSION').'</font>';
				
//			echo ' <a href="'.$approot.'/delete.php?drive='.$folder.'" target="Blank">'.ttt('delete content').'</a>'; doesn't work wich too much files
			echo '</p>';
			$check = NULL;
		}
	}
	echo ' <p><a href="'.$dsmurl.'/webman/index.cgi?launchApp=SYNO.SDS.App.FileStation3.Instance" target="Blank">'.ttt('open synology filemanager').'</a></p>';
		
	
	
////SHOW OPTIONS TO MANUPULATE THE COPY	
	if ($drives != NULL){
	echo '<h2>'.ttt('option').'</h2><p>'.ttt('Settings for the kind of copy').'</p>';
	echo '<p><input type="checkbox" name="differbygenre" value="true">'.ttt('differfolderbygenre').' <strong>'.ttt('or').'</strong> <input type="checkbox" name="CD16" value="true">'.ttt('Copy 50 files in each Folder CD01 - CD06 to use it with a CD changer Simulator for older Carradios. As example when you use the Yatour Digital Music Changer.').'</p>';
	echo '<p></p>';
//	echo '<p><input type="checkbox" name="baftbf" value="true">'.ttt('Breakes the copyjob at first to big file').'</p>';
//	echo '<p><input type="checkbox" name="leftfreespace" value="true">'.ttt('Left free space on destination').': <input type="text" name="leftfreespacemb">MB</p>';
	echo '<p><input type="checkbox" name="convert2mp3" value="true">'.ttt('Convert Files to MP3');
	echo ' (change bitrate: <select name="bitrate" size="1">';
	echo '<option>no</option>';
	foreach($allowedbitrate as $bitrate) echo '<option>'.$bitrate.'</option>';
	echo '</select>)';
	echo '</p>';	
	
	
////SHOW THE START BUTTON	
	echo '<h2>'.ttt('lets go').'</h2><p><input type="submit" name="option" target="_blank" value="'.ttt('Start to copy').'"></form></p><p style="font-size:xx-small;">'.ttt('after start').'</p>';
	
	}
}

////FEEDBACK
echo '<h2>Feedback</h2><p>'.ttt('FEEDBACK').'</p>';
	
////IMPRESSUM
echo '<hr><p><table style="border-style:none;"><tr style="border-style:none;"><td style="border-style:none;">'.ttt('Autor').': <a href="http://thiemo.schuff.eu">Thiemo Schuff</a> - April 2016</td><td style="text-align:right;border-style:none;"><a rel="license" href="http://creativecommons.org/licenses/by-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nd/4.0/88x31.png" /></a><br />'.ttt('This work is licensed under a').' <a rel="license" href="http://creativecommons.org/licenses/by-nd/4.0/">Creative Commons Attribution-NoDerivatives 4.0 International '.ttt('License').'</a></td></tr></table></p>.';
	
	
?>