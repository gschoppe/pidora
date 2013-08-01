$(document).ready(function() {
    var undefinedVar;
    doUpdate(undefinedVar);
    $('#newStationForm'  ).submit(function() {addStation();});
    $('#newStationButton').click (function() {addStation();});
});

function doUpdate(oldSongData) {
    var newDataPlain, newSongData;
    $.get("/api.php", function(newDataPlain) {
        newData = JSON.parse(newDataPlain);
        if(newData.title) {
            if($('#msg').is(':visible')) {
                clearScreen(function() {
                    $('#content').fadeIn('slow');
                    startScroll();
                });
            }
            newSongData = newData;
            if((typeof oldSongData === "undefined") ||
               (oldSongData.title   != newSongData.title  ) || 
               (oldSongData.artist  != newSongData.artist ) ||
               (oldSongData.album   != newSongData.album  ) ||
               (oldSongData.details != newSongData.details) ||
               (oldSongData.loved   != newSongData.loved  ) ||
               (oldSongData.artURL  != newSongData.artURL )) {
                oldSongData = newSongData;
                clearScreen(function() {
                    updateSong(newSongData);
                    $('#content').fadeIn('slow');
                    startScroll();
                });
                setMousetraps();
            }
            if(oldSongData.remaining != newSongData.remaining) {
                $('#content .remaining').html(newSongData.remaining);
                $('#content .duration' ).html(newSongData.duration );
                var progressBarWidth = "" + newSongData.percentage+"%";
                if(newSongData.percentage == null) {
                    $('#content .time').hide();
                    $("#content div.progress_bar div.marker").width(0);
                }else {
                    if(oldSongData.duration == newSongData.duration) {
                        $("#content div.progress_bar div.marker").animate({ width: progressBarWidth }, 1000);
                    }else{
                        $("#content div.progress_bar div.marker").width(progressBarWidth);
                    }
                    $('#content .time').fadeIn("slow");
                }
            }
        } else if(newData.msg) {
            clearScreen(function() {
                $('#msg h1').html(newData.msg);
                $('#msg').fadeIn('slow');
            });
        }
        setTimeout(function() {
            doUpdate(oldSongData);
        }, 3000);
    });
}

function clearScreen(doNext) {
    $('#content, #msg, #stationList, #newStation').fadeOut('slow').promise().done(function() {
        doNext();
    });
}

function updateSong(data) {
    var title  = document.createElement('h1');
    var artist = document.createElement('h2');
    var album  = document.createElement('h2');

    $(title ).text(data.title );
    $(artist).text(data.artist);
    $(album ).text(data.album ).addClass('album');
    $("#marquee-wrap").html("").append(title,artist,album);
    
    $('#content .details').html("EMPTY").hide();
    if(data.loved) {
        $('#content .love').show();
    } else {
        $('#content .love').hide();
    }
    $('#content .albumart').attr("src", data.artURL).attr("alt", data.album + " by " + data.artist);
    
    if(data.percentage == null) {
        $('#content .time').hide();
        $("#content div.progress_bar div.marker").width(0);
    }
                
};

function startScroll() {
    $('#marquee-wrap').children().animateOverflow();
}

function explainSong() {
    details = $('p.details').html();
    if (details == "EMPTY") {
        $('p.details').html("Grabbing explanation...").fadeToggle('slow');
        $.get("api.php", {command:'e'}).done(function(explainPlain) {
            explain = JSON.parse(explainPlain);
            $('p.details').fadeOut('slow', function() {
                $(this).html(explain.explanation).fadeIn('slow');
            });
        });
    } else {
        $('p.details').fadeToggle('slow');
    }
}

function stationSetup() {
    var index = 0;
    getStations(index);
    Mousetrap.reset();
    Mousetrap.bind('0', function() { sendCommand('s'.concat(index,'0')); });
    Mousetrap.bind('1', function() { sendCommand('s'.concat(index,'1')); });
    Mousetrap.bind('2', function() { sendCommand('s'.concat(index,'2')); });
    Mousetrap.bind('3', function() { sendCommand('s'.concat(index,'3')); });
    Mousetrap.bind('4', function() { sendCommand('s'.concat(index,'4')); });
    Mousetrap.bind('5', function() { sendCommand('s'.concat(index,'5')); });
    Mousetrap.bind('6', function() { sendCommand('s'.concat(index,'6')); });
    Mousetrap.bind('7', function() { sendCommand('s'.concat(index,'7')); });
    Mousetrap.bind('8', function() { sendCommand('s'.concat(index,'8')); });
    Mousetrap.bind('9', function() { sendCommand('s'.concat(index,'9')); });
    Mousetrap.bind('n', function() { getStations(++index); });
    Mousetrap.bind('b', function() { getStations(--index); });
    Mousetrap.bind('esc', function() { clearStations(); });
}

function getStations(index) {
    $.get("api.php", {station:index}).done(function(stationList) {
        clearScreen(function() {
            $('#stationList').html(stationList).fadeIn('slow');
        });
    });
}

function clearStations() {
    clearScreen(function() {
        $('#content').fadeIn('slow');
        setMousetraps();
    });
}

function showNewStation() {
    clearScreen(function() {
        $('#newStation').fadeIn('slow', function(){
            $('#newStationName').focus();
        });
        Mousetrap.reset();
        Mousetrap.bind('esc', function() { clearStations(); });
    });
}

function addStation() {
    var stationName = $('#newStationName').val();
    if(stationName) {
        sendCommand('c'+stationName);
    }
    clearStations();
}

function sendCommand(action) {
    $.get("api.php", {command:action});
};

function setMousetraps() {
    Mousetrap.reset();
    Mousetrap.bind(['p', 'space'], function() { sendCommand('p'); });
    Mousetrap.bind('n', function() { sendCommand('n'); });
    Mousetrap.bind('l', function() { sendCommand('+'); });
    Mousetrap.bind('b', function() { sendCommand('-'); });
    Mousetrap.bind('t', function() { sendCommand('t'); });
    Mousetrap.bind('q', function() { sendCommand('q'); });
    Mousetrap.bind('e', function() { explainSong(); });
    Mousetrap.bind('s', function() { stationSetup(); });
}