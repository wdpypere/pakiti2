<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" >
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
    include_once("../../include/functions.php");
    include_once("../../include/mysql_connect.php");
    include_once("../../include/gui.php");

    # Actions
    $act = (isset($_GET["act"])) ? $_GET["act"] : "noop";
    
    $country = (isset($_GET["country"])) ? $_GET["country"] : "";
    $tag = (isset($_GET["tag"])) ? $_GET["tag"] : "all";
    
    switch ($act) {
	case "del":
		if (isset($_GET["siteid"])) {
			if ($enable_authz) {
                        	if (check_authz_site($siteid) != 1) {
			        	break;
	                        }
	                }
			$site_id = mysql_real_escape_string($_GET["siteid"]);
			$sql = "SELECT id FROM host WHERE site_id='".$site_id."'";
			if (!$res = mysql_query($sql)) {
			      $err = mysql_error($link);
      			}
  
		        while ($row = mysql_fetch_row($res)) {
	 	        	$host_id = $row[0];
	 		        $sql = "DELETE FROM installed_pkgs WHERE host_id='$host_id'";
			        if (!mysql_query($sql)) {
	        			$err = mysql_error($link);
		        	}
			        $sql = "DELETE FROM installed_pkgs_cves WHERE host_id='$host_id'";
			        if (!mysql_query($sql)) {
 				      $err = mysql_error($link);
		        	}
      			}

		      $sql = "DELETE FROM host WHERE site_id=$site_id";
		      if (!mysql_query($sql)) {
 			    $err = mysql_error($link);
		      }

		      $sql = "DELETE FROM site WHERE id='".$site_id."'";
		      if (!mysql_query($sql)) {
		     	 $err = mysql_error($link);
		      }       
		}
		break;
	case "noop":
		break;	
    }
?>


<html>
<head>
	<title>Pakiti Results for <?php echo $titlestring ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="pakiti.css" media="all" type="text/css" />
	<link rel="shortcut icon" type="image/ico" href="favicon.ico"> 
</head>
<body>

<?php print_header(); ?>

<!-- Start a table for the drop down boxes. -->
        <form action="" method="get" name="gform">
                <table width="100%">
                <tr align="center">
                        <td>Country:
<?php
	print "<select name=\"country\" onchange=\"gform.submit();\">";
	print "<option value=\"\">All";
	$sql = "SELECT country FROM site WHERE country != '' GROUP BY country ORDER BY country";

	$countries = mysql_query($sql) ;
	while ($row = mysql_fetch_row($countries)) {
		print '<option' ;
		if ($country ==  $row[0])
			print " selected";
		print " value=\"$row[0]\">$row[0]\n" ;
	}
	print "</select>";
?>
        </td>
	<td>Select tag:
       	  <select name="tag" onchange="gform.submit();">
          <option taga="all" <?php if ($tag == "all") print " selected"; ?>>all</option>
<?php
        # Print all admins
        $sql = "SELECT DISTINCT admin FROM host";
        if (!$res = mysql_query($sql)) {
                print "Error: " . mysql_error($link);
                exit;
        }
        while ($row = mysql_fetch_row($res)) {
                print "<option tag=\"$row[0]\"";
                if ($tag == $row[0]) print " selected";
                print ">$row[0]</option>";
        }
?>
                                </select>
                        </td>

</tr></table></form>
<?php     	
	$sql = "SELECT site.id, site.name, site.numhosts, site.country, site.roc FROM site ";
	if ($tag != "all") $sql .= ", host ";
	
	$sqlwhere = "";
	if ($enable_authz) {
		$site_ret = get_authz_site_ids();
                if ($site_ret != 1 && $site_ret != -1 && !empty($site_ret)) {
			$sqlwhere .= " ( $site_ret ) ";
                }	
	}
	if (!empty($country)) {
		if (!empty($sqlwhere)) {
			 $sqlwhere .= " AND site.country='$country'";
		} else {
			$sqlwhere .= " site.country='$country'";
		}
	}
	if ($tag != "all") {
		if (!empty($sqlwhere)) {
			$sqlwhere .= " AND host.admin='$tag' AND host.site_id=site.id "; 
		} else {
			 $sqlwhere .= " host.admin='$tag' AND host.site_id=site.id ";
		}
	}
	if (!empty($sqlwhere)) $sql .= "WHERE $sqlwhere";
        $sql .= " ORDER BY site.country, site.roc, site.name";

	if (!$sites = mysql_query($sql)) {
		print "Error: " . mysql_error($link);
		exit;
	}

	$num_of_sites = mysql_num_rows($sites);
?>
<h3>Showing <?= $num_of_sites ?> sites for <?php echo $titlestring ?></h3>

<!-- Display Output -->
<table width="100%" border="0" class="tg">
<tr>
	<td width="5%"><h5><font color="red">Avg. security/worst</font></h5></td>
	<td width="5%"><h5>Avg. CVEs/worst</h5></td>
	<td width="5%"><h5>#Hosts</h5></td>
	<td width="30%"><h5>Site name</h5></td>
	<td width="10%"><h5>ROC name</h5></td>
	<td width="14%"><h5>Country</h5></td>
	<td width="14%"><h5>Pakiti client</h5></td>
</tr>


<?php
	$bg_color_alt = 0;

	while ($row = mysql_fetch_row($sites) ) {

		$siteid = $row[0];
		$site = $row[1];

		$tld = $row[3];
		$num_hosts = $row[2];
		$country = $row[3];
		$roc = $row[4];
		$num_cves = 0;
		$num_cves_site = 0;
		$worst_site_sec = 0;
		$worst_site_other = 0;
		$worst_site_cves = 0;
		$num_up_sec_pkgs_site = 0;
		$skip = 1;

		# Get worst and average number of CVEs which has some client for the site
		$sql = "select count(distinct cve.cve_name) from installed_pkgs_cves, host, cve where installed_pkgs_cves.host_id=host.id and host.site_id=$siteid and installed_pkgs_cves.cve_id=cve.cves_id group by host.id";
		if (!$res = mysql_query($sql)) {
                        print "Error: " . mysql_error($link);
                        exit;
                }
		while ($row_site = mysql_fetch_row($res)) {
			$num_cves_site +=$row_site[0];
			if ($row_site[0] > $worst_site_cves) $worst_site_cves = $row_site[0];
		}

		# Get worst and average number of sec packages which has some client for the site
		$sql = "select count(installed_pkgs.act_version_id) FROM installed_pkgs, act_version, host WHERE act_version_id>0 AND installed_pkgs.host_id=host.id and host.site_id=$siteid and installed_pkgs.act_version_id=act_version.id AND act_version.is_sec=1 group by host.id";
		if (!$res = mysql_query($sql)) {
                        print "Error: " . mysql_error($link);
                        exit;
                }
		while ($row_site = mysql_fetch_row($res)) {
			$num_up_sec_pkgs_site +=$row_site[0];
			if ($row_site[0] > $worst_site_sec) $worst_site_sec = $row_site[0];
		}

		// Alternate background colors of rows
                if ($bg_color_alt == 1) {
	                $bg_color = 'class="bg1"';
                	$bg_color_alt = 0;
		} else {
                	$bg_color = 'class="bg2"'; 
	                $bg_color_alt = 1;
               	}

		print "<tr $bg_color>";
		if ($num_hosts == 0) {
			print "<td class=\"s_pkgs\">0</td>
            			<td class=\"cves\">0</td>";
		} else {
			print "<td class=\"s_pkgs\">" . round($num_up_sec_pkgs_site/$num_hosts,0). "/$worst_site_sec</td>";
              		print "<td class=\"cves\">" . round($num_cves_site/$num_hosts,0) . "/$worst_site_cves</td>";
		}
		print "<td>$num_hosts</td>
	        <td><a href=\"./hosts.php?s=$siteid\">$site</td>
		<td>$roc</td>
		<td>$tld</td>
		<td><a href=\"client.php?site=$site\">Download</a></td>";

                print "</tr>";
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

</body>
</html>

