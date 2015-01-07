#!/bin/bash

# Get list of site security officers
curl -s --capath /etc/grid-security/certificates/ --cert /etc/apache2/ssl/pakiti.egi.eu.pem --key /etc/apache2/ssl/pakiti.egi.eu.key "https://goc.egi.eu/gocdbpi/private/?method=get_site_contacts&roletype=Site%20Security%20Officer" -o /tmp/pakiti-seo.xml

# Get site security info, it CSIRT email
curl -s --capath /etc/grid-security/certificates/ --cert /etc/apache2/ssl/pakiti.egi.eu.pem --key /etc/apache2/ssl/pakiti.egi.eu.key "https://goc.egi.eu/gocdbpi/private/?method=get_site_security_info" -o /tmp/pakiti-csirt.xml

# Get list of all NGI contacts
curl -s --capath /etc/grid-security/certificates/ --cert /etc/apache2/ssl/pakiti.egi.eu.pem --key /etc/apache2/ssl/pakiti.egi.eu.key "https://goc.egi.eu/gocdbpi/private/?method=get_roc_contacts" -o /tmp/pakiti-ngi-so.xml

# Get list of sites
curl -s --capath /etc/grid-security/certificates/ --cert /etc/apache2/ssl/pakiti.egi.eu.pem --key /etc/apache2/ssl/pakiti.egi.eu.key "https://goc.egi.eu/gocdbpi/private/?method=get_site_list&certification_status=Certified&production_status=Production" -o /tmp/pakiti-sites.xml

cd /var/www/pakiti-egi/scripts && /usr/bin/php site_admins.php 2>/dev/null

