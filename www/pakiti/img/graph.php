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
$from_date = (isset($_GET["from_date"])) ? mysql_real_escape_string($_GET["from_date"]) : "";
$to_date = (isset($_GET["to_date"])) ? mysql_real_escape_string($_GET["to_date"]) : "";

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
$type="a";

switch ($type) {
	case "a":
		$sql = "SELECT 
				count(site_name) as x, UNIX_TIMESTAMP(date) as y
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

		$sql .= " GROUP BY date ORDER BY date";
	break;
}

if (!$sqlres = mysql_query($sql)) {
	print $sql;
	print mysql_error();
	exit;
}

header("Content-Type: image/png");

$res_count = mysql_num_rows($sqlres);

$x_gap = 15;
$x_max=$res_count*$x_gap; // Maximum width of the graph or horizontal axis
$y_max=300; // Maximum hight of the graph or vertical axis

$im = @ImageCreate ($x_max, $y_max);
$line_color = ImageColorAllocate ($im,10,10,10);
$base_color = ImageColorAllocate ($im,25,25,25);
$white     = ImageColorAllocate ($im,0xff,0xff,0x2f);

imagefilledrectangle($im,0,0,$x_max,$y_max,$white);
imageline($im, 0,$y_max-100,$x_max,$y_max-100,$base_color);

$x1 = 0;
$y1 = 0;
$y = 0;
$multi = 10;

if($row = mysql_fetch_row($sqlres)) {
	$x1++;
	$y1 = $row[0]*$multi;
	$y = $row[1];
}
while ($row = mysql_fetch_row($sqlres)) {
	$x2 = $x2 + $x_gap;
	$y2 = $row[0]*$multi;

#	imageline($im, $x1, $y_max-100-$y1, $x2, $y_max-100-$y2, $line_color);
	imagefilledrectangle($im, $x1, $y_max-100-$y1, $x2, $y_max-100-$y2, $line_color);
	imagestringup($im, 2, $x1, $y_max-20, date("d.m.Y", $y), $line_color); 
#	print "$x1, $y1, $x2, $y2<br>";

	$x1 = $x2;
	$y1 = $y2;
	$y = $row[1];
}
ImagePNG($im);

?>
