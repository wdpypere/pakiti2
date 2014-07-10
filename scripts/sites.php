#!/usr/bin/php
<?php
include_once("../config/config.php");
include_once("../include/mysql_connect.php");

$oval = new DOMDocument();
$oval->load("https://next.gocdb.eu/gocdbpi/public/?method=get_site_list");

#Get OVAL generator
$sites = $oval->getElementsByTagName('SITE');

$err = '';
foreach ($sites as $site) {
        $site_name = $site->getAttribute('NAME');
        $country = $site->getAttribute('COUNTRY');
        $roc = $site->getAttribute('ROC');
        print "$site_name - $country - $roc\n";
        if (empty($country)) $country = "unknown";
        if (empty($roc)) $roc = "unknown";
        $sql = "update site set country='$country', roc='$roc' where name='$site_name'";
        if (!mysql_query($sql)) {
                $err = mysql_error($link);
        }
}

if ($err != "") {
        print $err;
}
?>

