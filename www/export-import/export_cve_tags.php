<?php

$config = '/etc/pakiti2/pakiti2-server-egi.conf';
include("../../include/mysql_connect.php");

# <cveTags>
#    <cveTag>
#        <cveName>...</cveName>
#        <reason>...</reason>
#        <enabled>...</enabled>
#        <tagName>...</tagName>
#    </cveTag>
#</cveTags>

$err = '';
$xml = '<?xml version="1.0" encoding="UTF-8" ?>';
$xml .= "<cveTags>";

$sql = "SELECT cve_name,reason,enabled,tag,url FROM cve_tags";

if (!$res = mysql_query($sql)) {
        $err = mysql_error($link);

} else {
        while ($row = mysql_fetch_row($res)) {
                $cve_name = $row[0];
                $reason = $row[1];
                $enabled = $row[2];
                $tag = $row[3];
		$infoUrl = $row[4];
		$xml .= "<cveTag><cveName>$cve_name</cveName><reason>$reason</reason><infoUrl>$infoUrl</infoUrl><enabled>$enabled</enabled><tagName>$tag</tagName></cveTag>";
	}
}

$xml .= '</cveTags>';

header('Content-Type: text/xml');

if ($err != "") {
        print $err;
};

print $xml;
?>
