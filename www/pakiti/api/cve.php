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
$type = (isset($_GET["type"])) ? mysql_real_escape_string($_GET["type"]) : "csv";
 
if ($cve != "") {
	$sql = "select url from cve_tags where cve_name='$cve'";
	if (!$res = mysql_query($sql)) {
		print "ERROR: " . mysql_error();
      	}
	$row = mysql_fetch_row($res);
	$advisory_url = $row[0];

	$sql = "select os.os, pkgs.name, cves.version, cves.rel, cves.operator from pkgs, cves, cve, os, cves_os where cve.cve_name='$cve' and cves.id=cve.cves_id and cves.pkg_id=pkgs.id and cves.cves_os_id=cves_os.id and cves_os.os_id=os.id";

      if ($os != "") {
	$sql .= " and (os.os='$os' or os.os=' $os')  order by pkgs.name";
      } else {
        $sql .= " order by os.os, pkgs.name";
      }

      if (!$res = mysql_query($sql)) {
		print "ERROR: " . mysql_error();
      }

  switch ($type) {
    case "csv":
	header("Content-Type: text/plain");
	print "CVE,Os,Package name,Operator,Package version\n";

	while($row = mysql_fetch_row($res) ) {
	      $host_os = trim($row[0]);
	      $pkg_name = $row[1];
	      $pkg_version = $row[2] . "-" . $row[3];
              $operator = $row[4];
 
	      print "$cve,$host_os,$pkg_name,$operator,$pkg_version\n";
	}
	break;
    case "xml":
	header("Content-Type: text/xml; charset=utf-8");
        print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\n";

	print " <cve name=\"$cve\" advisory_url=\"$advisory_url\">";
	$current_os = "";
	$os_changed = 0;
	while($row = mysql_fetch_row($res) ) {
              $host_os = trim($row[0]);
              $pkg_name = $row[1];
              $pkg_version = $row[2] . "-" . $row[3];
	      $operator = ($row[4] == ">" ? "is older than" : ($row[4] == "<" ? "is earlier than" :  ($row[4] == "=" ? "equals" : "unknown")));

	      if ($current_os != $host_os) {
		if ($current_os != "") {
		  print "  </os>";
		}
		print "  <os name=\"$host_os\">";
		$current_os = $host_os;
	      }
              print "<pkg name=\"$pkg_name\" operator=\"$operator\" version=\"$pkg_version\" />";
        }
	print "  </os>";
	print " </cve>";
	break;
  }
}
?>
