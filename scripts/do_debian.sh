#!/bin/bash

WD=/var/www/pakiti-egi/scripts

if [ ! -d /tmp/pakiti-egi-debian ]; then
    mkdir /tmp/pakiti-egi-debian || exit 1
fi
cd /tmp/pakiti-egi-debian || exit 1

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
./process_dsa.php < /tmp/pakiti-egi-debian/DSA/list
