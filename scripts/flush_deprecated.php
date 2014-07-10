#!/usr/bin/php
<?php
$config = '/etc/pakiti/pakiti-server-egi.conf';
include("../include/mysql_connect.php");

# Backup the whole database
system('export PAKITISQL=pakiti-`date +%d.%m.%y`.sql; cd /var/tmp/pakiti-backup-egi; mysqldump --compact -h ' . $dbhost . ' -u ' . $user . ' -p"' . $password . '" ' . $dbname . ' > $PAKITISQL; bzip2 $PAKITISQL');

# Update statistics
# Select hosts which reported more then 2 days ago
$sql = "SELECT 
                site.name, cve.cve_name, date(max(host.time)), cve_tags.tag, site.country
        FROM 
                cve_tags, cve, cves, installed_pkgs_cves, host, site
        WHERE 
                cve_tags.enabled = 1 AND
                cve_tags.cve_name=cve.cve_name AND
                cve_tags.tag='EGI-Critical' AND
                cve.cves_id=cves.id AND
                cves.id=installed_pkgs_cves.cve_id AND
                installed_pkgs_cves.host_id=host.id AND
                host.site_id=site.id
        GROUP BY site.name, cve.cve_name
        ORDER BY site.name, cve.cve_name";

if (!$res = mysql_query($sql)) {
        $err = mysql_error($link);

} else {
        $sql = "";
        while ($row = mysql_fetch_row($res)) {
                $site_name = $row[0];
                $cve_name = $row[1];
                $date = $row[2];
                $cve_tag = $row[3];
                $country = $row[4];

                if ($sql != "") $sql .= ",";
                $sql .= "('$site_name', '$country', '$cve_name', '$cve_tag', '$date')";
        }

        if ($sql != "") {
                $asql = "insert ignore into cve_statistics (site_name, country, cve_name, tag, date) values $sql";
                if (!mysql_query($asql)) {
                        $err = mysql_error($link);
                }
        }
}
# Select hosts which reported more then 2 days ago
$sql = "SELECT id, dmn_id, site_id FROM host WHERE TIMESTAMPDIFF(DAY,time, CURRENT_TIMESTAMP()) >= 1";

$count = 0;
$err = '';

if (!$res = mysql_query($sql)) {
        $err = mysql_error($link);

} else {
        while ($row = mysql_fetch_row($res)) {

                # Delete data from installed_pkgs and installed_pkgs_cve
                $sql = "DELETE LOW_PRIORITY FROM installed_pkgs WHERE host_id=" . $row[0];
                if (!mysql_query($sql)) {
                        $err = mysql_error($link);
                        break;
                }

                $sql = "DELETE LOW_PRIORITY FROM installed_pkgs_cves WHERE host_id=" . $row[0];
                if (!mysql_query($sql)) {
                        $err = mysql_error($link);
                        break;
                }

                # Update hosts count for the domain
                $sql = "UPDATE domain SET numhosts=numhosts-1 WHERE id=" . $row[1];
                if (!mysql_query($sql)) {
                        $err = mysql_error($link);
                        break;
                }

                # Update hosts count for the site
                $sql = "UPDATE site SET numhosts=numhosts-1 WHERE id=" . $row[2];
                if (!mysql_query($sql)) {
                        $err = mysql_error($link);
                        break;
                }

                # Finally delete host from host table
                $sql = "DELETE FROM host WHERE id=" . $row[0];
                if (!mysql_query($sql)) {
                        $err = mysql_error($link);
                        break;
                }
                $count++;
        }
}

if ($err != "") {
        print $err;
}
?>
            
