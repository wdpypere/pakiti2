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

$cve = (isset($_GET["cve"])) ? mysql_real_escape_string($_GET["cve"]) : "";

$action = (isset($_GET["act"])) ? $_GET["act"] : "noop";

switch ($action) {
	case "add": 
		$reason = mysql_real_escape_string($_GET["reason"]);
		$url = mysql_real_escape_string($_GET["url"]);
		$tag = mysql_real_escape_string($_GET["tag"]);
		if (!empty($cve)) {
			$sql = "INSERT INTO cve_tags (cve_name, tag, reason, url, modifier) VALUES
			('$cve', '$tag','$reason','$url', '" . get_logged_user() . "')"; 
			if (!mysql_query($sql)) {
				$err .= "Error: " . mysql_error($link);
			}
		}
		break;
	case "del":
		$id = mysql_real_escape_string($_GET["val"]);		
		$sql = "DELETE FROM cve_tags WHERE id=$id";
		if (!mysql_query($sql)) {
	                $err .= "Error: " . mysql_error($link);
                }
		break;
	case "switch":
		$id = mysql_real_escape_string($_GET["val"]);		
		$subval = mysql_real_escape_string($_GET["subval"]);
		$enabled = ($subval == "true") ? 1 : 0;
		$sql = "UPDATE cve_tags SET enabled=$enabled WHERE id=$id";
		if (!mysql_query($sql)) {
	                $err .= "Error: " . mysql_error($link);
                }
		break;
	case "noop":
		break;
}
 
$title = "Pakiti CVEs Exceptions";

?>
<html>
<head>
	<title><?php print $title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="pakiti.css" media="all" type="text/css" />
</head>
<body onLoad="document.getElementById('loading').style.display='none';">

	<div id="loading" style="position: absolute; width: 250px; height: 40px; left: 45%; top: 50%; font-weight: bold; font-size: 20pt; text-decoration: blink;">Loading ...</div>

<?php print_header(); 
if (!empty($err)) print $err;
?>
<h4>Add new entry</h4>
<!-- Start to Display the Main Results -->
<form action="" method="get" name="tags">
<input type="hidden" name="cve" value="<?= $cve ?>">
<input type="hidden" name="act" value="add">
<input type="hidden" name="val" value="">
<input type="hidden" name="subval" value="">
<table>
<tr style="background: #eeeeee; font-style: italic;" align="top">
<tr align="left">
	<td>CVE: <select name="cve">
		<?php
			$cves = mysql_query("SELECT DISTINCT cve_name FROM cve ORDER BY cve_name DESC") ;
	                while($row = mysql_fetch_row($cves)) {
				print "<option>$row[0]</option>\n";
			}
		?>
	<td>CVE Tag: <select name="tag">
		<?php
			foreach ($cve_tags as $tag) {
				print "<option>" . ucfirst($tag) . "</option>\n";
			}
		?>
	    </select></td>
	<td>Reason: <input type="text" name="reason" size="50"></td>
	<td>URL to the EGI Advisory: <input type="text" name="url" size="50" value="https://"></td>
	<td><input type="submit" value="Add"></td>
</tr>
</table>
</form>

<h4>All tags</h4>
<table width="100%">
<tr style="background: #eeeeee; font-style: italic;" align="top">
<td align="center" width="50">Enabled</td>
<td align="center" width="150">CVE</td>
<td align="center" width="5%">CVE Tag</td>
<td>Reason</td>
<td>URL to the EGI Advisory</td>
<td width="35%">Modifier</td>
<td width="10%">Timestamp</td>
<td width="5%"></td>
</tr>

<?php
	$sql = "select tag, reason, modifier, timestamp, id, enabled, cve_name, url  from cve_tags";
	if (!$res = mysql_query($sql)) {
		print "Error: " . mysql_error($link);
                exit;
        }
	$i = 0;
	while ($row = mysql_fetch_row($res)) {
		$tag = $row[0];
		$reason = $row[1];
		$modifier = $row[2];
		$timestamp = $row[3];
		$id = $row[4];
		$enabled = $row[5];
		$cve_name = $row[6];
		$url = $row[7];
		
		print "<tr>\n";
		print "<td><input type=\"checkbox\" onClick=\"document.tags.val.value='$id'; 
                                document.tags.act.value='switch';
                                document.tags.subval.value=this.checked;
                                document.tags.submit();\"";
		if ($enabled == 1) print "checked";
		print "></td>";
		print "<td align=\"center\"><a href=\"cve.php?cve=$cve_name\">$cve_name</a></td><td align=\"center\">$tag</td>\n<td>$reason</td>\n";
		print "<td><a href=\"$url\" title=\"$url\">link</a></td>\n";
		print "<td>$modifier</td>\n<td>$timestamp</td>\n";
		print "<td><span class=\"bured\" onClick=\"document.tags.val.value='$row[4]'; 
                                document.tags.act.value='del';
                                document.tags.submit();\">&nbsp;[remove]</span></td></tr>\n";
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
    echo "<br><small>Executed in ".round($totaltime, 2)." seconds</small></p>";
?>

<?php print_footer(); ?>

</body></html>

