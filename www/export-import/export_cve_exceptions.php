<?php

$config = '/etc/pakiti2/pakiti2-server-egi.conf';
include("../../include/mysql_connect.php");

# <cveExceptions>
#    <cveException>
#        <cveName>...</cveName>
#        <reason>...</reason>
#        <pkg>
#            <name>...</name>
#            <version>...</version>
#            <release>...</release>
#            <arch>...</arch>
#            <type>...</type>
#        </pkg>
#        <osGroup>
#            <name>...</name>
#        </osGroup>
#    </cveException>
#</cveExceptions>

$err = '';
$xml = '<?xml version="1.0" encoding="UTF-8" ?>';
$xml .= "<cveExceptions>";

$sql = "SELECT DISTINCT pkgs_exceptions.cve_name, pkgs_exceptions.reason, pkgs.name, pkgs_exceptions.version, pkgs_exceptions.rel, pkgs_exceptions.arch, cve_tags.enabled, cve_tags.tag FROM pkgs_exceptions, pkgs, cve_tags WHERE pkgs.id=pkgs_exceptions.pkg_id AND cve_tags.cve_name=pkgs_exceptions.cve_name";

if (!$res = mysql_query($sql)) {
        $err = mysql_error($link);

} else {
        while ($row = mysql_fetch_row($res)) {
                $cve_name = $row[0];
                $reason = $row[1];
		$pkg_name = $row[2];
		$pkg_version = $row[3];
		$pkg_rel = $row[4];
		$pkg_arch = $row[5];
                $enabled = $row[6];
                $tag = $row[7];
		$xml .= "<cveException><cveName>$cve_name</cveName><reason>$reason</reason><pkg><name>$pkg_name</name><version>$pkg_version</version><release>$pkg_rel</release><arch>$pkg_arch</arch><type></type></pkg><enabled>$enabled</enabled><tag><name>$tag</name><description></description></tag><osGroup><name></name></osGroup></cveException>";
	}
}

$xml .= '</cveExceptions>';

header('Content-Type: text/xml');

if ($err != "") {
        print $err;
};

print $xml;
?>
