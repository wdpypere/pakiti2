<?php
# Default Config file.
$config = '/etc/pakiti2/pakiti2-server-egi.conf';

# Default view in host detail:
# installed - show all installed packages
# updates - show all packages which have newer version in repository
# supdates - show packages which have newer version in security repository
# cve - show packages which have some CVE
$default_view = "cve";

# Default tag which will be shown on Pakiti server web page
# all - show all tags
# 'your tag' - show only machines tagged 'your tag'
$default_tag = "all";

# Default order
# tag - order by tag name
# host - order by hostname
# time - order by the time of last report
# kernel - order by kernel
#$default_order = "tag";
$default_order = "time";

# Default view on domains and hosts page
# all - all domains or hosts
# vulnerable - only with vulnerable packages
# unpatched - only with unpatched packages
$default_type = "all";

# Which package names represent kernels, this name will be used to determine running kernel and the right one package represents it
$kernel_pkg_names = array ( "kernel", "kernel-devel", "kernel-smp", "kernel-smp-devel", "kernel-xenU", "kernel-xenU-devel", "kernel-largesmp", "kernel-largesmp-devel", "kernel-xen", "kernel-PAE", "kernel-hugemem", "kernel-ib" );

# If devel packages will be stored in the DB ([package name]-devel), 0 - false, 1 - true
$store_devel_packages = 0;

# If doc packages will be sotred in the DB ([package-name]-doc), 0 - false, 1 - true
$store_doc_packages = 0;

# List of ignored packages, this packages won't be stored in the database
$ignore_package_list = array ( "kernel-headers", "kernel-debug", "kernel-source", "kernel-firmware", "kernel-debug-devel", "kernel-kdump" );

# Enable anonymous links
$anonymous_links = 1;

# Lifetime in seconds (default one week)
$anonymous_link_lifetime = 604800;

# Secret used for links - !!! CHANGE IT !!!
$secret = 'abcdefghijklmnopqrstuv1234567890!@#$%^&*()';

# Enable/disable Outdated/missing packages view (off by default)
$ext_pages_outdated = 0;

# Enable ansynchronous mode (vulnerabilities won't be checked when host reporting, but by running scripts/recalculate_vulnerabilities.php script)
$asynchronous_mode = 0;

# Enable authorization, off by default
$enable_authz = 1;

# DNs of the users, who can setup authz
# Example $admin_dns = array ( "/DC=cz/DC=cesnet-ca/O=Masaryk University/CN=xxx1", "/DC=cz/DC=cesnet-ca/O=Masaryk University/CN=xxx2" );
#$admin_dns = array (
# );
include_once(realpath(dirname(__FILE__)) . '/admins_acl.php');

# Array of the trusted proxy clients, that can send results on behalf of other pakiti clients
# Example $trusted_proxy_clients = array ( "proxy1.ics.muni.cz", "proxy2.ics.muni.cz" );
$trusted_proxy_clients = array ( );

# Set of tags which can be used to tag the CVEs (e.g. critical)
$cve_tags = array( "EGI-Critical", "EGI-High" );

# Public key used for decrypting client reports
$cern_report_decryption_key = "/etc/apache2/ssl/pakiti.egi.eu.key-forCERN";

# "Acknowledgement" footer. Specified as a relative path in the web root.
$footer_image = "img/egi_banner.png";
?>
