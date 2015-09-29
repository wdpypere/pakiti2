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

include_once("../../include/mysql_connect.php");
include_once("../../include/gui.php");

$mtime = microtime(); 
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

$site = (isset($_GET["site"])) ? $_GET["site"] : "notset";
$act = (isset($_GET["act"])) ? $_GET["act"] : "noop";

# If the user is not authenticated, allow only download of the package
if (check_authz_all() == -1) {
	$act="download";
}

switch ($act) {
	case "download":
		if ($site == "notset") {
			print "Site name is not set!";
			exit;
		}
		# Rewrite SITE_NAME in the pakiti-client
		$tmpFile = tempnam("/tmp", "pakiti-client-");
		unlink($tmpFile);
		$tmpDirName = $tmpFile;
		mkdir($tmpDirName);
		system("cp -r ../../client/pakiti2-client-dist/* " . $tmpDirName);
		system("sed -i -e 's/^SITE_NAME=\"notset\"$/SITE_NAME=\"" . $site . "\"/' " . $tmpDirName . "/opt/pakiti2-client/pakiti2-client");
		system("sed -i -e 's/^SERVERS=\"notspecified\"$/SERVERS=\"" . $_SERVER['SERVER_NAME'] . ":443\"/' " . $tmpDirName . "/opt/pakiti2-client/pakiti2-client");
		# Create tar
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=pakiti2-client.tar");

		print system("tar -cf - --owner=root --group=root -C " . $tmpDirName . " opt etc");
		# Return it
		exit;
	case "noop":
		header("Cache-Control: no-cache, must-revalidate");
		print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" >';
		break;
}

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

<h2>Pakiti client for site '<?= $site; ?>'</h2>

<h3>Description</h3>
<p>
You can download custom Pakiti client which can be deployed on all nodes. The package contains Pakiti client script, CA certificates and Cron script. Cron script is installed into the /etc/cron.daily/.
</p>

<pre>
Pakiti client package contains:

/ -|
   |- etc
   |   |- cron.daily
   |      |- pakiti2-client
   |
   |- opt
      |- pakiti2-client
          |- pakiti2-client
</pre>

<h3>Usage</h3>

<ul>
  <li>Untar pakiti2-client.tar in the root /: <pre>cd / && tar xf pakiti2-client.tar</pre>
  <li>Restart Cron daemon: <pre>/etc/init.d/crond restart</pre>
</ul>

<div align="center" width="100%" style="font-size: 12pt; background: #eeffee;"><a href="?site=<?= $site ?>&act=download">Download Pakiti client package</a></div>
				
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

