#!/usr/bin/php
<?php

include_once("../config/config.php");
include_once("../include/functions-debian.php");
include_once("../include/mysql_connect.php");

/*
 * [03 Jun 2013] DSA-2702-1 telepathy-gabble - TLS verification bypass
 *         {CVE-2013-1431}
 *         [squeeze] - telepathy-gabble 0.9.15-1+squeeze2
 *         [wheezy] - telepathy-gabble 0.16.5-1+deb7u1
 */

$num = 0;
while ($line = fgets(STDIN)) {
    $num++;

    /* Record header */
    $ret = preg_match('/^\[.+\] (DSA-\S+) (.*)$/', $line, $matches);
    if ($ret === 1) {
        if (!empty($rec)) {
	    if (array_key_exists('cves', $rec))
		store_cve_data($rec);
	}
		
        //    print_r($rec);

        $rec = array();
        $rec['definition_id'] = $matches[1];
        $rec['severity'] = "n/a";
        $rec['title'] = $matches[1] . ": " . $matches[2];
        $rec['ref_url'] = "https://security-tracker.debian.org/tracker/" . $matches[1];
        continue;
    }

    /* CVEs */
    $ret = preg_match('/^\s+{(.+)}\s*$/', $line, $matches);
    if ($ret === 1) {
        $cves = preg_split("/\s/", $matches[1]);

        if (array_key_exists('cves', $rec))
            die ("Format error (multiple CVE lines) at " . $num);
        $rec['cves'] = array();

        foreach ($cves as $cve)
            array_push($rec['cves'], $cve);

        continue;
    }

    /* Debian versions affected */
    $ret = preg_match('/^\s+\[(\S+)\]\s+-\s+(\S+)\s+(.*)$/', $line, $matches);
    if ($ret === 1) {
        $deb_release = $matches[1];
        $name = $matches[2];

        $list = preg_split("/\s+/", $matches[3]);
        $package_version = $list[0];
        if ($package_version == "<not-affected>" ||
                $package_version == "<unfixed>" ||
                $package_version == "<end-of-life>") {
            continue;
        }

        /* see deb-version(5) for version number format */
        $ret = preg_match('/^[\.+-:~A-Za-z0-9]+$/', $package_version);
        if ($ret !== 1)
            die ("Format error (wrong version format) at line " . $num);

        /* rsplit('-', $package_version): */
        $ver = explode('-', $package_version);
        $debian_revision = array_pop($ver);
        $upstream_version = implode('-', $ver);

        $package = array();
        $package['name'] = $name;
        $package['version'] = $upstream_version;
        $package['release'] = $debian_revision;

        if (! array_key_exists('debian_releases', $rec))
            $rec['debian_releases'] = array();

        if (! array_key_exists($deb_release, $rec['debian_releases']))
            $rec['debian_releases'][$deb_release] = array();

        array_push($rec['debian_releases'][$deb_release], $package);

        continue;
    }

    $ret = preg_match('/^\s+NOTE:/', $line);
    if ($ret === 1) {
        continue;
    }

    die ("Format error (unrecognized line) at line " . $num);
}

if (!empty($rec)) {
    if (array_key_exists('cves', $rec))
	store_cve_data($rec);
}

//    print_r($rec);

return(0);

?>
