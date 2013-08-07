#!/usr/bin/env python

import sys, time, json, random
from evdev import InputDevice, categorize, ecodes
from urllib.request import urlopen
from daemon import daemon

def sendCommand(c):
    return json.loads(urlopen("http://127.0.0.1/api.php?command="+c).read().decode("utf-8"))
def getRandomStation():
    list = sendCommand('stationList')
    list = list['stations']
    index = random.randint(0, (len(list)-1))
    print(list[index]['id'] + " - " + list[index]['name'])
    sendCommand('s' + list[index]['id'])

class MyDaemon(daemon):
    def run(self):
        dev = InputDevice('/dev/input/event2')
        print(dev)
        for event in dev.read_loop():
            if event.type == ecodes.EV_KEY:
                key_pressed = str(categorize(event))
                if ', down' in key_pressed:
                    print(key_pressed)
                    if 'KEY_PLAYPAUSE' in key_pressed:
                        print('play')
                        sendCommand('p')
                    if 'KEY_FASTFORWARD' in key_pressed:
                        print('fastforward')
                        sendCommand('n')
                    if 'KEY_NEXTSONG' in key_pressed:
                        print('skip')
                        sendCommand('n')
                    if 'KEY_POWER' in key_pressed:
                        print('power')
                        sendCommand('q')
                    if 'KEY_VOLUMEUP' in key_pressed:
                        print('volume up')
                        sendCommand('v%2b')
                    if 'KEY_VOLUMEDOWN' in key_pressed:
                        print('volume down')
                        sendCommand('v-')
                    if 'KEY_CONFIG' in key_pressed:
                        print('Random Station')
                        getRandomStation()
if __name__ == "__main__":
    daemon = MyDaemon('/tmp/pidora-keyboard.pid')
    if len(sys.argv) == 2:
        if 'start' == sys.argv[1]:
            daemon.start()
        elif 'stop' == sys.argv[1]:
            daemon.stop()
        elif 'restart' == sys.argv[1]:
            daemon.restart()
        else:
            print ("Unknown command")
            sys.exit(2)
        sys.exit(0)
    else:
        print ("usage: %s start|stop|restart" % sys.argv[0])
        sys.exit(2)