#!/bin/bash

WD=/var/www/pakiti/scripts

if [ ! -d /tmp/pakiti-debian ]; then
    mkdir /tmp/pakiti-debian || exit 1
fi
cd /tmp/pakiti-debian || exit 1

if [ ! -d DSA/.svn ]; then
    svn checkout svn://anonscm.debian.org/svn/secure-testing/data/DSA/ >/dev/null || exit 1
else
    svn up DSA >/dev/null
    if [ $? -ne 0 ]; then
	traceroute -n anonscm.debian.org
	exit 1
    fi
fi

cd $WD
./process_dsa.php < /tmp/pakiti-debian/DSA/list
