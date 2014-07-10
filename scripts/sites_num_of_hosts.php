#!/usr/bin/php
<?php
$config = '/etc/pakiti/pakiti-server-egi.conf';
include_once("../include/mysql_connect.php");

$sql = "select site_id, count(id) from host group by site_id";
$res = mysql_query($sql);
while ($row = mysql_fetch_row($res)) {
        $sql = "update site set numhosts=$row[1] where id=$row[0]";
        mysql_query($sql);
}
?>

