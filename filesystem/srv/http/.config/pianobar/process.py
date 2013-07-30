#!/usr/bin/env python

import sys, csv, subprocess, os, json, time
from time import gmtime, strftime

def process_mpg(input, output, thefile):
	mpgout = open(output, "w")
	subprocess.Popen(["mpg123","-R","--fifo", input], stdout=mpgout)
	#time.sleep(1)
	os.system("echo \"load "+thefile+"\" > " + input)
	is_stopped = 0
	is_closed  = 0
	while (is_stopped != 1) :
		time.sleep(1)
		mpgmess = open(output, "r")
		for line in mpgmess:
			if ("@P 0" in line):
				is_stopped = 1
		if(os.system("ps cax | grep mpg123") == ""):
			is_stopped = 1
			is_closed  = 1
		mpgmess.close()
	if(is_closed != 1): os.system("echo \"q\" > " + input)
	mpgout.close()

def buildJSON(title, artist, album, artURL, loved, explainURL):
	data = '{"title": ' + json.dumps(title) + ',"artist": ' + json.dumps(artist) + ',"album": ' + json.dumps(album) + ',"artURL": ' + json.dumps(artURL) + ',"loved": ' + str(bool(loved)).lower() + ',"explainURL": ' + json.dumps(explainURL) + '}'
	return json.dumps(json.loads(data), indent=2)
www = "/srv/http/"
lastNewsPath    = www + ".config/mpg123/lastnews"
mpgInPath       = www + ".config/mpg123/input"
mpgOutPath      = www + ".config/mpg123/output"
pbarOutPath     = www + ".config/pianobar/output"
pbarInPath      = www + ".config/pianobar/input"
stationListPath = www + ".config/pianobar/stationList"
curSongPath     = www + ".config/pianobar/curSong.json"
msgPath         = www + "msg"

event = sys.argv[1]
lines = sys.stdin.readlines()

#open(www + "rawdata.txt", "w").write("\n".join(lines))

fields = dict([line.strip().split("=", 1) for line in lines])

artist = fields["artist"]
title = fields["title"]
album = fields["album"]
coverArt = fields["coverArt"]
rating = int(fields["rating"])
detailUrl = fields["detailUrl"]

if event == "songstart":
	open(curSongPath, "w").write(buildJSON(title, artist, album, coverArt, rating, detailUrl))
elif event == "songfinish":
	os.system("> " + pbarOutPath)
	import feedparser, urllib
	feed = feedparser.parse("http://www.npr.org/rss/podcast.php?id=500005")
	if not os.path.lexists(lastNewsPath): open(lastNewsPath, "w").write("-1")
	lastNews = int(open(lastNewsPath, "r").read())
	currNews = feed.entries[0].updated_parsed.tm_hour
	currHour = int(strftime("%H", gmtime()))
	currMin  = int(strftime("%M", gmtime()))
	if currNews != lastNews and currNews == currHour and currMin < 30 :
		open(lastNewsPath, "w").write(str(feed.entries[0].updated_parsed.tm_hour))
		open(curSongPath, "w").write(buildJSON(feed.entries[0].title, feed.feed.title, feed.feed.title, "http://media.npr.org/images/podcasts/2013/primary/hourly_news_summary.png", 0, "null"))
		open(pbarOutPath, "w").write("NEWS")
		open(pbarInPath, "w").write("p")
		
		newsaddress = feed.entries[0].id
		process_mpg(mpgInPath, mpgOutPath, newsaddress)
		
		os.system("> " + pbarOutPath)
		open(pbarInPath, "w").write("p")
elif event == "songlove":
	open(curSongPath, "w").write(buildJSON(title, artist, album, coverArt, 1, detailUrl))
	open(msgPath, "w").write("Loved")
elif event == "songban":
	open(msgPath, "w").write("Banned")
elif event == "songshelf":
	open(msgPath, "w").write("Tired")
elif event == "usergetstations" or event == "stationcreate" or event == "stationdelete" or event == "stationrename":				# Code thanks to @officerNordBerg on GitHub
	stationCount = int(fields["stationCount"])
	stations = ""
	for i in range(0, stationCount):
		stations += "%s="%i + fields["station%s"%i] + "|"
	stations = stations[0:len(stations) - 1]
	open(stationListPath, "w").write(stations)