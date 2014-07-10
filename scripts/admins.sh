#!/bin/bash
OUTPUT=/var/www/pakiti-egi/config/admins_acl.php
BASE=/var/www/pakiti-egi/config/admins_acl.base

TMP=`tempfile -m 0644` || exit 1

echo "<?php" > $TMP
echo "\$admin_dns = array (" >> $TMP
cat $BASE | grep -v "^[ \t\n]*#" >> $TMP

for i in `ldapsearch -LLL -x -H ldap://aldor.ics.muni.cz/  -b 'cn=csirt,ou=groups,dc=egi,dc=eu' member|grep -v '^dn' |sed -e 's/member: //'`; do ldapsearch -LLL -x -H ldap://aldor.ics.muni.cz/  -b "$i" userCertificateSubject | perl -p -0040 -e 's/\n //' |  grep userCertificateSubject | sed -e 's/^userCertificateSubject: \(.*\)/"\1",/'; done >> $TMP

echo "); ?>" >> $TMP

mv $TMP $OUTPUT

