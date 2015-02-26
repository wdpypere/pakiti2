<?php
# Copyright (c) 2008-2009, Grid PP, CERN and CESNET. All rights reserved.
# 
# Redistribution and use in source and binary forms, with or
# without modification, are permitted provided that the following
# conditions are met:
# 
#   o Redistributions of source code must retain the above
#     copyright notice, this list of conditions and the following
#     disclaimer.
#   o Redistributions in binary form must reproduce the above
#     copyright notice, this list of conditions and the following
#     disclaimer in the documentation and/or other materials
#     provided with the distribution.
# 
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
# CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
# INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
# BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
# EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
# ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
# OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE. 

include_once("../../../config/config.php");
include_once("../../../include/mysql_connect.php");
include_once("../../../include/functions.php");
include_once("../../../include/authz.php");

# First value of the array is used as a default
$stag = (isset($_GET["tag"])) ? mysql_real_escape_string($_GET["tag"]) : "";
$country = (isset($_GET["country"])) ? mysql_real_escape_string($_GET["country"]) : "";
$site = (isset($_GET["site"])) ? mysql_real_escape_string($_GET["site"]) : "";
$cve = (isset($_GET["cve"])) ? mysql_real_escape_string($_GET["cve"]) : "";
$roc = (isset($_GET["roc"])) ? mysql_real_escape_string($_GET["roc"]) : "";
$type = (isset($_GET["type"])) ? mysql_real_escape_string($_GET["type"]) : "";
$pakiti_tag = (isset($_GET["a"])) ? mysql_real_escape_string($_GET["a"]) : "Nagios";

# Default output type is CSV
if ($type == "") {
	$type = "csv";
}

if ($site != "") {
        if ($enable_authz) {
		$sql = "SELECT id FROM site WHERE name='$site'";
		$res = mysql_query($sql);
		$row = mysql_fetch_row($res);
                if (check_authz_site($row[0]) != 1) {
                        exit;
                }
        }
}
# If no cve is selected, do not show anything

$sql = "SELECT DISTINCT 
		host.host, arch.arch, site.name, site.country, cve.cve_name,
		site.id, cve_tags.tag, os.os, site.mail, site.roc, cves.title
	FROM 
		cve_tags, cve, cves, installed_pkgs_cves, host, site, arch, os
	WHERE 
		host.admin = '$pakiti_tag' AND
		cve_tags.enabled = 1 AND
		cve_tags.cve_name=cve.cve_name AND
		cve.cves_id=cves.id AND
		cves.id=installed_pkgs_cves.cve_id AND
		installed_pkgs_cves.host_id=host.id AND
		host.site_id=site.id AND
		host.arch_id=arch.id AND
		host.os_id = os.id ";
if (!empty($stag)) $sql .= " AND cve_tags.tag='$stag'";
if (!empty($country)) $sql .= " AND site.country = '$country'";
if (!empty($cve)) $sql .= " AND cve.cve_name = '$cve'";
if (!empty($site)) $sql .= " AND site.name = '$site'";
if (!empty($roc)) $sql .= " AND site.roc = '$roc'";

# Authz
	if ($enable_authz) {
		$entities_ret = get_authz_site_ids();
		if (($entities_ret != 1 || $entities_ret != -1) && !empty($entities_ret)) {
			$sql .= " AND ($entities_ret)  ";
		}
	}       

$sql .= " ORDER BY cve_tags.tag, site.country, site.roc, site.name, host.host, cve.cve_name";

if (!$sqlres = mysql_query($sql)) {
	print "ERROR: " . mysql_error();
}

$res = array();
$hosts = 0;
while ($row = mysql_fetch_row($sqlres)) {
	$res[$hosts] = array();
	$res[$hosts]["host"] = $row[0];
	$res[$hosts]["host_arch"] = $row[1];
	$res[$hosts]["site_name"] = $row[2];
	$res[$hosts]["country"] = $row[3];
	$res[$hosts]["roc"] = $row[9];
	$res[$hosts]["cve_name"] = $row[4];
	$res[$hosts]["site_id"] = $row[5];
	$res[$hosts]["cve_tag"] = $row[6];
	$res[$hosts]["host_os"] = $row[7];
	$res[$hosts]["cve_title"] = $row[10];
	$hosts++;

	if (!isset(${"sites_$row[6]"}[$row[2]])) {
		${"sites_$row[6]"}[$row[2]] = 0;
	} else {
		${"sites_$row[6]"}[$row[2]]++;
	}
	if (!isset(${"countries_$row[6]"}[$row[3]])) {
		${"countries_$row[6]"}[$row[3]] = 0;
	} else {
		${"countries_$row[6]"}[$row[3]]++;
	}
	if (!isset(${"cve_$row[4]"}[$row[2]])) {
		${"cve_$row[4]"}[$row[2]] = 0;
	} else {
		${"cve_$row[4]"}[$row[2]]++;
	}
	if (!isset(${"sc_$row[2]"})) {
		${"sc_$row[2]"} = 0;
	} else {
		${"sc_$row[2]"} = $row[8];
	}
}

switch ($type) {
	case "csv":
		header("Content-Type: text/plain");
		print "CVE Tag,Site Country,ROC,Site Name,Hostname,Host Architecture,Host OS,CVE Name,CSIRT Mails\n";

		foreach ($res as $key => $val) {
			$host = $val["host"];
			$host_arch = $val["host_arch"];
			$site_name = $val["site_name"];
			$site_country = $val["country"];
			$site_roc = $val["roc"];
			$cve_name = $val["cve_name"];
			$site_id = $val["site_id"];
			$cve_tag = $val["cve_tag"];
			$host_os = $val["host_os"];

			${"sc_$site_name"} = str_replace(",", ";", ${"sc_$site_name"});
			${"sc_$site_name"} = str_replace(" ", "", ${"sc_$site_name"});
			print "$cve_tag,$site_country,$site_roc,$site_name,$host,$host_arch,$host_os,$cve_name," . ${"sc_$site_name"} . "\n";
		}
		break;
	case "xml":
		header("Content-Type: text/xml; charset=utf-8");
		print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\n";
		print "<pakiti xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"http://pakiti.egi.eu/pakiti.xsd\" timestamp=\"".date("c")."\" description=\"Monitoring of security updates\">\n";
# date("Y-m-d\Th:m:sT")
		print " <sites>\n";

		$current_site = "";
		$current_host = "";
		foreach ($res as $key => $val) {
                        $host = $val["host"];
                        $host_arch = $val["host_arch"];
                        $site_name = $val["site_name"];
                        $site_country = $val["country"];
                        $cve_name = $val["cve_name"];
                        $site_id = $val["site_id"];
                        $cve_tag = $val["cve_tag"];
                        $host_os = $val["host_os"];
			$cve_title = $val["cve_title"];

			# Create tag description
			switch ($cve_tag) {
				case "EGI-High":
					$cve_tag_dsc = "1";
					break;
				case "EGI-Critical":
					$cve_tag_dsc = "2";
					break;
			}
		
			# Skip unknwon sites
			if ($site_name == "unknown") continue;
			
			if ($current_site != $site_name) {
				if ($current_site != "") {
					print "   </cves>\n";
					print "  </host>\n";
					print "  </site>\n";
				}
				$current_site = $site_name;
				$site_changed = 1;
				print "  <site name=\"$site_name\">\n";
			}
	
			if ($current_host != $host) {
				if ($site_changed != 1) {
					print "   </cves>\n";
					print "  </host>\n";
				}
				$current_host = $host;
				print "  <host>\n";
				print "   <hostname>$host</hostname>\n";
				print "   <os>$host_os</os>\n";
				print "   <cves>\n";
				$site_changed = 0;
			}
			print "    <cve tag=\"$cve_tag\" dashboard_status=\"$cve_tag_dsc\" title=\"$cve_title\">$cve_name</cve>\n";
			
		
		}
		if ($current_site != "") {
			print "   </cves>\n";
			print "  </host>\n";
			print "  </site>\n";
		}
		print "</sites>\n</pakiti>";

		break;
	default:
		header("Content-Type: text/plain");
		print "Unsupported output type!";
}
?>
