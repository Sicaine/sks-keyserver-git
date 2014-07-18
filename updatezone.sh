#!/bin/sh
PATH=/usr/sbin/:/usr/bin:/bin:$PATH
export PATH
date
cd /webs/sks-keyservers.net/

/usr/sbin/rndc flush 2>&1

php status/get_zonedata.php > zoneinfo.txt

if [ -n "`cat zoneinfo.txt`" ]; then
	(cat zonetpl | sed -e "s/XXXXXXXXX/`m=$(date +%M); m=${m:0:1}; date +3%y%m%d%H$m`/"; cat zoneinfo.txt;) > /var/bind/pri/sks-keyservers.net.zone
	/usr/sbin/rndc reload 2>&1
fi;
