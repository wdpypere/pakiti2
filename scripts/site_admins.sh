#!/bin/bash

# Get list of site security officers
wget -q -O /tmp/pakiti-seo.xml --no-check-certificate --certificate=/etc/apache2/ssl/pakiti.egi.eu.pem --private-key=/etc/apache2/ssl/pakiti.egi.eu.key "https://goc.egi.eu/gocdbpi/private/?method=get_site_contacts&roletype=Site%20Security%20Officer"

# Get site security info, it CSIRT email
wget -q -O /tmp/pakiti-csirt.xml --no-check-certificate --certificate=/etc/apache2/ssl/pakiti.egi.eu.pem --private-key=/etc/apache2/ssl/pakiti.egi.eu.key "https://goc.egi.eu/gocdbpi/private/?method=get_site_security_info"

# Get list of all NGI contacts
wget -q -O /tmp/pakiti-ngi-so.xml --no-check-certificate --certificate=/etc/apache2/ssl/pakiti.egi.eu.pem --private-key=/etc/apache2/ssl/pakiti.egi.eu.key "https://goc.egi.eu/gocdbpi/private/?method=get_roc_contacts"

# Get list of sites
wget -q -O /tmp/pakiti-sites.xml --no-check-certificate --certificate=/etc/apache2/ssl/pakiti.egi.eu.pem --private-key=/etc/apache2/ssl/pakiti.egi.eu.key "https://goc.egi.eu/gocdbpi/private/?method=get_site_list&certification_status=Certified&production_status=Production"

cd /var/www/pakiti/scripts && /usr/bin/php site_admins.php 2>/dev/null

