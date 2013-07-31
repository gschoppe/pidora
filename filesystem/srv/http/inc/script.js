$.fn.pingpongscroll = function () {
    var delay = 30;
    $(this).wrapInner('<span>');
    var contentWidth = $(this).children('span').width();
    var boxWidth = $(this).width();

    if (contentWidth > boxWidth) {
        var startIndent = parseInt($(this).css('text-indent'));
        var currIndent  = startIndent;
        var left = true;
        $(this).pingpongscrollstep(contentWidth, startIndent, currIndent, left, delay);
    }
};
$.fn.pingpongscrollstep = function (contentWidth, startIndent, currIndent, left, delay) {
    if($(this).length != 0) {
        thisdelay = delay;
        if(left) {
            if(contentWidth + currIndent > $(this).width()) {
                currIndent = currIndent - 1;
                $(this).css('text-indent', currIndent);
            } else {
                left = false;
                thisdelay = thisdelay*20;
            }
        } else {
            if(currIndent < startIndent) {
                currIndent = currIndent + 1;
                $(this).css('text-indent', currIndent);
            } else {
                left = true;
                thisdelay = thisdelay*30;
            }
        }
        var thiselement = this;
        setTimeout(function(){
            $(thiselement).pingpongscrollstep(contentWidth, startIndent, currIndent, left, delay);
        }, thisdelay);
    }
};

$(document).ready(function() {
    var newDataPlain, oldSongData, newSongData;
    window.setInterval(function() {
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
                    
                    if(!newSongData.percentage) {
                        $('#content .time').hide();
                        $("#content div.progress_bar div.marker").width(0);
                    }else {
                        if(oldSongData.duration == newSongData.duration) {
                            $("#content div.progress_bar div.marker").animate({ width: progressBarWidth }, 1000);
                            $('#content .time').fadeIn("slow");
                        }else{
                            $("#content div.progress_bar div.marker").width(progressBarWidth);
                            $('#content .time').fadeIn("slow");
                        }
                    }
                }
            } else if(newData.msg) {
                clearScreen(function() {
                    $('#msg h1').html(newData.msg);
                    $('#msg').fadeIn('slow');
                });
            }
        });
    }, 1000);
    
    $('#newStationForm'  ).submit(function() {addStation();});
    $('#newStationButton').click (function() {addStation();});
});

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
    
    if(data.duration == "NEWS") {
        $('#content .duration').html("");
        $('#controls').fadeOut('fast');
    } else {
        $('#content .duration').html(data.duration);
        if(!$('#controls').is(':visible'))
            $('#controls').fadeIn('slow');
    }
    $('#content .details').html("EMPTY").hide();
    if(data.loved) {
        $('#content .love').show();
    } else {
        $('#content .love').hide();
    }
    $('#content .albumart').attr("src", data.artURL).attr("alt", data.album + " by " + data.artist);
};

function startScroll() {
    $('#marquee-wrap').children().each(function(){
        $(this).pingpongscroll();
    });
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