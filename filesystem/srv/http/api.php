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
    $return = json_encode(array("msg"=>"Starting Pianobar"));
    unlink($pbarOutPath);
    exec("echo \"\" > ~/".$curSongPath); //wipe old track data
    exec("pianobar &>> ~/".$pbarOutPath." &");
} elseif (isset($_GET['command'])&&$_GET['command']) {
    $c = $_GET['command'];
    if($c[0] == 'v') {
        $volCmd = substr($c,1);
        setVol($volCmd);
        file_put_contents($msgPath, "Vol ".$volCmd);
        $return = json_encode(array("response"=>"ok"));
    } elseif (getLastLine($pbarOutPath) == "NEWS") {
        switch($c) {
            case "p" :
                file_put_contents($mpgInPath, "p\n");
                file_put_contents($msgPath, "Play/Pause");
                break;
            case "n" :
                file_put_contents($mpgInPath, "q\n");
                exec("echo \"\" > ~/".$curSongPath); //wipe old track data
                break;
            case "q" :
                file_put_contents($mpgInPath, "q\n");
                file_put_contents($msgPath, "Closing Pianobar");
                exec("echo \"\" > ~/".$curSongPath); //wipe old track data
                file_put_contents($pbarInPath, "q\n");
                sleep(3);
                shell_exec("killall pianobar");
                break;
            default :
                
        }
        $return = json_encode(array("response"=>"ok"));
    } elseif ($c == "e") {
        $return = getDetails();
    } elseif ($c == "stationList") {
        $return = getStations();
    }else {
        file_put_contents($pbarInPath, "$c\n");
        
        if($c == "p") {
            file_put_contents($msgPath, "Play/Pause");
        } elseif($c == "n") {
            file_put_contents($msgPath, "Skipped");
            exec("echo \"\" > ~/".$curSongPath); //wipe old track data
        } elseif($c == "-" || $c == "t") {
            exec("echo \"\" > ~/".$curSongPath); //wipe old track data
        } elseif($c == "q") {
            file_put_contents($msgPath, "Closing Pianobar");
            exec("echo \"\" > ~/".$curSongPath); //wipe old track data
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
            exec("echo \"\" > ~/".$curSongPath); //wipe old track data
            $id = substr($c, 1);
            $stations = getStationList();
            foreach($stations as $station) {
                if($station['id'] == $id) {
                    file_put_contents($msgPath, $station['name']);
                    break;
                }
            }
        }
        $return = json_encode(array("response"=>"ok"));
    }
} else {
    $return = getSong();
}
echo $return;

function getStations() {
    return(json_encode(array('stations'=>getStationList())));
}

function getStationList() {
    global $stationListPath;
    $arrayStations = explode("|", file_get_contents($stationListPath));
    $stations = array();
    foreach($arrayStations as $stationRow)
    {
        $station = explode("=", $stationRow);
        if(count($station) >= 2) {
            $id = ensure2Digit($station[0]);
            if(count($station) > 2) {
                array_shift($station);
                $name = implode("=", $station);
            } else {
                $name = $station[1];
            }
            $stations[] = array('id'=>$id, 'name'=>$name);
        }
    }
    return($stations);
}

function getSong() {
    global $curSongPath, $pbarOutPath;
    $return = "";
    if (!file_exists($curSongPath)) {
        file_put_contents($curSongPath, "");
    }
    $songInfo = json_decode(file_get_contents($curSongPath), true);
    $coverart = $songInfo["artURL"];
    if ($coverart) {
        maybeClearCache();
        $temp = "albumart/".md5($songInfo["artist"]." | ".$songInfo["album"]).".jpg";
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
    $songInfo["volume"    ] = getVol();
    
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

// one out of every 5000 calls, this will clear the old files from the coverart cache
// only run occasionally to avoid lag
function maybeClearCache() {
    $dir = "albumart/";
    $cachetime = 60*60*24*30; // one month cache
    if(rand(0,5000) == 1) {
        $numDeleted = 0;
        $filenames = array_diff(scandir($directory), array('..', '.','index.html'));
        foreach($filenames as $filename) {
            $filename = $dir.$filename;
            $last_accessed = fileatime($filename);
            if($last_accessed) {
                $age = time() - $last_accessed;
                if($age > $cachetime)
                    unlink($filename);
                    $numDeleted++;
            } else {
                unlink($filename);
                $numDeleted++;
            }
        }
        return($numDeleted);
    }
    return(0);
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

function getVol() {
    $volVal = exec("amixer get Master | grep 'Front Left:' | cut -d ' ' -f 5");
    $volume = round(intval($volVal)*100/255);
    return($volume);
}

function setVol($value = 100) {
    if(is_string($value)) {
        $volume = getVol();
        if($value[0] == '+') {
            if(strlen($value) == 1) {
                $value = 5;
            } else {
                $value = intval(substr($value, 1));
            }
            $value = $volume + $value;
        } elseif($value[0]=='-') {
            if(strlen($value) == 1) {
                $value = 5;
            } else {
                $value = intval(substr($value, 1));
            }
            $value = $volume - $value;
        } else {
            $value = intval($value);
        }
    }
    if($value > 100) {
        $value = 100;
    } elseif($value < 0) {
        $value = 0;
    }
    $newVol = round($value*255/100);
    exec("amixer cset iface=MIXER,name='Master' ".$newVol." >/dev/null");
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