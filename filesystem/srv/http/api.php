<?php
header('Content-type: text/html');
putenv('PATH=/usr/local/sbin:/usr/local/bin:/usr/bin:/usr/bin/core_perl:.');

if (file_exists("msg"))
{
	$msg = file_get_contents("msg");
	unlink("msg");
	$return = json_encode(array("msg"=>$msg));
} elseif (!(shell_exec("ps cax | grep pianobar"))) {
	$return = json_encode(array("album"=>"","loved"=>false,"artist"=>"the pianobar service is loading", "title"=>"Loading...", "artURL"=>"inc/pandora.png", "startup"=>true));
	unlink("pbarout");
	$cmd = "pianobar &>> ~/pbarout &";
    exec($cmd);
} elseif (isset($_GET['command'])&&$_GET['command']) {
	$c = $_GET['command'];
	if ($c == "e") {
		$return = getDetails();
	} else {
		file_put_contents("ctl", "$c\n");
		
		if ($c == "n") {
            file_put_contents("msg", "Skipped");
		} elseif ($c == "q") {
			file_put_contents("msg", "Rebooting");
            sleep(3);
            shell_exec("killall pianobar");
			//foreach (glob("albumart/*.jpg") as $delete) unlink($delete);
		} elseif($c[0] == "c") {
            if (strlen($c)>1) {
                file_put_contents("ctl", "0\n");
                file_put_contents("msg", "Adding Station");
            } else {
                file_put_contents("ctl", "\n");
                file_put_contents("msg", "No Station Entered");
            }
		} elseif ($c[0] == "s") {
            file_put_contents("msg", "Changing stations");
        }
		$return = json_encode(array("response"=>"ok"));
	}
} elseif (isset($_GET['station']) && $_GET['station'] != null) {
	$return="";
	$i = $_GET['station']*10;
	$max = $i+10;
	$arrayStations = explode("|", file_get_contents("stationList"));
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
	$return = "";
	
	$songInfo = json_decode(file_get_contents("curSong.json"), true);
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
	
	$lastLine = getLastLine("pbarout");
	$lastLine = explode("-", $lastLine);
	$duration = $lastLine[count($lastLine)-1];
	$songInfo["duration"] = $duration;
	
	return json_encode($songInfo);
}

function getDetails($url = NULL) {
	if (!$url) {
		$songInfo = json_decode(file_get_contents("curSong.json"));
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