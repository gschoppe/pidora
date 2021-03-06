Pidora
======
A network-controlled pandora client for embedded systems, such as the Raspberry Pi or PogoPlug E02


*This is a quick and dirty README. Use at your own risk*

1.	Install Arch Linux on your device
2.	Open a terminal or connect via SSH
3.	Enter the following commands:<br/>
<tt>pacman -Syu</tt><br/>
<tt>timedatectl set-timezone America/New_York</tt><br/>
<tt>pacman -S lighttpd php php-fpm sqlite php-sqlite libao alsa-utils avahi libpulse pianobar mpg123 python-feedparser sudo</tt><br/>
<tt>echo "use_mmap=no" >> /etc/libao.conf</tt><br/>
<tt>cd /etc</tt><br/>
<tt>wget https://dl.dropbox.com/u/42238/pogoplug/v2/asound.conf</tt><br/>
<i>OR</i><br/>
<tt>wget https://dl.dropbox.com/u/42238/pogoplug/v2/48khz/asound.conf</tt><br/>
<i>OR</i><br/>
<tt>wget https://dl.dropbox.com/u/42238/pogoplug/v2/default/asound.conf</tt><br/>
<i>depending on your DAC</i>
4.	Download, extract, and place the files contained in this repository's "filesystem" directory in the proper locations relative to root
5.	Open /srv/http/.config/pianobar/config in your favorite text editor, and enter your pandora username and password in the indicated spots 
6.	Enter the following commands:<br/>
<tt>gpasswd -a http audio</tt><br/>
<tt>mkfifo /srv/http/.config/pianobar/input</tt><br/>
<tt>chown -R http /srv/http</tt><br/>
<tt>chmod -R 777 /srv/http</tt><br/>
<tt>systemctl enable php-fpm</tt><br/>
<tt>systemctl enable lighttpd</tt><br/>
<tt>reboot</tt>
7.	run <tt>sudo -u http pianobar</tt> and choose an initial station, then quit.
8.	open your browser to the IP address of the device (or 127.0.0.1, if you are working from the device directly)

That's it for the normal installation.

If you want to play with adding hotkey support, you will want to add the following commands: <br/>
<tt>pacman -Sy gcc pip</tt><br/>
<tt>pip install evdev</tt><br/>
<tt>chmod +x -R /root/pidora-keyboard</tt><br/>


Contact me
==========
If you have any questions about my modified version of pidora, contact me at [gschoppe.com](http://gschoppe.com)

*This is the original Contact information for user jacroe, who wrote pidora*
You can shoot me an email or submit an issue at [GitHub](https://github.com/jacroe/pidora/issues/new) if you have a question or a suggestion. I welcome them with open arms.

If you found this useful, I also welcome tips with open arms! You can tip me via [Gittip](http://gittip.com/jacroe), [Paypal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XC7VG35XEHN8W), or [Bitcoin](http://jacroe.com/bitcoin.html). I'll use these to pay for bills and/or Mountain Dew and pizza. Thank you, and best wishes!