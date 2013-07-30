<?php
header('Content-type: text/html');
putenv('PATH=/usr/local/sbin:/usr/local/bin:/usr/bin:/usr/bin/core_perl:.');

$mpgInPath       = ".config/mpg123/input";
$mpgOutPath      = ".config/mpg123/output";
$pbarInPath      = ".config/pianobar/input";
$pbarOutPath     = ".config/pianobar/output";
$stationListPath = ".config/pianobar/stationList";
$curSongPath     = ".config/pianobar/curSong.json";
$msgPath         = "msg";

if (file_exists($msgPath))
{
	$msg = file_get_contents($msgPath);
	unlink($msgPath);
	$return = json_encode(array("msg"=>$msg));
} elseif (!(shell_exec("ps cax | grep pianobar"))) {
	$return = json_encode(array("album"=>"","loved"=>false,"artist"=>"the pianobar service is loading", "title"=>"Loading...", "artURL"=>"inc/pandora.png", "startup"=>true));
	unlink($pbarOutPath);
	$cmd = "pianobar &>> ~/".$pbarOutPath." &";
	exec($cmd);
} elseif (isset($_GET['command'])&&$_GET['command']) {
	$c = $_GET['command'];
	if (getLastLine($pbarOutPath) == "NEWS") {
		switch($c) {
			case "p" :
				file_put_contents($mpgInPath, "p\n");
				break;
			case "n" :
				file_put_contents($mpgInPath, "q\n");
				break;
			case "(" :
				file_put_contents($mpgInPath, "-\n");
				break;
			case ")" :
				file_put_contents($mpgInPath, "+\n");
				break;
			default :
				
		}
	} elseif ($c == "e") {
		$return = getDetails();
	} else {
		file_put_contents($pbarInPath, "$c\n");
		
		if ($c == "n") {
			file_put_contents($msgPath, "Skipped");
		} elseif ($c == "q") {
			file_put_contents($msgPath, "Rebooting");
			sleep(3);
			shell_exec("killall pianobar");
			//foreach (glob("albumart/*.jpg") as $delete) unlink($delete);
		} elseif($c[0] == "c") {
			if (strlen($c)>1) {
				file_put_contents($pbarInPath, "0\n");
				file_put_contents($msgPath, "Adding Station");
			} else {
				file_put_contents($pbarInPath, "\n");
				file_put_contents($msgPath, "No Station Entered");
			}
		} elseif ($c[0] == "s") {
			file_put_contents($msgPath, "Changing stations");
		}
		$return = json_encode(array("response"=>"ok"));
	}
} elseif (isset($_GET['station']) && $_GET['station'] != null) {
	$return="";
	$i = $_GET['station']*10;
	$max = $i+10;
	$arrayStations = explode("|", file_get_contents($stationListPath));
	$return .= "<a onclick=\"clearStations();\"; id=\"closeStations\"><span>esc - Cancel</span></a><br />\n";
	if ($i > 0)
		$return .= "<a onclick=\"getStations(".($_GET['station']-1).");\">B - Back</a><br />\n";
	for($i; ($i < $max) && ($i < count($arrayStations) ); $i++)
	{
		$stationRaw = $arrayStations[$i];
		$station = explode("=", $stationRaw);
		$return .= "<a onclick=\"sendCommand('s".$station[0]."');hideStations();\">".substr($station[0], -1)." - ".$station[1]."</a><br />\n";
	}
	if (count($arrayStations) > $max)
		$return .= "<a onclick=\"getStations(".($_GET['station']+1).");\">N - Next</a><br />";
} else {
	$return = getSong();
}
echo $return;

function getSong() {
	global $curSongPath, $pbarOutPath;
	$return = "";
	if (!file_exists($curSongPath)) {
		file_put_contents($curSongPath, "");
	}
	$songInfo = json_decode(file_get_contents($curSongPath), true);
	$coverart = $songInfo["artURL"];
	if ($coverart) {
		$temp = "albumart/".md5($songInfo["album"]).".jpg";
		if (!file_exists($temp))
			file_put_contents($temp, file_get_contents($coverart));
		$coverart = $temp;
	} else {
		$coverart = "inc/pandora.png";
	}
	$songInfo["artURL"] = $coverart;
	
	//Get and parse time remaining and duration
	$duration = $remaining = $elapsed    = "";
	$percentage = null; 
	$lastLine   = getLastLine($pbarOutPath);
	$lastLine   = explode("-", $lastLine);
	$timing     = $lastLine[count($lastLine)-1];
	$timing     = explode("/", $timing);
	
	if(count($timing) == 2) {
		$pattern = '/^[0-5]?[0-9]:[0-5]?[0-9]$/';
		if(preg_match($pattern, $timing[0])&&preg_match($pattern, $timing[1])) {
			$remaining_array   = explode(":", $timing[0]);
			$duration_array    = explode(":", $timing[1]);
			$remaining_seconds = intval($remaining_array[1]) + intval($remaining_array[0])*60;
			$duration_seconds  = intval( $duration_array[1]) + intval( $duration_array[0])*60;
			$elapsed_seconds   = $duration_seconds - $remaining_seconds;
			if($duration_seconds != 0 && $duration_seconds >= $remaining_seconds) {
				$remaining = $timing[0];
				$elapsed   = secondsToTime($elapsed_seconds,2);
				$duration  = $timing[1];
				$percentage = round((1 - $remaining_seconds/$duration_seconds),3)*100;
			}
		}
	}
	$songInfo["remaining" ] = $remaining;
	$songInfo["elapsed"   ] = $elapsed;
	$songInfo["duration"  ] = $duration;
	$songInfo["percentage"] = $percentage;
	
	return json_encode($songInfo);
}

function getDetails($url = NULL) {
	global $curSongPath;
	if (!$url) {
		$songInfo = json_decode(file_get_contents($curSongPath));
		$url = $songInfo->explainURL;
	}
	$data = file_get_contents($url);
	#preg_match("#features of this track(.*?)\<p\>These are just a#is", $data, $matches); // uncomment this if explanations act funny
	preg_match("#features of this track(.*?)\</div\>#is", $data, $matches);
	$strip = array("Features of This Track</h2>", "<div style=\"display: none;\">", "</div>", "<p>These are just a");
	if (!$matches[0])
		return "We were unable to get the song's explanation. Sorry about that.";
	$data = explode("<br>", str_replace($strip, "", $matches[0]));
	unset($data[count($data)-1]);
	if (trim($data[count($data)-1]) == "many other comedic similarities") {
		$ending = "many other comedic similarities";
		unset($data[count($data)-1]);
	} else {
		$ending = "many other similarites as identified by the Music Genome Project";
	}
	$data = implode(", ", array_map('trim', $data));
	return json_encode(array("explanation"=>"We're playing this track because it features $data, and $ending."));
}

// Prefix single-digit values with a zero.
function ensure2Digit($number) {
	if($number < 10) {
		$number = '0' . $number;
	}
	return $number;
}

// Convert seconds into months, days, hours, minutes, and seconds.
function secondsToTime($sec, $depth = 5) {
	if($depth == 1) {
		return($sec);
	} else {
		$ss = ensure2Digit($sec%60);
	}
	if($depth == 2) {
		return(ensure2Digit(floor($sec/60)).":".$ss);
	} else {
		$mm = ensure2Digit(floor(($sec%3600)/60));
	}
	if($depth == 3) {
		return(ensure2Digit(floor($sec/3600)).":".$mm.":".$ss);
	} else {
		$hh = ensure2Digit(floor(($sec%86400)/3600));
	}
	if($depth == 4) {
		return(ensure2Digit(floor($sec/86400)).":".$hh.":".$mm.":".$ss);
	} else {
		$dd = ensure2Digit(floor(($sec%2592000)/86400));
	}
	
	$MM = ensure2Digit(floor($sec/2592000));
    return $M.":".$d.":".$h.":".$m.":".$s;
}

function getLastLine($filename){
	$f      = fopen($filename, 'r');
	$cursor = -1;
	$line   = '';
	fseek($f, $cursor, SEEK_END);
	$char   = fgetc($f);
	// test for empty file
	if($char === false)
		return false;
	// Trim trailing newline chars of the file
	while ($char === "\n" || $char === "\r" || $char === " ") {
		fseek($f, --$cursor, SEEK_END);
		$char = fgetc($f);
	}
	// Read until the start of file or first newline char
	while ($char !== false && $char !== "\n" && $char !== "\r" ) {
		// Prepend the new char
		$line = $char . $line;
		fseek($f, --$cursor, SEEK_END);
		$char = fgetc($f);
	}
	
	return trim($line);
}
?>