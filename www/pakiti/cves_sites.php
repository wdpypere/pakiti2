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

$cve = (isset($_GET["cve"])) ? mysql_real_escape_string($_GET["cve"]) : "";
$site = (isset($_GET["site"])) ? mysql_real_escape_string($_GET["site"]) : "";
$arch = (isset($_GET["arch"])) ? mysql_real_escape_string($_GET["arch"]) : "";
$rh_release = (isset($_GET["rh_release"])) ? mysql_real_escape_string($_GET["rh_release"]) : "";
 
$title = "Pakiti Package Results for ";
if ($cve != "") $title .= "$cve";
if ($site != "") $title .= " for $site";

?>
<html>
<head>
	<title><?php print $title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="pakiti.css" media="all" type="text/css" />
	<link rel="shortcut icon" type="image/ico" href="favicon.ico"> 

	<script type="text/javascript">
	function showhide(site_id) {
		var elem = document.getElementsByName(site_id);
                for (var i in elem) {
        	        if (elem[i].style.display == 'none') {
                	        elem[i].style.display='';
                        } else {
                                elem[i].style.display='none';
                        }
                }
	}
	</script>
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

<!-- Start a table for the drop down boxes. -->
	<form action="" method="get" name="gform">
		<table width="100%">
		<tr align="center">
			<td width="20%">
<?php
	/* Show selected CVE */
	print "CVE:";
	if (!$authorized) {
		print "&nbsp; $cve";
        } else {

		print '	<select name="cve" onchange="gform.submit();">';
		print '	<option value=""';
		if ($cve != "") print " selected";
		print '>No CVE selected';
		$cves = mysql_query("SELECT DISTINCT cve_name FROM cve ORDER BY cve_name DESC") ;
		while($row = mysql_fetch_row($cves)) {
			print '<option' ;
			if ( $cve ==  $row[0] )
				print " selected";
			print ' value="'.$row[0].'">'.$row[0] ;
		}
		print "</select>";
	}
	print "</td>"
?>
	<td width="20%">
<?php
	/* Show selected Architecture */
	print "Architecture:";
	if (!$authorized) {
		print "&nbsp; $arch";
        } else {

		print '	<select name="arch" onchange="gform.submit();">';
		print '	<option value=""';
		if ($arch != "") print " selected";
		print '>No architecture selected';
		$archs = mysql_query("SELECT arch FROM arch ORDER BY arch") ;
		while($row = mysql_fetch_row($archs)) {
			print '<option' ;
			if ( $arch ==  $row[0] )
				print " selected";
			print ' value="'.$row[0].'">'.$row[0] ;
		}
		print "</select>";
	}
	print "</td>"
?>
	<td width="20%">
<?php
	/* Show selected RH release */
	print "RedHat release:";
	if (!$authorized) {
		print "&nbsp; $rh_release";
        } else {

		print '	<select name="rh_release" onchange="gform.submit();">';
		print '	<option value=""';
		if ($rh_release != "") print " selected";
		print '>No RedHat release selected';
		$rh_releases = mysql_query("SELECT value FROM settings WHERE name='RedHat Releases CVE' ORDER BY value") ;
		while($row = mysql_fetch_row($rh_releases)) {
			print '<option' ;
			if ( $rh_release ==  $row[0] )
				print " selected";
			print ' value="'.$row[0].'">Release '.$row[0] ;
		}
		print "</select>";
	}
	print "</td>"
?>
	<td>Site:
<?php
       if (!$authorized) {
               print "&nbsp; $site";
       } else {                

               print '<select name="site" onchange="gform.submit();">
               <option value="">All';
		$sql = "SELECT name, country FROM site ";
		if ($enable_authz) {
			$entities_ret = get_authz_site_ids();
			
	                if (($entities_ret != 1 || $entities_ret != -1) && !empty($entities_ret)) {
                        	$sql .= " WHERE  $entities_ret  ";
                        }
                }	
		$sql .= "ORDER BY country, name" ;

		$sites = mysql_query($sql);
		while ($row = mysql_fetch_row($sites)) {
			print '<option' ;
			if ($site ==  $row[0])
				print " selected"; 
			print " value='$row[0]'>$row[1] - $row[0]\n" ;
   		}

		print '</select>';
	}
?>
		</tr>
		</table>
	</form>

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

<h3>Results for 
<!-- Start to Display the Main Results -->
<?php

# If no cve is selected, do not show anything

$act_site = "";
$sites = array();

if ($cve != "") {
	$sql = "SELECT DISTINCT site.name, host, UNIX_TIMESTAMP(time), site.id, site.country FROM host, installed_pkgs_cves, cve, site";
	if ($arch) $sql .= ", arch";
	if ($rh_release) $sql .= ", cves";
	$sql .= " WHERE cve.cve_name='$cve' AND installed_pkgs_cves.cve_id=cve.cves_id AND installed_pkgs_cves.host_id=host.id AND host.site_id=site.id";
	if ($site) $sql .= " AND site.name='$site'";
	if ($arch) $sql .= " AND arch.arch='$arch' AND host.arch_id=arch.id ";
	if ($rh_release) $sql .= " AND cves.cves_os_id='rh_$rh_release' AND cve.cves_id=cves.id ";
	# Authz
        if ($enable_authz) {
		$entities_ret = get_authz_site_ids();
		 if (($entities_ret != 1 || $entities_ret != -1) && !empty($entities_ret)) {
			$sql .= " AND ($entities_ret)  ";
		}
        }       

	$sql .= " ORDER BY country, site.name, host";

	if (!$res = mysql_query($sql)) {
		print "ERROR: " . mysql_error();
	}

	while($row = mysql_fetch_row($res) ) {

		$sites[$row[3]] = "$row[4] - $row[0]";

		if (!isset($hosts[$row[3]])) {
			$hosts[$row[3]] = array();
			$dates[$row[3]] = array();
		}
		array_push($hosts[$row[3]], $row[1]);
		array_push($dates[$row[3]], date("j F Y H:i", $row[2]));
	}
	$num_of_sites = count($sites);
?>
<b><a href="cve.php?cve=<?= $cve ?>"><?= $cve ?></a></b> occuring on <?= $num_of_sites ?> sites</h3>

<table width="100%">
<tr style="background: #eeeeee; font-style: italic;" align="top">
	<td width="30%">
		Site/Host
	</td>
	<td>
		Packages
	</td>
	<td>
		Last report
	</td>
</tr>


<?php

	$bg_color = 'class="bg1"';
	foreach ($sites as $site_id => $site_name) {

		$bg_color == 'class="bg1"' ? $bg_color = 'class="bg2"': $bg_color = 'class="bg1"';

		print "<tr $bg_color>";
		print "<td colspan=\"3\">";
		print "<b>";
		if ($authorized) print "<a href=\"hosts.php?s=$site_id\">";
		print $site_name;
		if ($authorized) print "</a>";
		print "</b>";
		if ($authorized) print " <span style=\"cursor: pointer;\" onclick=\"showhide($site_id);\">+</span></td>";
		print "</tr>\n";
		
		foreach ($hosts[$site_id] as $key => $val) {
			$sql = "SELECT pkgs.name, installed_pkgs.version, installed_pkgs.rel FROM pkgs, installed_pkgs_cves, installed_pkgs, cve, host
				WHERE cve.cve_name='$cve' AND installed_pkgs_cves.cve_id=cve.cves_id AND installed_pkgs_cves.host_id=host.id AND host.host='$val'
				AND installed_pkgs.id=installed_pkgs_cves.installed_pkg_id AND pkgs.id=installed_pkgs.pkg_id AND installed_pkgs.host_id=host.id";
			$res2 = mysql_query($sql);
			$packages = "";

			while ($pkg = mysql_fetch_row($res2)) {
				$packages .= "$pkg[0] ($pkg[1]-$pkg[2])<br> ";
			}
			$bg2_color == 'class="bg1"' ? $bg2_color = 'class="bg2"': $bg2_color = 'class="bg1"';
			print "<tr $bg2_color name=\"$site_id\"";
			if ($authorized) print " style=\"display: none;\"";
			print ">";
		        print "<td>";
			if ($authorized) print "<a href=\"host.php?h=$val\">";
			print "&nbsp;&nbsp;$val";
			if ($authorized) print "</a>";
			print "</td>\n";
			print "<td style=\"color: red;\">$packages</td>";
			print "<td>" . $dates[$site_id][$key] . "</td>";
			print "</tr>\n";
		}
	}
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

