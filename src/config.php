<?php


/*
 *
 * VegaDNS - DNS Administration Tool for use with djbdns
 *
 * CREDITS:
 * Written by Bill Shupp
 * <hostmaster@shupp.org>
 *
 * LICENSE:
 * This software is distributed under the GNU General Public License
 * Copyright 2003-2013, Bill Shupp
 * see COPYING for details
 *
 */



// Location of vegadns private directories (should be in ServerRoot of Apache)
$private_dirs = '/usr/local/apache/vegadns';

// Location of sessions dir
$session_dir = "$private_dirs/sessions";

// Location of smarty dirs
$smarty->compile_dir = "$private_dirs/templates_c";
$smarty->configs_dir = "$private_dirs/configs";
$smarty->cache_dir = "$private_dirs/cache";


// Mysql settings
$mysql_host = 'localhost';
$mysql_user = 'vegadns';
$mysql_pass = 'secret';
$mysql_db = 'vegadns';

// Local URL
$vegadns_url = 'http://127.0.0.1/';

// Contact info used in from/to addresses of email notifactions for inactive
// domains
$supportname = "The VegaDNS Team";
$supportemail = "support@example.com";

// Enable IPv6 support
$use_ipv6 = false;

// Hosts allowed to access get_data
// These are a comma delimited list of IPv4 addresses
// Such a list could look like:
// $trusted_hosts = '127.0.0.1,127.0.0.1,127.0.0.3';

$trusted_hosts = '127.0.0.1';

// Set this to 1 if you don't want to limit access to get_data
$trusted = 0;

// IP Address of the local tinydns instance.  This is the IP that will be used
// for dns lookups on authoritative information
$tinydns_ip = '127.0.0.1';

// Records per page
$per_page = 75;

// Session timeout time.  default: 3600 (1 hour)
$timeout = 3600;

// Directory containing dnsq and dnsqr
$dns_tools_dir = '/usr/local/bin';

// Set to true if you want to store sessions in mysql rather than in files
// (required when load balancing VegaDNS)
$use_mysql_sessions = false;

// Set this to a record name you want to query for version information
// over a TXT record
// $vegadns_generation_txt_record = "vegadns-generation.example.com";

/////////////////////////////////////
// NO NEED TO EDIT BELOW THIS LINE //
/////////////////////////////////////

require_once 'version.php';

if(!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])
    && !preg_match('/.*\/axfr_get.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

?>
