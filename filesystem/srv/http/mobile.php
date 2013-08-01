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
        
        $(document).ready(function() {
            var emptyVar;
            doUpdate(emptyVar);
        });
    </script>
</head>
<body>
<div id="header">
    <ul>
        <li><a href="<?=$pidoraurl;?>?p=p">Pause</a></li>
        <li><a href="<?=$pidoraurl;?>?p=n">Next</a></li>
        <li><a href="<?=$pidoraurl;?>?p=%2B">Love</a></li>
        <li><a href="<?=$pidoraurl;?>?p=-">Ban</a></li>
        <li><a href="<?=$pidoraurl;?>?p=t">Tired</a></li>
    </ul>
</div>
<div class="colmask fullpage">
    <div class="col1">
        <!-- Column 1 start -->
        <div class="displayed">
            <div id="info-wrap">
                <h1><span class="songTitle"></span ></h1>
                <h2><span class="songArtist"></span></h2>
                <h2><span class="songAlbum"></span ></h2>
            </div>
            <span class="songRemaining"></span>
        </div>
        <img class="displayed songCover" src="/inc/pandora.png" width="350", height="350", alt="">
        <!-- Column 1 end -->
    </div>
</div>
<div class="footer" style=display:none>
</div>
</body>
</html>
