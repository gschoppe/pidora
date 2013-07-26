Pidora
======
A network-controlled pandora client for embedded systems, such as the Raspberry Pi or PogoPlug E02


*This is a quick and dirty README. Use at your own risk*

1.	Install Arch Linux on your device
2.	Open a terminal or connect via SSH
3.	Enter the following commands:
<tt>pacman -Syu</tt>
<tt>timedatectl set-timezone America/New_York</tt>
<tt>pacman -S lighttpd php php-fpm sqlite php-sqlite libao alsa-utils avahi libpulse pianobar mpg123 python-feedparser sudo</tt>
<tt>echo "use_mmap=no" >> /etc/libao.conf</tt>
<tt>cd /etc</tt>
<tt>wget https://dl.dropbox.com/u/42238/pogoplug/v2/asound.conf</tt>
<i>OR</i>
<tt>wget https://dl.dropbox.com/u/42238/pogoplug/v2/48khz/asound.conf</tt>
<i>OR</i>
<tt>wget https://dl.dropbox.com/u/42238/pogoplug/v2/default/asound.conf</tt>
<i>depending on your DAC</i>
4.	Download, extract, and place the files contained in this repository's "filesystem" directory in the proper locations relative to root
5.	Enter the following commands:
<tt>gpasswd -a http audio</tt>
<tt>mkfifo /srv/http/ctl</tt>
<tt>chown -R http /srv/http</tt>
<tt>chmod -R 777 /srv/http</tt>
<tt>systemctl enable php-fpm</tt>
<tt>systemctl enable lighttpd</tt>
<tt>reboot</tt>
6.	open your browser to the IP address of the device (or 127.0.0.1, if you are working from the device directly)

That's it for the installation. Now let's configure our machine to automatically launch the web browser and pianobar.

Contact me
==========
If you have any questions about my modified version of pidora, contact me at [gschoppe.com](http://gschoppe.com)

*This is the original Contact information for user jacroe, who wrote pidora*
You can shoot me an email or submit an issue at [GitHub](https://github.com/jacroe/pidora/issues/new) if you have a question or a suggestion. I welcome them with open arms.

If you found this useful, I also welcome tips with open arms! You can tip me via [Gittip](http://gittip.com/jacroe), [Paypal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XC7VG35XEHN8W), or [Bitcoin](http://jacroe.com/bitcoin.html). I'll use these to pay for bills and/or Mountain Dew and pizza. Thank you, and best wishes!