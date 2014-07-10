#!/usr/bin/php
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

#include_once("../config/config.php");
$config = '/etc/pakiti/pakiti-server-egi.conf';
#include_once("../include/functions.php");
include_once("../include/mysql_connect.php");

$which_tag = "";

if (isset($argv[1])) $which_tag = $argv[1];

$sql = "SELECT site.name, cve_tags.tag, unix_timestamp(max(host.time)), site.roc
        FROM cve_tags, cve, cves, installed_pkgs_cves, host, site, arch
        WHERE 
                host.admin = 'Nagios' AND
                cve_tags.enabled = 1 AND
                cve_tags.cve_name=cve.cve_name AND
                cve.cves_id=cves.id AND
                cves.id=installed_pkgs_cves.cve_id AND
                installed_pkgs_cves.host_id=host.id AND
                host.site_id=site.id AND
                TIMESTAMPDIFF(HOUR,host.time, CURRENT_TIMESTAMP()) <= 24 ";
if ($which_tag != "") {
        $sql .= " AND cve_tags.tag='$which_tag' ";
}
$sql .= "GROUP BY site.id ORDER BY cve_tags.tag, site.roc, site.name";

if (!$res = mysql_query($sql)) {
        print "ERROR: " . mysql_error() . "\n";
        exit;
}

$msg_body = "";

$current_tag = "";
$entries = mysql_num_rows($res);
$count = 0;
while ($row = mysql_fetch_row($res)) {
        $site = $row[0];
        $cve_tag = $row[1];
        $date = date("d.m. H:i", $row[2]);
        $roc = $row[3];

        if ($current_tag != $cve_tag) {
                $msg .= "$cve_tag ($entries)\n";
                $current_tag = $cve_tag;
        }
        $msg_body .= "  $roc  $site (last check: $date)\n        https://pakiti.egi.eu/tags_sites.php?tag=$cve_tag&site=$site\n";
        $count++;
}

$headers = 'From: irtf@mailman.egi.eu' . "\r\n" .
    'Reply-To: irtf@mailman.egi.eu' . "\r\n";

// Create a subject
$msg_header = "Dear Security Officer On Duty,\n\n";
$subject = '';
switch ($which_tag) {
        case "EGI-Critical":
                $subject = 'Pakiti Daily Reminder (' . date("d.m.Y") . ') - EGI-Critical';
                $msg_header .= "Please check the following links:\n\n";
                $msg_body .= "\n\nAnd re-open tickets/send reminders in RT accordingly. Additional Information 
                        on the procedure can be found on:\n\nhttps://wiki.egi.eu/csirt/index.php/Handling_critical_vulnerabilities";
                break;
        case "EGI-High":
                $msg_header .= "Sites marked as EGI-High follows:\n\n";
                $subject = 'Pakiti Weekly Reminder (week ' . date("W/Y") . ') - EGI-High';
                break;
        default:
                $subject = 'Pakiti Reminder (' . date("d.m.Y") . ') - ' . $which_tag;
}

$msg_footer = "\n\nCheers,\n\nyour friendly Pakiti reminder.";

// Create a message
$msg = $msg_header . $msg_body . $msg_footer;

if ($count > 0) {
        mail('irtf@mailman.egi.eu',$subject,$msg, $headers);
}
?>

