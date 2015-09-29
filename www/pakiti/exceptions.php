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

include_once("../../config/config.php");
include_once("../../include/functions.php");
if (check_admin_authz() == -1) {
        echo "You are not authorized.";
        exit;
} else {
        header("Cache-Control: no-cache, must-revalidate");
        print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" >';
}

include_once("../../include/mysql_connect.php");
include_once("../../include/gui.php");

$mtime = microtime(); 
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

$entries = (isset($_POST["entries"])) ? $_POST["entries"] : 0;
$cve = (isset($_POST["cve"])) ? mysql_real_escape_string($_POST["cve"]) : "";
if (empty($cve)) { 
	$cve =(isset($_GET["cve"])) ? mysql_real_escape_string($_GET["cve"]) : "";
}
$act = (isset($_GET["act"])) ? $_GET["act"] : "noop";

if ($entries > 0) {
	for ($i=0; $i < $entries; $i++) {
		if (isset($_POST["modified$i"]) && $_POST["modified$i"] == 1) {
			# data[0] - pkgid
			# data[1] - version
			# data[2] - rel
			# data[3] - arch
			$data = explode(' ',mysql_real_escape_string($_POST["exception$i"]));
			$reason = mysql_real_escape_string($_POST["reason$i"]);
	
			if (isset($_POST["exception$i"])) {
				$sql = "INSERT INTO pkgs_exceptions (pkg_id, version, rel, cve_name, arch, reason, modifier) VALUES
				($data[0],'$data[1]','$data[2]','$cve','$data[3]','$reason','" . get_logged_user() . "') 
				ON DUPLICATE KEY UPDATE reason='$reason', modifier='" . get_logged_user() . "'"; 
				if (!mysql_query($sql)) {
					$err .= "Error: " . mysql_error($link);
				}
				$exp_id = mysql_insert_id();

				$sql = "SELECT cves.id FROM cves, cve WHERE cve.cve_name='$cve' AND cves.pkg_id='$data[0]' AND cve.cves_id=cves.id";
				if (!$res = mysql_query($sql)) {
                                       $err .= "Error: " . mysql_error($link);
                                }
				while ($row = mysql_fetch_row($res)) {
					$sql = "INSERT INTO pkg_exception_cve (cve_id, exp_id) VALUES ($row[0], $exp_id)";
					if (!mysql_query($sql)) {
                                	        $err .= "Error: " . mysql_error($link);
                                	}
				}
			}
		}
	}
}

switch ($act) {
	case "del": 
		$exp_id = mysql_real_escape_string($_GET["exp_id"]);
		$sql = "DELETE FROM pkgs_exceptions WHERE id=$exp_id";
		if (!mysql_query($sql)) {
			$err .= "Error: " . mysql_error($link);
		}
		$sql = "DELETE FROM pkg_exception_cve WHERE exp_id=$exp_id";
		if (!mysql_query($sql)) {
			$err .= "Error: " . mysql_error($link);
		}
		break;
	case "noop":
	default:
		break;
}

 
$title = "Pakiti CVEs Exceptions";

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
if (!empty($err)) print $err;
?>

<!-- Start a table for the drop down boxes. -->
	<form action="" method="post" name="gform">
		<table width="100%">
		<tr align="center">
			<td width="50%">
<?php
	/* Show selected CVE */
	print "CVE:";
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
	print "</td>"
?>

		</tr>
		</table>
	</form>

<h3>Selected CVE: <b><?php print $cve; ?></b></h3>

<?php if (!empty($cve)) { ?>
<h4>Actual exceptions</h4>
<table width="100%">
<tr style="background: #eeeeee; font-style: italic;" align="top">
<td>Package</td>
<td>Reason</td>
<td>Modifier</td>
<td>Timestamp</td>
<td>Action</td>
</tr>

<?php
	$sql = "SELECT e.id, e.pkg_id, e.version, e.rel, e.arch, e.reason, e.modifier, e.timestamp, pkgs.name FROM pkgs_exceptions e left join pkgs on e.pkg_id=pkgs.id WHERE cve_name='$cve'";
	if (!$res = mysql_query($sql)) {
		print "Error: " . mysql_error($link);
		exit;
	}

	while ($row = mysql_fetch_row($res)) {
		$pkg_id = $row[1];
		$pkg_ver = $row[2];
		$pkg_rel = $row[3];
		$pkg_arch = $row[4];
		$pkg_name = $row[8];
		$pkg_reason = $row[5];
		$pkg_modifier = $row[6];
		$pkg_timestamp = $row[7];
	
		print "<td>$pkg_name $pkg_ver/$pkg_rel <i>($pkg_arch)</i></td>\n";
		print "<td>$pkg_reason</td>";
		print "<td>$pkg_modifier</td>\n";
		print "<td>$pkg_timestamp</td>\n";
		print "<td><a href=\"?exp_id=$row[0]&act=del&s=$row[12]&cve=$cve\" title=\"Delete exception\" style=\"color: #cc0000;\">X</a></td>\n</tr>\n";
	}
?>
</table>
<?php } ?>

<h4>Actual exceptions</h4>
<!-- Start to Display the Main Results -->
<form action="" method="post">
<table>
<tr style="background: #eeeeee; font-style: italic;" align="top">
<td width=15></td>
<td>Installed versions</td>
<td>Reason</td>
</tr>

<?php
	$sql = "select distinct i.version, i.rel, i.arch, p.name, p.id, cve.cve_name  from cve, cves, installed_pkgs i, pkgs p where cve.cve_name='$cve' 
		and cve.cves_id=cves.id and cves.pkg_id=i.pkg_id and p.id=cves.pkg_id order by p.name, i.version, i.rel";
	if (!$res = mysql_query($sql)) {
		print "Error: " . mysql_error($link);
                exit;
        }
	$i = 0;
	while ($row = mysql_fetch_row($res)) {
		$pkg_id = $row[4];
		$pkg_ver = $row[0];
		$pkg_rel = $row[1];
		$pkg_arch = $row[2];
		$pkg_name = $row[3];
		$cve_name = $row[5];

		$sql = "SELECT 1 FROM pkgs_exceptions WHERE pkg_id=$pkg_id AND version='$pkg_ver' AND rel='$pkg_rel' AND arch='$pkg_arch' AND cve_name='$cve_name'";
		if (!$res2 = mysql_query($sql)) {
	                print "Error: " . mysql_error($link);
       	                exit;
        	}
		if (mysql_num_rows($res2) == 0) {
			print "<tr>\n<td><input name=\"exception$i\" id=\"exception$i\" value=\"$pkg_id $pkg_ver $pkg_rel $pkg_arch\" type=\"checkbox\" onClick=\"document.getElementById('modified$i').value = 1;\"></td>\n";
			print "<td>$pkg_name $pkg_ver/$pkg_rel <i>($pkg_arch)</i></td>\n";
			print "<td id=\"reason$i\"><input type=\"text\" size=50 name=\"reason$i\" onKeyUp=\"document.getElementById('exception$i').checked = true\"
				onClick=\"document.getElementById('modified$i').value = 1;\">
				<input type=\"hidden\" name=\"modified$i\" id=\"modified$i\" value=0>
				<input type=\"hidden\" name=\"exp_id$i\" value=\"$exp_id\"></td></tr>\n";
			$i++;
		}
	}
?>		
<tr align="left"><td colspan="3"><input type="submit" name="submit" value="Save the changes"></td></tr>
</table>
<input type="hidden" name="entries" value="<?= $i ?>">
<input type="hidden" name="cve" value="<?= $cve ?>">
</form>
		
<p align="center">
<?php
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $starttime);
    echo "<br><small>Executed in ".round($totaltime, 2)." seconds</small></p>";
?>

<?php print_footer(); ?>

</body></html>

