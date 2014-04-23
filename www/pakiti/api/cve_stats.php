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
$type = (isset($_GET["type"])) ? mysql_real_escape_string($_GET["type"]) : "";
$from_date = (isset($_GET["from_date"])) ? mysql_real_escape_string($_GET["from_date"]) : "";
$to_date = (isset($_GET["to_date"])) ? mysql_real_escape_string($_GET["to_date"]) : "";

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

$sql = "SELECT 
		site_name, country, cve_name, tag, date
	FROM 
		cve_statistics
	WHERE 1=1 ";
if (!empty($stag)) $sql .= " AND tag='$stag'";
if (!empty($country)) $sql .= " AND country = '$country'";
if (!empty($cve)) $sql .= " AND cve_name = '$cve'";
if (!empty($site)) $sql .= " AND site_name = '$site'";
if (!empty($from_date)) $sql .= " AND date >= '$from_date'";
if (!empty($to_date)) $sql .= " AND date <= '$to_date'";

# Authz
	if ($enable_authz) {
		$entities_ret = get_authz_site_names();
		if (($entities_ret != 1 || $entities_ret != -1) && !empty($entities_ret)) {
			$sql .= " AND ($entities_ret)  ";
		}
	}       

$sql .= " ORDER BY country, site_name, tag, cve_name, date";

if (!$sqlres = mysql_query($sql)) {
	print "ERROR: " . mysql_error();
}



switch ($type) {
	case "csv":
		header("Content-Type: text/plain");
		print "Country,Site Name,CVE Tag,CVE Name,Date\n";

		while ($row = mysql_fetch_row($sqlres)) {
			$site_name = $row[0];
			$site_country = $row[1];
			$cve_name = $row[2];
			$cve_tag = $row[3];
			$date = $row[4];

			print "$site_country,$site_name,$cve_tag,$cve_name,$date\n";
		}
		break;
	case "xml":
		header("Content-Type: text/plain");
		print "Currently unsupported output type";
		
/*		header("Content-Type: text/xml; charset=utf-8");
		print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\n";
		print "<pakiti xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"http://pakiti.egi.eu/pakiti.xsd\" timestamp=\"".date("c")."\">\n";
# date("Y-m-d\Th:m:sT")
		print "<notsupporte
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
				if ($current_host != "" && $site_changed != 1) {
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
			print "    <cve tag=\"$cve_tag\">$cve_name</cve>\n";
			
		
		}
		if ($current_site != "") {
			print "   </cves>\n";
			print "  </host>\n";
			print "  </site>\n";
		}
		print "</sites>\n</pakiti>";
*/
		break;
	default:
		header("Content-Type: text/plain");
		print "Unsupported output type!";
}
?>
