<?php                                                                                                     
    if (isset($_GET['p']) && $_GET['p'])
    {
        file_put_contents("/.config/pianobar/input", "{$_GET['p']}\n");
        header("Location: mobile.php");
    }
    $pidoraurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
    <title>Pianobar | Mobile</title>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
    <meta name="description" content="Pidora mobile site - Full web control of Pianobar" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />
    <meta name="robots" content="index, follow" />
    <link rel=stylesheet href=inc/mobile.css />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="/inc/jquery-animateoverflow.min.js"></script>
    <script src="/inc/jquery-animateoverflow.min.js"></script>
    <script>
        function doUpdate(oldSongData) {
            var newDataPlain, newSongData;
            $.get("/api.php", function(newDataPlain) {
                newData = JSON.parse(newDataPlain);
                if(newData.title) {
                    newSongData = newData;
                    if((typeof oldSongData === "undefined") ||
                       (oldSongData.title     != newSongData.title  ) || 
                       (oldSongData.artist    != newSongData.artist ) ||
                       (oldSongData.album     != newSongData.album  ) ||
                       (oldSongData.details   != newSongData.details) ||
                       (oldSongData.loved     != newSongData.loved  ) ||
                       (oldSongData.artURL    != newSongData.artURL )) {
                        oldSongData = newSongData;
                        updateSong(newSongData);
                    }
                    if(oldSongData.remaining != newSongData.remaining) {
                        if(newSongData.remaining) {
                            $('.songRemaining').text(newSongData.remaining + " remaining");
                        } else {
                            $('.songRemaining').text("");
                        }
                    }
                }
                setTimeout(function() {
                    doUpdate(oldSongData);
                }, 3000);
            });
        }
        
        function updateSong(data) {
            var title  = document.createElement('h1');
            var artist = document.createElement('h2');
            var album  = document.createElement('h2');

            $(title ).text(data.title );
            $(artist).text(data.artist);
            $(album ).text(data.album ).addClass('album');
            $("#info-wrap").html("").append(title,artist,album);
            
            if(data.loved) {
                $('.love').show();
            } else {
                $('.love').hide();
            }
            $('.songCover').attr("src", data.artURL).attr("alt", data.album + " by " + data.artist);
            if(data.remaining) {
                $('.songRemaining').text(data.remaining + " remaining");
            } else {
                $('.songRemaining').text("");
            }
            $("h1, h2").animateOverflow();
        }
        
        function sendCommand(action) {
            $.get("api.php", {command:action});
        };
        
        function showStations() {
            $.get("api.php", {command:'stationList'}).done(function(response) {
                var responseObj = JSON.parse(response);
                var stationList = responseObj.stations;
                var output = "<ol>";
                for(i = 0; i < stationList.length; i++) {
                    output += "<li><a onclick=\"sendCommand('s" + stationList[i].id + "');clearStations();\">";
                    output += stationList[i].name;
                    output += "</a></li>\n"
                }
                output += "</ol>";
                $('#stationList .stations').html(output);
                $('#albumArt, #trackInfo').hide();
                $('#stationList').show();
            });
        }
        function clearStations() {
            $('#stationList').hide();
            $('#albumArt, #trackInfo').show();
            $('#stationList .stations').html("");
        }
        
        $(document).ready(function() {
            var emptyVar;
            doUpdate(emptyVar);
        });
    </script>
</head>
<body>
<div id="header">
    <ul id="controls">
        <li><a onclick="showStations();"    ><span id="changestation" title="Change Station"    >Change Station    </span></a></li>
        <li><a><span class="nocontrol"> | </span></a></li>
        <li><a onclick="sendCommand('p');"  ><span id="pause"   title="Pause"  >Pause  </span></a></li>
        <li><a onclick="sendCommand('n');"  ><span id="next"    title="Next"   >Next   </span></a></li>
        <li><a><span class="nocontrol"> | </span></a></li>
        <li><a onclick="sendCommand('+');"  ><span id="love"    title="Love"   >Love   </span></a></li>
        <li><a onclick="sendCommand('-');"  ><span id="ban"     title="Ban"    >Ban    </span></a></li>
        <li><a onclick="sendCommand('t');"  ><span id="tired"   title="Tired"  >Tired  </span></a></li>
        <li><a><span class="nocontrol"> | </span></a></li>
        <li><a onclick="sendCommand('v-');"  ><span id="voldown" title="Vol -"  >Vol -  </span></a></li>
        <li><a><span class="nocontrol" id="volume">Volume</span><a></li>
        <li><a onclick="sendCommand('v+');"  ><span id="volup"   title="Vol +"  >Vol +  </span></a></li>

        <li><a class="right" onclick="sendCommand('q');"><span id="restart" title="Restart">Restart</span></a></li>
    </ul>
</div>
<div class="colmask">
    <div class="colleft" id="albumArt">
        <!-- Column 1 start -->
        <img class="displayed songCover" src="/inc/pandora.png" alt=""/>
        <!-- Column 1 end -->
    </div>
    <div class="colright span2cols" id="trackInfo">
        <div class="displayed">
            <div id="info-wrap">
                <h1><span class="songTitle"></span ></h1>
                <h2><span class="songArtist"></span></h2>
                <h2><span class="songAlbum"></span ></h2>
            </div>
            <span class="songRemaining"></span>
        </div>
    </div>
    <div class="span3cols" id="stationList" style="display:none">
        <div class="closeButton">
            <a onclick="clearStations();" id="closeStations"><span>Close</span></a>
        </div>
        <div class="stations">
        
        </div>
    </div>
</div>
<div class="footer" style=display:none>
</div>
</body>
</html>
