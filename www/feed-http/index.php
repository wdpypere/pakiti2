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

include '../../config/config.php';
include '../../include/functions.php';
include_once '../../include/mysql_connect.php';

$starttime = start_time();

###########################################
# Open syslog

openlog("[PAKITI]", LOG_PID, LOG_LOCAL0);

if (isset($_POST["v"]))
	$version = mysql_real_escape_string(trim(htmlspecialchars($_POST["v"])));
else if (isset($_POST["version"]))
	$version = mysql_real_escape_string(trim(htmlspecialchars($_POST["version"])));
else $version = "cern_1"; // If no version is provided then set version to CERN client

# Detect type of report, if it is not Pakiti genuine then convert it
switch ($version) {
	case "cern_1":
		# Example of the report:
		##
		#ip: 128.142.145.197
		#ts: 1412031358
		#arch: x86_64
		#host: dpm-puppet01.cern.ch dpm-puppet01.ipv6.cern.ch
		#kernel: 2.6.32-431.3.1.el6.x86_64
		#packager: rpm
		#site: MYSITE
		#system: Scientific Linux CERN SLC release 6.5 (Carbon)
		##
		#CERN-CA-certs 0:20140325-2.slc6 noarch

		# Map onto the variables

		# Decrypt the report
		$data = file_get_contents('php://input');
		$tmpFileIn = tempnam("/dev/shm/", "cern_IN_");
		# Store encrypted report into the file and the use openssl smime to decode it
		if (file_put_contents($tmpFileIn, $data) === FALSE) {
			unlink($tmpFileIn);
			syslog(LOG_ERR, "Cannot write to the file '$tmpFileIn' during decoding cern_1 report");
		}
		$tmpFileOut = tempnam("/dev/shm/", "cern_OUT_");
		if (system("openssl smime -decrypt -binary -inform DER -inkey ". $cern_report_decryption_key ." -in $tmpFileIn -out $tmpFileOut") === FALSE) {
			unlink($tmpFileOut);
			unlink($tmpFileIn);
			syslog(LOG_ERR, "Cannot run openssl smime on the file '$tmpFileIn'");
		}
		# Clean up
		unlink($tmpFileIn);

		$handle = fopen("$tmpFileOut", "r");
		$lineNumber = 0;
		if ($handle) {
		    while (($line = fgets($handle)) !== false) {
			$lineNumber++;
			if ($lineNumber == 1 && trim($line) != "#") {
				unlink($tmpFileOut);
				syslog(LOG_ERR, "Bad format of the report, it should start with # '$tmpFileOut'");
			}
			if ($lineNumber > 1 && trim($line) == "#") {
				# We have reached end of header
				break;
			}
			# Get field name and value separatedly  
			$fields = explode(':', $line, 2);
			switch(trim($fields[0])) {
				case "arch": $arch = trim($fields[1]); break;
				# Get only the first hostname in the list, CERN sends all possible hostnames of the host
				case "host": $host = trim($fields[1]); break;
				case "kernel": $kernel = trim($fields[1]); break;
				case "packager": $os_type = trim($fields[1]); break;
				case "site": $site = trim($fields[1]); break;
				case "system": $os = trim($fields[1]); break;
			}
		    }

		    # Set admin and proxy
		    $admin = "WLCG";
		    $proxy = 0;
		   
		    $pkgs = "";
		    while (($line = fgets($handle)) !== false) {
			if ($line == "#" || empty($line)) continue;
			# Store packages into the internal variable
			$pkgs .= $line;
		    }
		} else {
			// error opening the file.
			unlink($tmpFileOut);
			syslog(LOG_ERR, "Cannot open file with the report '$tmpFileOut'");
		}
		fclose($handle);
		unlink($tmpFileOut);
		break;

	default:
		print "Unsupported version!";
		exit;

}
###########################################
# Checking incoming connexion

$remote_host = array_key_exists('REMOTE_HOST',$_SERVER) ? $_SERVER['REMOTE_HOST'] : "";
$remote_addr = $_SERVER['REMOTE_ADDR'];

if (!preg_match("/[a-zA-Z]/", $remote_host)) {
	if (!$remote_addr) 
		$remote_addr = "127.0.0.1";
	else 
		$remote_host = ($addr=gethostbyaddr($remote_addr)) ? $addr : $remote_addr;
}

# Check if the reporting machine is trusted proxy client
if (in_array($remote_host, $trusted_proxy_clients) !== false && $proxy == 1) {
	syslog(LOG_INFO, "Proxy $remote_host is reporting for $host");
	// Rewrite remote_host to the hostname of the pakiti client machine
	$remote_host = $host;
	$remote_addr = gethostbyname($host);
}

$contenu =  "Connection from: ".$remote_host." reporting for: ".$host;
syslog(LOG_INFO, $contenu);
if ($host == $kernel) {
	print '<html><head></head><body>Error</body></html>';
	$contenu ="bad parameters from ".$remote_addr;
	syslog(LOG_ERR, $contenu);
	closelog();
	exit;
}	


##########################################
# Processing the package list
$items = array();
if ($pkgs) {
	switch ($version) {
		case "1": 
			$pkgs = str_replace ("\\", "", $pkgs);
			preg_match_all("'<N=\'(.*?)\' V=\'(.*?)\' R=\'(.*?)\'/>'si",$pkgs,$items);
			break;
		case "2": 
			$pkgs = str_replace ("\\", "", $pkgs);
			preg_match_all("'<N=\"(.*?)\" V=\"(.*?)\" R=\"(.*?)\"/>'si",$pkgs,$items);
			break;
		case preg_match("/2\.\d\.\d/", $version)?$version:!$version:
			preg_match_all("/NEWPAKITIRPM=(.*?) (.*?)-(.*?)[ ']/i",$pkgs,$items);
			break;
		case "3": 
			$pkgs = str_replace ("\\", "", $pkgs);
			preg_match_all("|^'(.*?)' '(.*?)' '(.*?)'$|sim",$pkgs,$items);
			break;
		case "4": 
			$pkgs = str_replace ("\\", "", $pkgs);
			preg_match_all("|^'(.*?)' '(.*?)' '(.*?)' '(.*?)'$|sim",$pkgs,$items);
			break;
		case "cern_1":
			#CERN-CA-certs 0:20140325-2.slc6 noarch
			preg_match_all("|^(.*?)[\s]+(.*?)-(.*?)[\s]+(.*?)$|sim",$pkgs,$items);
			break;
	}
}
syslog(LOG_INFO,"Number of transmited pkgs: " . count($items[1]));

#########################################
#Get OS and arch ID
$sql = "LOCK TABLES os WRITE, arch WRITE, oses_group READ, repositories READ, cves_os READ" ;
if (!mysql_query($sql)) {
	syslog(LOG_ERR, "DB: Unable to lock tables #1: ".mysql_error($link));
	closelog();
	exit;
}

# Try to find OS from the package
$tmp_os = "";
if (count($items[1]) > 0) {
        if (($key = array_search("sl-release", $items[1])) !== false) {
                if (($delim = strpos($items[2][$key],":")) !== false)
                        $ver = substr($items[2][$key], $delim+1);
                else $ver = $items[2][$key];

                $tmp_os = "Scientific Linux $ver";
        }
        else if (($key = array_search("redhat-release", $items[1])) !== false) {
                if (($delim = strpos($items[2][$key],":")) !== false)
                        $ver = substr($items[2][$key], $delim+1);
                else $ver = $items[2][$key];

                $tmp_os = "Red Hat Enterprise Linux $ver";
        }
        else if (($key = array_search("sles-release", $items[1])) !== false) {
                if (($delim = strpos($items[2][$key],":")) !== false)
                        $ver = substr($items[2][$key], $delim+1);
                else $ver = $items[2][$key];

                $tmp_os = "SuSe Linux $ver";
        }
        else if (($key = array_search("hpc-release", $items[1])) !== false) {
                if (($delim = strpos($items[2][$key],":")) !== false)
                        $ver = substr($items[2][$key], $delim+1);
                else $ver = $items[2][$key];

                $tmp_os = "HPC Linux $ver";
        }
        else if (($key = array_search("centos-release", $items[1])) !== false) {
                if (($delim = strpos($items[2][$key],":")) !== false)
                        $ver = substr($items[2][$key], $delim+1);
                else $ver = $items[2][$key];

                $tmp_os = "CentOS Linux $ver";
        } else {
                # Use the one provided by the pakiti-client
                $tmp_os = $os;
        }
} else {
        $tmp_os = $os;
}

if (($os == "n/a") || ($os == "")) {
        $tmp_os = "unknown";
}

$os = $tmp_os;

$sql = "SELECT DISTINCT os.id FROM os WHERE os.os='$os'";
if (!$row = mysql_query($sql)) {
	syslog(LOG_ERR, "DB: Unable to get os id:".mysql_error($link));
	closelog();
	exit_with_unlock();
}
$item = mysql_fetch_row($row);
if (mysql_num_rows($row) == 0) {
	$sql = "INSERT INTO os (os) VALUES ('$os')";
	if (!mysql_query($sql)) {
		syslog(LOG_ERR, "DB: Unable to insert new os '$os':".mysql_error($link));
		closelog();
		exit_with_unlock();
	}
	$os_id = mysql_insert_id();
	$os_group_id = 0;
	$repo_id = 0;
} else {
	$os_id = $item[0];
	$sql = "SELECT os_group_id FROM oses_group WHERE os_id=$os_id";
	if (!$res = mysql_query($sql)) {
	        syslog(LOG_ERR, "DB: Unable to get repo id:".mysql_error($link));
	        closelog();
	        exit_with_unlock();
	}
	if ($row = mysql_fetch_row($res)) {
		$os_group_id = $row[0];
	} else {
		$os_group_id = 0;
	}
}

# Correct OS type
# It can be defined by the repository type
$sql = "SELECT DISTINCT repositories.type FROM repositories, oses_group WHERE oses_group.os_id=$os_id AND oses_group.os_group_id=repositories.os_group_id";
if (!$row = mysql_query($sql)) {
        syslog(LOG_ERR, "DB: Unable to get os id:".mysql_error($link));
        closelog();
        exit_with_unlock();
}
if (mysql_num_rows($row) > 0) {
       $os_type = $row[0];
} else {
	# Or it can be defined by CVEs source
        $sql = "SELECT id FROM cves_os WHERE os_id=$os_id";
        if (!$row = mysql_query($sql)) {
                syslog(LOG_ERR, "DB: Unable to get os id:".mysql_error($link));
                closelog();
                exit_with_unlock();
        }
        if ((mysql_num_rows($row) != 0) && (strpos($row[0], 'rh_') !== false)) {
               $os_type = "rpm";
        }
}


$sql = "SELECT arch.id FROM arch WHERE arch.arch='$arch'";
if (!$row = mysql_query($sql)) {
	syslog(LOG_ERR, "DB: Unable to get arch id:".mysql_error($link));
	closelog();
	exit_with_unlock();
}
$item = mysql_fetch_row($row);
if (mysql_num_rows($row) == 0) {
	$sql = "INSERT INTO arch (arch) VALUES ('$arch')";
	if (!mysql_query($sql)) {
		syslog(LOG_ERR, "DB: Unable to insert new arch '$arch':".mysql_error($link));
		closelog();
		exit_with_unlock();
	}
	$arch_id = mysql_insert_id();
} else {
	$arch_id = $item[0];
}

$sql = "UNLOCK TABLES" ;
if (!mysql_query($sql)) {
	syslog(LOG_ERR, "DB: Unable to unlock tables #1: ".mysql_error($link));
	closelog();
	exit;
}
##########################################
# Enter the host record.
$sql = "LOCK TABLES host WRITE, domain WRITE, site WRITE" ;
if (!mysql_query($sql)) {
	syslog(LOG_ERR, "DB: Unable to lock tables #2: ".mysql_error($link));
	closelog();
	exit;
}

# Firstly, check if there is a domain table for this host
# Check if $remote_host is really hostname and not only ip
if ($remote_host == $remote_addr)
	$domain_host = $host;
else $domain_host = $remote_host;

$dmn_i = strpos($domain_host, ".");
if ($dmn_i === false) {
	$domain = $domain_host;
} else {
	$domain = substr($domain_host, $dmn_i + 1);
}
# Check if domain ends with .local or .localdomain and try to guess real domain name
if ((substr($domain, -6, 6) == ".local") || (substr($domain, -12, 12) == ".localdomain")) {
	$domain = "unknown";
}

$sql = "SELECT id FROM domain WHERE domain='$domain'"; 
if (!$rowd = mysql_query($sql)) {
	syslog(LOG_ERR, "DB: Unable to get domain id:".mysql_error($link));
	closelog();
	exit_with_unlock();
}

if (mysql_num_rows($rowd)) {
	$item = mysql_fetch_row($rowd);
	$domain_id = $item[0];
} else {
	$sql = "INSERT INTO domain SET domain='$domain', numhosts = 0";
	if (!mysql_query($sql)) {
		$mysql_e = mysql_error();
		syslog(LOG_ERR, "DB: Unable to add domain record: $mysql_e \n $sql");
		closelog();
		exit_with_unlock();
	}
	$domain_id = mysql_insert_id();
}

$sql = "SELECT id FROM site WHERE name='$site'";
if (!$rows = mysql_query($sql)) {
        syslog(LOG_ERR, "DB: Unable to get site id:".mysql_error($link));
        closelog();
        exit_with_unlock();
}

if (mysql_num_rows($rows)) {
        $item = mysql_fetch_row($rows);
        $site_id = $item[0];
} else {
        $sql = "INSERT INTO site SET name='$site', numhosts = 0";
        if (!mysql_query($sql)) {
                $mysql_e = mysql_error();
                syslog(LOG_ERR, "DB: Unable to add site record: $mysql_e \n $sql");
                closelog();
                exit_with_unlock();
        }
        $site_id = mysql_insert_id();
}

$sql = "SELECT id FROM host WHERE host='$host' AND dmn_id=" . $domain_id;
if (!$row = mysql_query($sql)) {
	syslog(LOG_ERR, "DB: Unable to get host id:".mysql_error($link));
	closelog();
	exit_with_unlock();
}
$item = mysql_fetch_row($row);
$host_id = $item[0];

if (mysql_num_rows($row)) {
	$sql = "UPDATE host SET os_id='$os_id',kernel='$kernel',admin='$admin',conn='".$_SERVER['SERVER_PORT'].
                       	"',arch_id='$arch_id', version='$version',report_host='$remote_host',
			report_ip='$remote_addr', type='$os_type', site_id='$site_id', time=NOW() WHERE id=$host_id";
	if (!mysql_query($sql)) {
                        syslog(LOG_ERR, "DB: Unable to update host record");
                        closelog();
                        exit_with_unlock();
                }
}
else {
	$sql = "UPDATE domain SET numhosts = numhosts + 1 WHERE id='".$domain_id."'";
	if (!mysql_query($sql)) {
		syslog(LOG_ERR, "DB: Unable to update domain record");
		closelog();
		exit_with_unlock();
	}
	$sql = "UPDATE site SET numhosts = numhosts + 1 WHERE id='".$site_id."'";
	if (!mysql_query($sql)) {
		syslog(LOG_ERR, "DB: Unable to update site record");
		closelog();
		exit_with_unlock();
	}

	# Domain table stuff is done, insert into host table
	$sql = "INSERT INTO host SET host='$host', dmn_id='$domain_id',os_id='$os_id', kernel='$kernel',
			admin='$admin', conn='".$_SERVER['SERVER_PORT']."',version='$version',
			arch_id=$arch_id,report_host='$remote_host', report_ip='$remote_addr', type='$os_type',
			site_id='$site_id'";

	if (!mysql_query($sql)) {
		$mysql_e = mysql_error();
		syslog(LOG_ERR, "DB: Unable to add host record: $mysql_e \n $sql");
		closelog();
		exit_with_unlock();
	}
	$host_id = mysql_insert_id();
}

$sql = "UNLOCK TABLES" ;
if (!mysql_query($sql)) {
        syslog(LOG_ERR, "DB: Unable to unlock tables #2: ".mysql_error($link));
        closelog();
        exit;
}

###########################################
# Store the information in DB
$sql = "LOCK TABLES pkgs WRITE, installed_pkgs WRITE, installed_pkgs_cves WRITE, act_version READ, cves READ, cves_os READ, host WRITE, pkgs_exceptions READ, pkg_exception_cve READ";
if (!mysql_query($sql)) {
	syslog(LOG_ERR, "DB: Unable to lock tables #3: ".mysql_error($link));
	closelog();
	exit;
}

# Delete old records if there are new on
# Check if the report contains some changes
$pkgs_md5 = md5($pkgs);
$pkgs_md5 = md5($pkgs_md5 . $kernel);
$sql = "SELECT report_md5 FROM host WHERE id=$host_id";
if (!$row = mysql_query($sql)) {
        syslog(LOG_ERR, "DB: Unable to select report_md5 from host: ".mysql_error($link));
        closelog();
        exit_with_unlock();
}
$item = mysql_fetch_row($row);
if (mysql_num_rows($row) == 1) {
    if ($pkgs_md5 != $item[0]) {
    	$sql = "DELETE FROM installed_pkgs WHERE host_id='".$host_id."'" ;
	if (!mysql_query($sql)) {
		syslog(LOG_ERR, "DB: Unable to delete installed_pkgs for host:".mysql_error($link));
		closelog();
		exit_with_unlock();
	}
	$sql = "DELETE FROM installed_pkgs_cves WHERE host_id='".$host_id."'" ;
	if (!mysql_query($sql)) {
		syslog(LOG_ERR, "DB: Unable to delete installed_pkgs_cves for host:".mysql_error($link));
		closelog();
		exit_with_unlock();
	}
        $sql = "UPDATE host SET report_md5='$pkgs_md5', pkgs_change_timestamp=CURRENT_TIMESTAMP WHERE id=$host_id";
	if (!mysql_query($sql)) {
	    syslog(LOG_ERR, "DB: Unable to update host and set report_md5:".mysql_error($link));
            closelog();
	    exit_with_unlock();
	}
   } else {
	// The host haven't reported any new package version, in case of asynchronous mode, we can end here
	if ($asynchronous_mode == 1) {
		$sql = "UNLOCK TABLES" ;
		if (!mysql_query($sql)) {
		        syslog(LOG_ERR, "DB: Unable to unlock tables #3: ".mysql_error($link));
		        closelog();
		        exit_with_unlock();
		}
        	syslog(LOG_INFO, "Information recorded for $host in time: " . end_time($starttime));
		closelog();
		exit;
	}
   }
} else {
    $sql = "UPDATE host SET report_md5='$pkgs_md5' WHERE id=$host_id";
    if (!mysql_query($sql)) {
        syslog(LOG_ERR, "DB: Unable to update host and set report_md5:".mysql_error($link));
        closelog();
        exit_with_unlock();
    }
}

$num_of_cves = 0;
$num_of_sec = 0;
$num_of_others = 0;
$we_have_kernel = false;
$count_items = count($items[1]);

for ($i = 0; $i <= $count_items; $i++) {
	$act_version_id = NULL;

	# Data from report
	$r_pkg_name = mysql_real_escape_string($items[1][$i]);
	$r_pkg_version = mysql_real_escape_string($items[2][$i]);
	$r_pkg_rel = mysql_real_escape_string($items[3][$i]);
	$r_pkg_arch = mysql_real_escape_string($items[4][$i]);
	# If pakiti client doesn't report arch for packages, set the arch to N/A.
	if (empty($r_pkg_arch)) $r_pkg_arch = "N/A";

	if ($i == $count_items) {
		if ($we_have_kernel === false) {
			# we have last package, check if we have some package represents kernel, if not then create fake one
			$kernel_raw_version = explode("-", trim($kernel), 2);
			$r_pkg_name = "kernel";
			$r_pkg_version = "0:" . $kernel_raw_version[0];
			$kernel_release = $kernel_raw_version[1];
			# If release containst also arch, strip it
			$arch_to_be_removed = ".$arch";
			$arch_len = strlen($arch_to_be_removed);
			if (substr($kernel_raw_version[1], -$arch_len, $arch_len) == $arch_to_be_removed) {
				$kernel_release = substr($kernel_raw_version[1], 0, strlen($kernel_raw_version[1])-$arch_len); 	
			}
			$r_pkg_rel = $kernel_release;
			$r_pkg_arch = $arch;
			syslog(LOG_INFO, "Adding fake kernel $kernel ($arch) in version $r_pkg_version/$r_pkg_rel for host $host");
		} else {
			break;
		}
	} else {
		/* If host reporting kernel package which is not active, skip it */
		if (is_kernel_pkg($r_pkg_name)) {
			if (is_unused_kernel_pkg($kernel, $r_pkg_name, $r_pkg_version, $r_pkg_rel) === true) {
				continue;
			} else {
				$we_have_kernel = true;
			}
		}
	}


	/* Do not store devel packages (package name has '-devel' at the end of the name) */
	if (!$store_devel_packages && substr($r_pkg_name, -6, 6) == "-devel") continue;
	
	/* Do not store doc packages (package name has '-doc' at the end of the name) */
	if (!$store_doc_packages && substr($r_pkg_name, -4, 4) == "-doc") continue;

	/* Ignore packages set in $ignore_package_list variable */
	if (in_array($r_pkg_name, $ignore_package_list) === true) {
		continue;
	}

	# If asynchronous mode is on, only store the reported packages
	if ($asynchronous_mode == 1) {
		$sql = "SELECT id FROM pkgs WHERE name='$r_pkg_name'";

	        $result = mysql_query($sql);
	        if (!$result) {
	                syslog(LOG_ERR, "DB: Unable to fetch package id:".mysql_error($link));
		}
		if (mysql_num_rows($result) > 0) {
			$pkg_id = mysql_fetch_row($result);
			$pkg_id = $pkg_id[0];

			$sql = "INSERT INTO installed_pkgs SET host_id=$host_id,pkg_id=$pkg_id,version='$r_pkg_version'," .
			       "rel='$r_pkg_rel',act_version_id='',exp_id='',arch='$r_pkg_arch'";
        		if (!mysql_query($sql)) {
		        	$mysql_e = mysql_error();
				syslog(LOG_ERR, "DB: Unable to add host-package entry: $mysql_e ... $sql");
			}
		} else {
	                $sql = "INSERT INTO pkgs SET name='$r_pkg_name'";
        	        if (!mysql_query($sql)) {
                	        syslog(LOG_ERR, "DB: Unable to add package: ".mysql_error($link));
                        	closelog();
	                        exit_with_unlock();
        	        }
		}
	} else {
		$sql = "SELECT act_version, act_version.id, is_sec, act_rel, pkgs.id FROM pkgs, act_version WHERE pkgs.name='$r_pkg_name' " .
		       "AND pkg_id=pkgs.id AND os_group_id=$os_group_id AND arch_id=$arch_id";
		$result = mysql_query($sql);
		if (!$result) {
			syslog(LOG_ERR, "DB: Unable to fetch package id:".mysql_error($link));
		}

		if (mysql_num_rows($result) > 0) {
			$act = mysql_fetch_row($result);
			$pkg_id = $act[4];
		
			$cmp_ret = vercmp($os_type, $r_pkg_version, $r_pkg_rel,  $act[0], $act[3]);

			// Check if there is different version/release of installed package and actual version of package
			if ($cmp_ret < 0) {
				$act_version_id = $act[1];
				$act_version_is_sec = $act[2];
				if ($act_version_is_sec == 1) 
					$num_of_sec += 1;
				else $num_of_others += 1;
			} else {
				$act_version_id = NULL;
				$act_version_is_sec = 0;
			}

		}
		else {
			$sql = "INSERT INTO pkgs SET name='$r_pkg_name'";
			if (!mysql_query($sql)) {
				# Entry probably exists
				$sql = "SELECT id FROM pkgs WHERE name='$r_pkg_name'";
				$result = mysql_query($sql);
		                if (!$result) {
		                        syslog(LOG_ERR, "DB: Unable to fetch package id:".mysql_error($link));
		                }
				if (mysql_num_rows($result) > 0) {
					$pkg_id = mysql_fetch_row($result);
		                        $pkg_id = $pkg_id[0];
				} else {
					syslog(LOG_ERR, "DB: Unable to get pkg id or add a new package: ".mysql_error($link));
					closelog();
					exit_with_unlock();
				}
			} else {
				$pkg_id = mysql_insert_id();
			}
		}

		// Insert new data only if the client have reported different data then before, otherwise only update (ON DUPLICATE KEY UPDATE)
		// Look if there is an exception
		$exp_id = "";
#		$sql = "SELECT id FROM pkgs_exceptions WHERE pkg_id=$pkg_id AND version='".$items[2][$i].
#                        "' AND rel='".$items[3][$i]."' AND arch='" . $items[4][$i] . "'";
#		if (!$exp_res = mysql_query($sql)) { 
#                    $mysql_e = mysql_error();
#                    syslog(LOG_ERR, "DB: Unable to get exception ID: $mysql_e ... $sql"); 
#                }
#		if ($exp_row = mysql_fetch_row($exp_res)) {
#			$exp_id = $exp_row[0];
#		}

		$sql = "INSERT INTO installed_pkgs SET host_id=$host_id, pkg_id=$pkg_id, version='$r_pkg_version',rel='$r_pkg_rel',act_version_id='$act_version_id'," .
		       "exp_id='$exp_id',arch='$r_pkg_arch' ON DUPLICATE KEY UPDATE act_version_id='$act_version_id', id=LAST_INSERT_ID(id)";

		if (!mysql_query($sql)) { 
		    $mysql_e = mysql_error();
		    syslog(LOG_ERR, "DB: Unable to add host-package entry: $mysql_e ... $sql"); 
		}
		$installed_pkg_id = mysql_insert_id();

		# Send package name if there is a new version
		if ($report == 1) {
			if ($act_version_id != NULL) {
				print "$r_pkg_name ".$act[0];
				if (!empty($act[3])) print "-".$act[3];
				if ($act_version_is_sec == 1) {
					print " SEC ";
				} else print " ORD ";
				print $r_pkg_version;
				if (!empty($r_pkg_rel)) print "-".$r_pkg_rel;
				print "\n";
			}
		}

		# Compare against CVEs
		# Get pkg version from CVEs
		$sql = "SELECT cves.id, cves.version, cves.rel FROM cves, cves_os, host WHERE cves.pkg_id=$pkg_id AND host.id=$host_id AND cves.cves_os_id=cves_os.id " .
		       "AND cves_os.os_id=host.os_id AND strcmp(concat(cves.version,cves.rel), '$r_pkg_version$r_pkg_rel') != 0";

		if (!$cve_result = mysql_query($sql)) {
		       $mysql_e = mysql_error();
		       syslog(LOG_ERR, "DB: Unable to get cves version and release: $mysql_e ... $sql");
			exit_with_unlock();	
		}
		$cves_to_insert = array();
		while ($cve_item = mysql_fetch_row($cve_result)) {
			$cmp_ret = vercmp($os_type, $r_pkg_version, $r_pkg_rel, $cve_item[1], $cve_item[2]);
			if ($cmp_ret < 0) {
				array_push($cves_to_insert, $cve_item[0]);
				$num_of_cves += 1;
			}
		}
		$cves_to_insert_sql = "";
		foreach($cves_to_insert as $cve_id) {
			// Is there an exception?
			$exp_sql = "SELECT 1 FROM pkg_exception_cve, pkgs_exceptions WHERE pkg_exception_cve.cve_id=$cve_id " .
				   "AND pkg_exception_cve.exp_id=pkgs_exceptions.id AND pkgs_exceptions.pkg_id=$pkg_id " .
				   "AND pkgs_exceptions.version='$r_pkg_version' AND pkgs_exceptions.rel='$r_pkg_rel' AND pkgs_exceptions.arch='$r_pkg_arch'";
			if (!$res_exp = mysql_query($exp_sql)) {
				$mysql_e = mysql_error();
				syslog(LOG_ERR, "DB: Unable to get exception: $mysql_e ... $sql");
				exit_with_unlock();	
			}
			if (mysql_num_rows($res_exp) == 0) { 
				$cves_to_insert_sql .= "($host_id, $installed_pkg_id,  $cve_id),";
				$num_of_cves += 1;
			}
		}	
						
		if (!empty($cves_to_insert_sql)) {
			# Remove last comma
			$cves_to_insert_sqla = substr($cves_to_insert_sql, 0, -1);
			$sql = "INSERT IGNORE INTO installed_pkgs_cves (host_id, installed_pkg_id, cve_id) VALUES $cves_to_insert_sqla";
			if (!mysql_query($sql)) {
				$mysql_e = mysql_error();
				syslog(LOG_ERR, "DB: Unable to add entry into installed_pkgs_cves: $mysql_e ... $sql");
				exit_with_unlock();	
			}
		}
	}
}

$sql = "UNLOCK TABLES" ;
if (!mysql_query($sql)) {
        syslog(LOG_ERR, "DB: Unable to unlock tables #3: ".mysql_error($link));
        closelog();
        exit;
}
mysql_close($link);
if ($asynchronous_mode == 1) {
	syslog(LOG_INFO, "Information recorded for $host from $version in time: " . end_time($starttime));
} else {
	syslog(LOG_INFO, "Information recorded for $host from $version in time: " . end_time($starttime) . " (Sec: $num_of_sec, Others: $num_of_others, CVEs: $num_of_cves)");
}
closelog();

# Everithing is ok, so print the OK message
print "OK";
?>
