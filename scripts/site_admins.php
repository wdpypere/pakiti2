#!/usr/bin/php
<?php
#include_once("../config/config.php");
$config = '/etc/pakiti2/pakiti2-server-egi.conf';
include_once("../include/mysql_connect.php");
include_once("../config/admins_acl.php");

$verbose = 0;
if (isset($argv[1]) && $argv[1] == "-v") $verbose = 1;

# Get site security contacts
$seo = new DOMDocument();
$seo->load("/tmp/pakiti-seo.xml", LIBXML_NOWARNING | LIBXML_NOERROR);

if ($seo === false) {
        file_put_contents('php://stderr', "Cannot load list of Site Security Contats from the GOCDB");
        $sites = array();
} else {
        $sites = $seo->getElementsByTagName('SITE');
}

$err = '';
foreach ($sites as $site) {
        $site_name = $site->getAttribute('NAME');
        $sql = "select id from site where name='$site_name'";
        $res = mysql_query($sql);
        $row = mysql_fetch_row($res);
        $site_id = $row[0];

        $scs_contact = $site->getElementsByTagName('CONTACT');
        foreach ($scs_contact as $contact) {
                $scs_forename = $contact->getElementsByTagName('FORENAME');
                $scs_surname = $contact->getElementsByTagName('SURNAME');
                $scs_certdn = $contact->getElementsByTagName('CERTDN');

                $sc_name =  $scs_forename->item(0)->nodeValue . " " . $scs_surname->item(0)->nodeValue;
                $sc_dn = $scs_certdn->item(0)->nodeValue;

                $sql = "select id, user from users where dn='$sc_dn'";
                $res = mysql_query($sql);
                if ($verbose) print "Processing $sc_name for site $site_name\n";
                if (mysql_num_rows($res) > 0) {
                        $row = mysql_fetch_row($res);
                        $user_id = $row[0];
                        $user_name = $row[1];

                        $sql = "insert ignore into user_site (user_id, site_id) values ($user_id, $site_id)";
                        mysql_query($sql);

                        if ($sc_name != $user_name)  {
                                $sql = "update users set user='$sc_name' where id='$user_id'";
                                mysql_query($sql);
                        }
                } else {
                        $sql = "insert into users (user, dn) values ('$sc_name', '$sc_dn')";
                        mysql_query($sql);
                        $user_id = mysql_insert_id();
                        $sql = "insert ignore into user_site (user_id, site_id) values ($user_id, $site_id)";
                        mysql_query($sql);
                        if ($verbose) print "Adding $sc_name to $site_name\n";
                }
        }
}
# Get NGI security officers
$ngi_contacts = new DOMDocument();
$ngi_contacts->load("/tmp/pakiti-ngi-so.xml", LIBXML_NOWARNING | LIBXML_NOERROR);

if ($ngi_contacts === false) {
        file_put_contents('php://stderr', "Cannot load list of NGI Security Officers from the GOCDB");
        $ngis = array();
} else {
        $ngis = $ngi_contacts->getElementsByTagName('ROC');
}
$gocdb_sites = new DOMDocument();
$gocdb_sites->load("/tmp/pakiti-sites.xml", LIBXML_NOWARNING | LIBXML_NOERROR);

if ($gocdb_sites === false) {
        file_put_contents('php://stderr', "Cannot load list of sites from the GOCDB");
        $sites = array();
} else {
        $sites = $gocdb_sites->getElementsByTagName('SITE');
}

foreach ($sites as $site) {
        $site_name = $site->getAttribute('NAME');
        $site_country = $site->getAttribute('COUNTRY');
        $site_ngi = $site->getAttribute('ROC');
        $sql = "select id from site where name='$site_name'";
        $res = mysql_query($sql);
        $site_id = 0;
        if (mysql_num_rows($res) == 0) {
                $sql = "insert into site set name='$site_name', country='$site_country', numhosts = 0";
                if ($verbose) print "Inserting new site $site_name\n";
                mysql_query($sql);
                $site_id = mysql_insert_id();
        } else {
                $row = mysql_fetch_row($res);
                $site_id = $row[0];
        }

        foreach ($ngis as $ngi) {
                $ngi_name = $ngi->getAttribute('ROC_NAME');
                # We are looking for the right NGI
                if ($ngi_name != $site_ngi) continue;

                $contacts = $ngi->getElementsByTagName('CONTACT');

                foreach ($contacts as $contact) {
                        $scs_role = $contact->getElementsByTagName('ROLE_NAME');
                        $role = $scs_role->item(0)->nodeValue;

                        # If the contact is not a Security Officer for NGI, skip it
                        if ($role != 'NGI Security Officer') continue;

                        $scs_forename = $contact->getElementsByTagName('FORENAME');
                        $scs_surname = $contact->getElementsByTagName('SURNAME');
                        $scs_certdn = $contact->getElementsByTagName('CERTDN');

                        $sc_name =  $scs_forename->item(0)->nodeValue . " " . $scs_surname->item(0)->nodeValue;
                        $sc_dn = $scs_certdn->item(0)->nodeValue;

                        # Check if the user is a Pakiti administrator
                        if (in_array($sc_dn, $admin_dns)) continue;

                        $sql = "select id, user from users where dn='$sc_dn'";
                        $res = mysql_query($sql);
                        if ($verbose) print "Processing $sc_name for $ngi_name\n";
                        if (mysql_num_rows($res) > 0) {
                                $row = mysql_fetch_row($res);
                                $user_id = $row[0];
                                $user_name = $row[1];

                                $sql = "insert ignore into user_site (user_id, site_id) values ($user_id, $site_id)";
                                mysql_query($sql);

                                if ($sc_name != $user_name)  {
                                        $sql = "update users set user='$sc_name' where id='$user_id'";
                                        mysql_query($sql);
                                }
                        } else {
                                $sql = "insert into users (user, dn) values ('$sc_name', '$sc_dn')";
                                mysql_query($sql);
                                $user_id = mysql_insert_id();
                                $sql = "insert ignore into user_site (user_id, site_id) values ($user_id, $site_id)";
                                mysql_query($sql);
                                if ($verbose) print "Adding $sc_name to $site_name for NGI $ngi_name\n";
                        }
                }
        }
}

# Get CSIRT emails
$csirt = new DOMDocument();
$csirt->load("/tmp/pakiti-csirt.xml", LIBXML_NOWARNING | LIBXML_NOERROR);

if ($csirt === false) {
        file_put_contents('php://stderr', "Cannot load list of CSIRT contacts from the GOCDB");
        $sites_csirt = array();
} else {
        $sites_csirt = $csirt->getElementsByTagName('SITE');
}

foreach($sites_csirt as $site) {
        $site_name = $site->getAttribute('NAME');
        $sql = "select id, mail from site where name='$site_name'";
        $res = mysql_query($sql);
        if (mysql_num_rows($res) == 0) {
                if ($verbose) print "Site $site_name is not stored in the Pakiti\n";
                continue;
        }
        $row = mysql_fetch_row($res);
        $site_id = $row[0];
        $site_mail = $row[1];

        $csirt_contact = $site->getElementsByTagName('CSIRT_EMAIL');
        $csirt_mail = $csirt_contact->item(0)->nodeValue;
        if (empty($csirt_mail)) {
                if ($verbose) print "CSIRT mail is empty for $site_name!\n";
                continue;
        }
        if ($csirt_mail != $site_mail) {
                $sql = "update site set mail='$csirt_mail' where id=$site_id";
                if ($verbose) print "Updating CSIRT email from $site_mail to $csirt_mail for $site_name\n";
                 mysql_query($sql);
        }
}

if ($err != "") {
        print $err;
}
?>
