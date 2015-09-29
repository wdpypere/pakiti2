<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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

$mtime = microtime(); 
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

include_once("../../config/config.php");
include_once("../../include/mysql_connect.php");
include_once("../../include/functions.php");
include_once("../../include/gui.php");

$authorized = 1;
if (($anonymous_links == 1) && (get_logged_user() == "")) {
       if (!check_link($_GET['auth'])) {
       	       print "You do not have permissions to access this site or the lifetime of the link has expired.";
               exit;
       }
       $authorized = 0;
}

# First value of the array is used as a default
$stag = (isset($_GET["tag"])) ? mysql_real_escape_string($_GET["tag"]) : "";
$country = (isset($_GET["country"])) ? mysql_real_escape_string($_GET["country"]) : "";
$site = (isset($_GET["site"])) ? mysql_real_escape_string($_GET["site"]) : "";
$cve = (isset($_GET["cve"])) ? mysql_real_escape_string($_GET["cve"]) : "";
$roc = (isset($_GET["roc"])) ? mysql_real_escape_string($_GET["roc"]) : "";
$pakiti_tag = (isset($_GET["a"])) ? mysql_real_escape_string($_GET["a"]) : "";

$title = "Pakiti CVEs by tags results";

?>
<html>
<head>
	<title><?php print $title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="pakiti.css" media="all" type="text/css" />
	<link rel="shortcut icon" type="image/ico" href="favicon.ico"> 
</head>
<body onLoad="document.getElementById('loading').style.display='none';">

	<div id="loading" style="position: absolute; width: 250px; height: 40px; left: 45%; top: 50%; font-weight: bold; font-size: 20pt; text-decoration: blink;">Loading ...</div>

<?php print_header(); 

if ($site != "") {
        if ($authorized && $enable_authz) {
		$sql = "SELECT id FROM site WHERE name='$site'";
		$res = mysql_query($sql);
		$row = mysql_fetch_row($res);
                if (check_authz_site($row[0]) != 1) {
                        exit;
                }
        }
}
?>

<?php
# SQL query to get the list of affected countries, cves and sites
$sql = "SELECT DISTINCT 
		site.name, site.country, cve.cve_name, site.roc
	FROM 
		cve_tags, cve, cves, installed_pkgs_cves, host, site
	WHERE 
		cve_tags.enabled = 1 AND
		cve_tags.cve_name=cve.cve_name AND
		cve.cves_id=cves.id AND
		cves.id=installed_pkgs_cves.cve_id AND
		installed_pkgs_cves.host_id=host.id AND
		host.site_id=site.id";
if (!empty($pakiti_tag)) {
	$sql .= " AND host.admin='$pakiti_tag'";
}
# Authz
	if ($enable_authz) {
		$entities_ret = get_authz_site_ids();
		if (($entities_ret != 1 || $entities_ret != -1) && !empty($entities_ret)) {
			$sql .= " AND ($entities_ret)  ";
		}
	}       

$countries = array();
$sites = array();
$rocs = array();
$cves = array();

if (!$sqlres = mysql_query($sql)) {
	print "ERROR: " . mysql_error();
}

# Create arrays for each tag
foreach($cve_tags as $t) {
	${"sites_${$t}"} = array();
	${"countries_${$t}"} = array();
	${"rocs_${$t}"} = array();
}

while ($row = mysql_fetch_row($sqlres)) {
	$sites[$row[0]] = 0;
	$countries[$row[1]] = 0;
	$cves[$row[2]] = 0;
	$rocs[$row[3]] = 0;
	foreach ($cve_tags as $t) {
		${"sites_$t"}[$row[0]] = 0;
		${"countries_$t"}[$row[1]] = 0;
		${"rocs_$t"}[$row[3]] = 0;
	}
}

ksort($sites);
ksort($countries);
ksort($rocs);
ksort($cves);

# If no cve is selected, do not show anything

$act_site = "";
$sites = array();

		#host.admin = '$pakiti_tag' AND
$sql = "SELECT DISTINCT 
		host.host, arch.arch, site.name, site.country, cve.cve_name,
		site.id, cve_tags.tag, os.os, site.mail, UNIX_TIMESTAMP(host.time), site.roc, host.admin
	FROM 
		cve_tags, cve, cves, installed_pkgs_cves, host, site, arch, os
	WHERE 
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
if (!empty($pakiti_tag)) $sql .= " AND host.admin = '$pakiti_tag'";

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
$affected_countries = array();
$current_country = "";
$affected_cves = array();
$current_cve = "";
while ($row = mysql_fetch_row($sqlres)) {
	$res[$hosts] = array();
	$res[$hosts]["host"] = $row[0];
	$res[$hosts]["host_arch"] = $row[1];
	$res[$hosts]["site_name"] = $row[2];
	$sites[$row[2]]++;
	$res[$hosts]["country"] = $row[3];
	$countries[$row[3]]++;
	$res[$hosts]["roc"] = $row[10];
	$rocs[$row[10]]++;
	$res[$hosts]["cve_name"] = $row[4];
	$cves[$row[4]]++;
	$res[$hosts]["site_id"] = $row[5];
	$res[$hosts]["cve_tag"] = $row[6];
	$res[$hosts]["host_os"] = $row[7];
	$res[$hosts]["time"] = date("j.n.y H:i", $row[9]);
	$res[$hosts]["tag"] = $row[11];
	$hosts++;

	${"sites_$row[6]"}[$row[2]]++;
	${"countries_$row[6]"}[$row[3]]++;
	if (!defined(${"cve_$row[4]"}[$row[2]])) ${"cve_$row[4]"}[$row[2]] = 0;
	${"cve_$row[4]"}[$row[2]]++;
	${"sc_$row[2]"} = $row[8];
	${"rocs_$row[2]"} = $row[10];

	// How many countries are affected	
	if ($row[3] != $current_country) {
		$affected_countries[$row[3]] = 1;
		$current_country = $row[3];
	}
	// How many distinct cves are actual	
	if ($row[4] != $current_cve) {
		$affected_cves[$row[4]] = 1;
		$current_cve = $row[4];
	}
}

# Select tags
$sql = "SELECT DISTINCT admin from host";
if (!$sqlres = mysql_query($sql)) {
        print "ERROR: " . mysql_error();
}
$pakiti_tags = array();
while ($row = mysql_fetch_row($sqlres)) {
	array_push($pakiti_tags, $row[0]);
}

?>

<!-- Start a table for the drop down boxes. -->
<form action="" method="get" name="gform">
<table width="100%">
<tr align="center">
<td width="20%">
<?php
        /* Show selected Tag */
        print "CVE Tag:";
        if (!$authorized) {
                print "&nbsp; $stag";
        } else {

                print ' <select name="tag" onchange="gform.submit();">';
		print '<option>';
                foreach ($cve_tags as $tag) {
                        print '<option' ;
                        if ( $tag ==  $stag )
                                print " selected";
                        print ' value="'.$tag.'">'.ucfirst($tag) ;
                }
                print "</select>";
        }
        print "</td><td>";

        /* Show selected Country */
        print "Country:";
        if (!$authorized) {
                print "&nbsp; $country";
        } else {

                print ' <select name="country" onchange="gform.submit();">';
		print '<option>';
                foreach ($countries as $key => $val) {
                        print '<option' ;
                        if ( $country ==  $key )
                                print " selected";
                        print " keyue='$key'>$key" ;
                }
                print "</select>";
        }
        print "</td><td>";

        /* Show selected Site */
        print "Site:";
        if (!$authorized) {
                print "&nbsp; $site";
        } else {

                print ' <select name="site" onchange="gform.submit();">';
		print '<option>';
		ksort($sites);
                foreach ($sites as $key => $val) {
                        print '<option' ;
                        if ( $site ==  $key )
                                print " selected";
                        print " keyue='$key'>$key" ;
                }
                print "</select>";
        }
        print "</td><td>";

        /* Show selected ROC */
        print "ROC:";
        if (!$authorized) {
                print "&nbsp; $roc";
        } else {

                print ' <select name="roc" onchange="gform.submit();">';
		print '<option>';
                foreach ($rocs as $key => $val) {
                        print '<option' ;
                        if ( $roc ==  $key )
                                print " selected";
                        print " keyue='$key'>$key" ;
                }
                print "</select>";
        }
        print "</td><td>";

        /* Show selected Tag */
        print "Tag:";
        if (!$authorized) {
                print "&nbsp; $pakiti_tag";
        } else {

                print ' <select name="a" onchange="gform.submit();">';
		print '<option>';
                foreach ($pakiti_tags as $key) {
                        print '<option' ;
                        if ( $pakiti_tag ==  $key )
                                print " selected";
                        print " keyue='$key'>$key" ;
                }
                print "</select>";
        }
        print "</td><td>";


        /* Show selected Site */
        print "CVE:";
        if (!$authorized) {
                print "&nbsp; $cve";
        } else {

                print ' <select name="cve" onchange="gform.submit();">';
		print '<option>';
                foreach ($cves as $key => $val) {
                        print '<option' ;
                        if ( $cve ==  $key )
                                print " selected";
                        print " keyue='$key'>$key" ;
                }
                print "</select>";
        }
        print "</td>";
?>
</table>
</form>
<!-- Start a table for the drop down boxes. -->
<table width="100%">
       <tr>
               <td align="right">
<?php
       if ($authorized && !empty($cve) && !empty($site)) {
               print "<span class=\"bu\" onClick=\"this.innerHTML='" . get_link() . "'\">Click to get anonymous link to this page (lifetime of the link is " . $anonymous_link_lifetime/60 . " minutes)</span>";
       }
?>
               </td>
       </tr>
</table>

<!-- Start to Display the Main Results -->
<table width="100%">
<tr style="background: #eeeeee; font-style: italic;" align="top">
	<td width="50">
		Tag
	</td>
	<td width="150">
		Country (<?php (!empty($country)) ? print "1" : print sizeof($affected_countries); ?>)
	</td>
	<td width="100">
		Site (<?php (!empty($site)) ? print "1" : print sizeof($sites); ?>)
	</td>
	<td width="100">
		ROC (<?php (!empty($roc)) ? print "1" : print sizeof($rocs); ?>)
	</td>	
	<td>
		Host (<?= $hosts; ?>)
	</td>
	<td width="50">
		Arch	
	</td>
	<td width="150">
		OS	
	</td>
	<td>
		CVE (<?php (!empty($cve)) ? print "1" : print sizeof($affected_cves); ?>)	
	</td>
	<td>
		Last report
	</td>
	<td>
		Tag
	</td>
</tr>


<?php

$bg_color = 'class="bg1"';
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
	$time = $val["time"];
	$host_tag = $val["tag"];


	$bg_color == 'class="bg1"' ? $bg_color = 'class="bg2"': $bg_color = 'class="bg1"';

	print "<tr $bg_color>";
	print "<td>";
	if ($cve_tag == "EGI-Critical") {
		print "<span style=\"color: red;\">$cve_tag</span>";
	} else {
		print $cve_tag;
	}
	print "</td>";
	print "<td>$site_country</td>";
	print "<td><b>";
	if ($authorized) print "<a href=\"hosts.php?s=$site_id\">";
	print $site_name;
	if ($authorized) print "</a>";
	print "</b>\n";
	print "</td>";
	print "<td>$site_roc</td>";
	print "<td>";
	if ($authorized) print "<a href=\"host.php?h=$host\">";
	print "&nbsp;&nbsp;$host";
	if ($authorized) print "</a>";
	print "</td>\n";
	print "<td>$host_arch</td>";
	print "<td>$host_os</td>";
	print "<td><a href=\"cve.php?cve=$cve_name&os=$host_os\" style=\"color: red;\">$cve_name</a></td>";
	print"<td>$time</td>";
	print"<td>";
	switch ($host_tag) {
		case "Nagios":
			print '<img width="16" src="img/nagios.png" alt="EGI Nagios">';
			break;
		case "Manual":
			print '<img width="16" src="img/manual.png" alt="Manual">';
			break;
		default:
			print $host_tag;
	}
	print"</td>";
	print "</tr>\n";
}
?>
</table>
<br/>
<h2>Statistics</h2>
<h3>Sites</h3>
<table>
<tr align="left">
	<th width="150">Site</th>
	<th width="150">ROC</th>
	<th width="150">CSIRT Mail</th>
	<th width="80">Vuln. hosts</th>
	<?php
		foreach($cve_tags as $t) {
			print "<th width=\"80\">$t</th>\n";
		}
	?>
</tr>
<?php
	ksort($sites);
	foreach($sites as $key => $val) {
		print "<tr><td>$key</td><td>".${"rocs_$key"}."</td><td>" . ${"sc_$key"} . "</td><td>$val</td>";
		foreach ($cve_tags as $t) {
			print "<td";
			if (${"sites_$t"}[$key] == 0) {
				print " style=\"color: lightgrey;\"";
			}	
			print ">" . ${"sites_$t"}[$key] . "</td>";
		}
		print "</tr>\n";	
	}
?>
</table>
<h3>CVEs</h3>
<table>
<tr align="left">
	<th width="150">CVE</th>
	<th>Vuln. sites</th>
	<th>Vuln. hosts</th>
</tr>
<?php
	ksort($cves);
	foreach($cves as $key => $val) {
		// If report is limited only to some site, show only affected cves 
		if (!array_key_exists($key, $affected_cves)) continue;
		if (!empty($cve) && $cve != $key) continue;
		print "<tr><td><a href=\"cve.php?cve=$key\">$key</a></td><td>";
	     	print sizeof(${"cve_$key"});	
		print "</td><td>$val</td></tr>\n";
	}
?>
</table>

<h3>Countries</h3>
<table>
<tr align="left">
	<th width="150">Country</th>
	<th width="80">Vuln. hosts</th>
	<?php
		foreach($cve_tags as $t) {
			print "<th width=\"80\">$t</th>\n";
		}
	?>
</tr>
<?php
	ksort($countries);
	foreach($countries as $key => $val) {
		// If report is limited only to some site, show only affected countries
		if (!array_key_exists($key, $affected_countries)) continue;
		if (!empty($country) && $country != $key) continue;
		print "<tr><td>$key</td><td>$val</td>";
		foreach ($cve_tags as $t) {
			print "<td";
			if (${"countries_$t"}[$key] == 0) {
				print " style=\"color: lightgrey;\"";
			}
			print ">" .${"countries_$t"}[$key] . "</td>";
		}
		print "</tr>\n";
	}
?>
</table>

<p align="center">
<?php
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $starttime);
    echo "<br><small>Executed in ".round($totaltime, 2)." seconds</small></font></p>";
?>

<?php print_footer(); ?>

</body></html>

