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
$os = (isset($_GET["os"])) ? mysql_real_escape_string($_GET["os"]) : "";
 
$title = "Pakiti ";
if ($cve != "") $title .= "$cve";

?>
<html>
<head>
	<title><?php print $title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="pakiti.css" media="all" type="text/css" />
	<link rel="shortcut icon" type="image/ico" href="favicon.ico"> 

	<script type="text/javascript">
	function showhide(dmn_id) {
		var elem = document.getElementsByName(dmn_id);
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

<?php print_header();  ?>

<h3>Selected CVE: <b><?php print $cve; ?></b></h3><a href="https://bugzilla.redhat.com/show_bug.cgi?id=<?=$cve?>">Link to the RedHat Bugzilla</a><br/><a href="https://security-tracker.debian.org/tracker/<?=$cve?>">Link to the Debian Advisories</a>

<?php  if ($os != "") {
  echo "<h4>OS: $os</h4>";
}
?>

<table width="100%">
<tr style="background: #eeeeee; font-style: italic;" align="top">
<?php if ($os == "") { ?>
	<td width="30%">
		OS	
	</td>
<?php } ?>
	<td>
		Package
	</td>
	<td>
		Version	
	</td>
</tr>

<!-- Start to Display the Main Results -->
<?php

# If no cve is selected, do not show anything

if ($cve != "") {
	$sql = "select os.os, pkgs.name, cves.version, cves.rel, cves.operator from pkgs, cves, cve, os, cves_os where cve.cve_name='$cve' and cves.id=cve.cves_id and cves.pkg_id=pkgs.id and cves.cves_os_id=cves_os.id and cves_os.os_id=os.id";

      if ($os != "") {
	$sql .= " and (os.os='$os' or os.os=' $os')  order by pkgs.name";
      } else {
        $sql .= " order by os.os, pkgs.name";
      }


	if (!$res = mysql_query($sql)) {
		print "ERROR: " . mysql_error();
	}

	$bg_color = 'class="bg1"';
	$current_host_os = "";
	while($row = mysql_fetch_row($res) ) {
	      $host_os = $row[0];
	      $pkg_name = $row[1];
	      $pkg_version = $row[2] . "-" . $row[3];
	      $operator = $row[4];
		
	      $bg_color == 'class="bg1"' ? $bg_color = 'class="bg2"': $bg_color = 'class="bg1"';
 
		print "<tr $bg_color>";
		if ($os == "") {
		  if ($current_host_os != $host_os) { 
		    print "<td><b>$host_os</b></td>";
		    $current_host_os=$host_os;
		  } else {
		    print "<td></td>";
		  }
		}
		print "<td><a href=\"packages.php?pkg=$pkg_name\">$pkg_name</a></td>";
		print "<td>$operator $pkg_version</td>";
		print "</tr>\n";
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

