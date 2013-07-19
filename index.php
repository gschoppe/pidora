<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pianobar</title>
    <link rel=StyleSheet href="inc/styles.css" type="text/css" />
    <link rel="icon" type="image/png" href="favicon.ico" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="http://cdn.craig.is/js/mousetrap/mousetrap.min.js?88c23"></script>
    <script src="/inc/script.js"></script>
</head>
<body>
    <div id="controls">
        <a onclick="stationSetup();"><span id="station" title="Station">Station</span></a>
        <a><span class="nocontrol"> | </span></a>
        <a onclick="sendCommand('p');"  ><span id="pause"   title="Pause"  >Pause  </span></a>
        <a onclick="sendCommand('n');"  ><span id="next"    title="Next"   >Next   </span></a>
        <a><span class="nocontrol"> | </span></a>
        <a onclick="sendCommand('+');"  ><span id="love"    title="Love"   >Love   </span></a>
        <a onclick="sendCommand('-');"  ><span id="ban"     title="Ban"    >Ban    </span></a>
        <a onclick="sendCommand('t');"  ><span id="tired"   title="Tired"  >Tired  </span></a>
        <a onclick="explain();"     ><span id="explain" title="Explain">Explain</span></a>
        <a><span class="nocontrol"> | </span></a>
        <a onclick="sendCommand('(');"  ><span id="voldown" title="Vol -"  >Vol -  </span></a>
        <a><span class="nocontrol" id="volume">Volume</span><a>
        <a onclick="sendCommand(')');"  ><span id="volup"   title="Vol +"  >Vol +  </span></a>

        <a class="right" onclick="sendCommand('q');"  ><span id="restart" title="Restart">Restart</span></a>
    </div>

    <div id="content">
        <img src=inc/love.png class=love style="display:none" />
        <img src=inc/pandora.png class=albumart alt="Pandora logo" />
        <h1>Loading...</h1>
        <h2>Getting Status from Server...</h2>
        <h2 class=album></h2>
        <p class=details>EMPTY</p>
    </div>

    <div id="stationList" style="display:none">
    </div>

    <div id="msg" style="display:none">
        <h1></h1>
    </div>
</body>
</html>
