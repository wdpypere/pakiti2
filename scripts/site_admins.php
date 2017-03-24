#!/usr/bin/php
<?php
#include_once("../config/config.php");
$config = '/etc/pakiti2/pakiti2-server-egi.conf';
include_once("../include/mysql_connect.php");
include_once("../config/admins_acl.php");

function add_user($site_id, $forename, $surname, $dn)
{
	$sql = "select id, user from users where dn='$dn'";
	$res = mysql_query($sql);
	if ($res === FALSE)
		return -1;
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_row($res);
		$user_id = $row[0];
	} else {
		$name = $forename . " " . $surname;
		$sql = "insert into users (user, dn) values ('$name', '$dn')";
		mysql_query($sql);
		$user_id = mysql_insert_id();
	}

	$sql = "insert ignore into user_site (user_id, site_id) values ($user_id, $site_id)";
	mysql_query($sql);

	return ($user_id);
}

$verbose = 0;
if (isset($argv[1]) && $argv[1] == "-v") $verbose = 1;

# Get site security contacts
$seo = new DOMDocument();
$seo->load("/tmp/pakiti-seo.xml", LIBXML_NOWARNING | LIBXML_NOERROR);

if ($seo === false) {
        file_put_contents('php://stderr', "Cannot load list of Site Security Contats from the GOCDB");
        $seo_sites = array();
} else {
        $seo_sites = $seo->getElementsByTagName('SITE');
}

$err = '';
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

$deleted_users = array();
foreach ($sites as $site) {
        $site_name = mysql_real_escape_string($site->getAttribute('NAME'));
        $site_country = mysql_real_escape_string($site->getAttribute('COUNTRY'));
        $site_ngi = mysql_real_escape_string($site->getAttribute('ROC'));
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

        $added_users = array();

        foreach ($seo_sites as $seo_site) {
                $seo_site_name = $seo_site->getAttribute('NAME');

                if ($seo_site_name != $site_name) continue;

                $scs_contact = $seo_site->getElementsByTagName('CONTACT');
                foreach ($scs_contact as $contact) {
                        $scs_forename = $contact->getElementsByTagName('FORENAME');
                        $scs_surname = $contact->getElementsByTagName('SURNAME');
                        $scs_certdn = $contact->getElementsByTagName('CERTDN');

                        $forename = mysql_real_escape_string($scs_forename->item(0)->nodeValue);
                        $surname = mysql_real_escape_string($scs_surname->item(0)->nodeValue);
                        $dn = mysql_real_escape_string($scs_certdn->item(0)->nodeValue);

                        $user_id = add_user($site_id, $forename, $surname, $dn);
                        if ($user_id == -1)
                                continue;

                        if ($verbose) printf("%s %s added to %s as site SO\n", $forename, $surname, $site_name);
                        $added_users[$user_id] = 1;
                }
	    }

        foreach ($ngis as $ngi) {
                $ngi_name = mysql_real_escape_string($ngi->getAttribute('ROC_NAME'));
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

                        $forename = mysql_real_escape_string($scs_forename->item(0)->nodeValue);
                        $surname = mysql_real_escape_string($scs_surname->item(0)->nodeValue);
                        $dn = mysql_real_escape_string($scs_certdn->item(0)->nodeValue);

                        $user_id = add_user($site_id, $forename, $surname, $dn);
                        if ($user_id == -1)
                                continue;

                        if ($verbose) printf("%s %s added to %s as NGI SO\n", $forename, $surname, $site_name);
                        $added_users[$user_id] = 1;
                }
        }

        if (empty($added_users)) {
                $sql = "select user_id from user_site where site_id = $site_id";
        } else {
                $ids = implode(',', array_keys($added_users));
                $sql = "select user_id from user_site where site_id = $site_id and user_id not in ($ids)";
        }
        $res = mysql_query($sql);
        if ($res === FALSE)
                continue;
        if (mysql_num_rows($res) <= 0)
                continue;

        /* Remove users not assigned to the site anymore */
        if ($verbose) printf("Removing from %s:\n", $site_name);
        while ($row = mysql_fetch_array($res, MYSQL_NUM)) {
                $user_id = $row[0];
                $sql = "select user from users where id = $user_id";
                $user = mysql_query($sql);
                if ($verbose) printf("\t%s\n", mysql_fetch_row($user)[0]);

                $sql = "delete from user_site where site_id = $site_id and user_id = $user_id";
                mysql_query($sql);
                $deleted_users[$user_id] = 1;
        }
}

/* Remove orphaned users, i.e. those not assigned to any site any more */
foreach (array_keys($deleted_users) as $user_id) {
        $sql = "select user_id from user_site where user_id = $user_id";
        $res = mysql_query($sql);
        if (mysql_num_rows($res) > 0)
                continue;

        $sql = "select user,dn from users where id = $user_id";
        $res = mysql_query($sql);
        $row = mysql_fetch_row($res);
        $user = $row[0];
        $dn = $row[1];

        $sql = "delete from users where id = $user_id";
        mysql_query($sql);
        if ($verbose) printf("%s (%s) removed from Pakiti\n", $user, $dn);
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
