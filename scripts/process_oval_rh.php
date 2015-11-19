#!/usr/bin/php
<?php
# Process oval data for RedHat/Sceintific Linux
# Notice: In OVAL for RH there is no distinguish between i686 and x86_64

include_once("../config/config.php");
include_once("../include/functions.php");
include_once("../include/mysql_connect.php");

$verbose = 0;

if (isset($argv[1]) && $argv[1] == "-v") $verbose = 1;

# Process each criteria, this function must be duplicated because PHP removed call by reference. processCriteriasWithReference requires os and package to be passed as a reference
function processCriteriasWithReference(&$xpath, $criteriaElement, &$res, &$os, &$package) {
	$operator = $criteriaElement->attributes->item(0)->value;

	// If we have $os and $package filled, store id
	if ($os != null && !empty($package)) {
#print "Storing $os, $package\n";
		if ($res['redhat_releases'][$os] == null) {
			$res['redhat_releases'][$os] = array();
		}
		array_push($res['redhat_releases'][$os], $package);
		// Empty package varialble
		$package = null;
	}

	// Check if the child nodes are criterion or criteria
	$criterias_query = 'def:criteria';
	$criterions_query = 'def:criterion';

	$criterias = $xpath->query($criterias_query, $criteriaElement);
	$criterions = $xpath->query($criterions_query, $criteriaElement);

	if ($criterions->length > 0) {
		// We have found criterions, so parse them. Try to find redhat version and packages names/versions
		foreach ($criterions as $criterion) {
			$comment = $criterion->attributes->item(0)->value;
			if (strpos($comment, "is installed")) {
				preg_match("/^Red Hat Enterprise Linux.* (\d+)[ ]*(Client|Server|Workstation|ComputeNode|)[ ]*is installed$/", $comment, $redhat_release);
				$os = $redhat_release[1];
#print "Got OS: $os\n";
			} elseif (strpos($comment, "is earlier than")) {
				preg_match("/^([^ ]+) is earlier than ([^-]*)-(.*)$/", $comment, $results);
				$package = array();
				$package['name'] = $results[1];
				$package['version'] = $results[2];
				$package['release'] = $results[3];
#print "Got package: {$package['name']} {$package['version']} {$package['release']} \n";
			}
		}

		// Criterions can contain both os and package under one criteria
		if ($os != null && !empty($package)) {
#print "Storing $os, $package\n";
			if (!array_key_exists($os, $res['redhat_releases'])) {
				$res['redhat_releases'][$os] = array();
			}
			array_push($res['redhat_releases'][$os], $package);
			// Empty package varialble
			$package = null;
		}
	}

	if ($criterias->length > 0) {
		// We have foung criterias, so pass them for further processing
		foreach ($criterias as $criteria) {
			if ($operator == "AND") {
				processCriteriasWithReference($xpath, $criteria, $res, $os, $package);
			} else {
				processCriterias($xpath, $criteria, $res, $os, $package);
			}
		}
	}
}
# Process each criteria
function processCriterias(&$xpath, $criteriaElement, &$res, $os, $package) {
	$operator = $criteriaElement->attributes->item(0)->value;

	// If we have $os and $package filled, store id
	if ($os != null && !empty($package)) {
#print "Storing $os, $package\n";
		if ($res['redhat_releases'][$os] == null) {
			$res['redhat_releases'][$os] = array();
		}
		array_push($res['redhat_releases'][$os], $package);
		// Empty package varialble
		$package = null;
	}

	// Check if the child nodes are criterion or criteria
	$criterias_query = 'def:criteria';
	$criterions_query = 'def:criterion';

	$criterias = $xpath->query($criterias_query, $criteriaElement);
	$criterions = $xpath->query($criterions_query, $criteriaElement);

	if ($criterions->length > 0) {
		// We have found criterions, so parse them. Try to find redhat version and packages names/versions
		foreach ($criterions as $criterion) {
			$comment = $criterion->attributes->item(0)->value;
			if (strpos($comment, "is installed")) {
				preg_match("/^Red Hat Enterprise Linux.* (\d+)[ ]*(Client|Server|Workstation|ComputeNode|)[ ]*is installed$/", $comment, $redhat_release);
				$os = $redhat_release[1];
#print "Got OS: $os\n";
			} elseif (strpos($comment, "is earlier than")) {
				preg_match("/^([^ ]+) is earlier than ([^-]*)-(.*)$/", $comment, $results);
				$package = array();
				$package['name'] = $results[1];
				$package['version'] = $results[2];
				$package['release'] = $results[3];
#print "Got package: {$package['name']} {$package['version']} {$package['release']} \n";
			}
		}

		// Criterions can contain both os and package under one criteria
		if ($os != null && !empty($package)) {
#print "Storing $os, $package\n";
			if (!array_key_exists($os, $res['redhat_releases'])) {
				$res['redhat_releases'][$os] = array();
			}
			array_push($res['redhat_releases'][$os], $package);
			// Empty package varialble
			$package = null;
		}
	}

	if ($criterias->length > 0) {
		// We have foung criterias, so pass them for further processing
		foreach ($criterias as $criteria) {
			if ($operator == "AND") {
				processCriteriasWithReference($xpath, $criteria, $res, $os, $package);
			} else {
				processCriterias($xpath, $criteria, $res, $os, $package);
			}
		}
	}
}

$sql = "SELECT value, value2 FROM settings WHERE name='RedHat CVEs URL' ORDER BY value ASC";
if (!$repositories = mysql_query($sql)) {
	die("DB: Select settings: ".mysql_error($link));
}
while ($row = mysql_fetch_row($repositories)) {
	// If value2 == 1 => the source is enabled
	if ($row[1] == 1) $oval_rh_file = $row[0];
	else continue;

# Remove white characters from begin and end
	$oval_rh_file = trim($oval_rh_file);

	$doc = new DOMDocument();
	libxml_set_streams_context(get_context());
	$doc->load($oval_rh_file);

	$xpath = new DOMXPath($doc);
	$xpath->registerNamespace('def', "http://oval.mitre.org/XMLSchema/oval-definitions-5");

	// We starts from the root element
	$query = '/def:oval_definitions/def:definitions/def:definition';

	$entries = $xpath->query($query);


	$sql = "LOCK TABLES pkgs WRITE, cves_os WRITE, cves WRITE, cve WRITE";
	if (!mysql_query($sql)) {
		die("DB: Unable to lock tables: ".mysql_error($link));
	}

	foreach ($entries as $entry) {
#  print "Processing definition: {$entry->attributes->item(0)->value}\n";  
		$res = array();

		$res['definition_id'] = $entry->attributes->item(0)->value;

		// Don't consider marginal versions, like 'Supplementary for RHEL' and the like, which
		// easily might distort results
		$platform_query = 'def:metadata/def:affected/def:platform';
		$platforms = $xpath->query($platform_query, $entry);
		$supported = False;
		foreach ($platforms as $platform) {
			if (preg_match('/^Red Hat Enterprise Linux [0-9\.]+$/', $platform->nodeValue)) {
				$supported = True;
				break;
			}
		}
		if (!$supported)
			continue;

		$el_severity = $entry->getElementsByTagName('severity')->item(0);
		if (!empty($el_severity)) {
			$res['severity'] = $el_severity->nodeValue;
		} else $res['severity'] = "n/a";

		$res['title'] = rtrim($entry->getElementsByTagName('title')->item(0)->nodeValue);
		$res['ref_url'] = $entry->getElementsByTagName('reference')->item(0)->getAttribute('ref_url');

		// Get associated CVEs
		$cve_query = 'def:metadata/def:advisory/def:cve';
		$cves = $xpath->query($cve_query, $entry);

		$res['cves'] = array();
		$res['redhat_releases'] = array();

		foreach ($cves as $cve) {
			array_push($res['cves'], $cve->nodeValue);
		}

		// Processing criteria
		$root_criterias_query = 'def:criteria';
		$root_criterias = $xpath->query($root_criterias_query, $entry);

		foreach ($root_criterias as $root_criteria) {
			$os = null;
			$package = array();
			processCriterias($xpath, $root_criteria, $res, $os, $package);
		}

		// Store the results from the $res into the DB
		// $res:
		//    [definition_id] => oval:com.redhat.rhsa:def:20110887
		//    [severity] => Critical
		//    [title] => RHSA-2011:0887: thunderbird security update (Critical)
		//    [ref_url] => https://rhn.redhat.com/errata/RHSA-2011-0887.html
		//    [cves] => Array
		//        (
		//            [0] => CVE-2011-0083
		//            [1] => CVE-2011-0085
		//        )
		//    [redhat_releases] => Array
		//        (
		//            [5] => Array
		//                (
		//                    [0] => Array
		//                        (
		//                            [name] => thunderbird
		//                            [version] => 0:2.0.0.24
		//                            [release] => 18.el5_6
		//                        )
		//
		//                )
		//
		//            [4] => Array
		//                (
		//                    [0] => Array
		//                        (
		//                            [name] => thunderbird
		//                            [version] => 0:1.5.0.12
		//                            [release] => 39.el4
		//                        )
		//
		//                )
		//
		//        )
		//
		//)

		foreach ($res['redhat_releases'] as $redhat_release => $pkgs) {
			foreach($pkgs as $pkg) {

				// Find the package id
				$sql = "SELECT id FROM pkgs WHERE name='" . $pkg['name'] ."'";
				if (!$row = mysql_query($sql)) {
					die("DB: Unable to get pkg id:".mysql_error($link));
				}
				if (mysql_num_rows($row) >= 1) {
					$item = mysql_fetch_row($row);
					$pkg_id = $item[0];
				} else {
					// PKG is not present, so insert it
					$sql = "INSERT INTO pkgs (name) VALUES ('" .$pkg['name']. "')";
#print "$sql\n";
					if (!mysql_query($sql)) {
						die("DB: Unable to add new pkg:".mysql_error($link));
					}
					$pkg_id = mysql_insert_id();
				}

				$sql = "INSERT INTO cves (def_id, cves_os_id, arch_id, pkg_id, version, rel, operator, severity, title, reference) 
					VALUES 
					('{$res['definition_id']}','rh_" . $redhat_release . "',0 ,'$pkg_id',
					 '{$pkg['version']}','{$pkg['release']}','<','{$res['severity']}',
					 '{$res['title']}','{$res['ref_url']}') 
					ON DUPLICATE KEY UPDATE id=last_insert_id(id), 
					   version='{$pkg['version']}', rel='{$pkg['release']}', severity='{$res['severity']}', 
					   title='{$res['title']}', reference='{$res['ref_url']}',
					   cves_os_id='rh_" . $redhat_release . "'";
				if (!mysql_query($sql)) {
					die("DB: Cannot insert cves data: ".mysql_error($link));
				}
#print "$sql\n";
				$ins_id = mysql_insert_id();
				// Insert detailed info about each CVE
				foreach ($res['cves'] as $rescve) {
					$sql2 = "INSERT IGNORE INTO cve (cves_id, cve_name) VALUES ($ins_id, '$rescve')";
					if (!mysql_query($sql2)) {
						die("DB: Cannot insert cves data: ".mysql_error($link));
					}
#print "$sql2\n";
				}
			}

		}
	}

}

$sql = "UNLOCK TABLES" ;
if (!mysql_query($sql)) {
	die("DB: Unable to unlock tables: ".mysql_error($link));
}
?>
