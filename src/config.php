<?php


/*
 * 
 * VegaDNS - DNS Administration Tool for use with djbdns
 * 
 * CREDITS:
 * Written by Bill Shupp
 * <bill@merchbox.com>
 * 
 * LICENSE:
 * This software is distributed under the GNU General Public License
 * Copyright 2003-2006, MerchBox.Com
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


// ADODB SQL settings
$sql_driver = 'mysql'; // Only testing MySQL at the moment
$sql_lang = 'en'; // Only testing en at the moment
$sql_host = 'localhost';
$sql_user = 'root';
$sql_pass = '';
$sql_db = 'vegadns';
$mysql_socket = '/tmp/mysql.sock';

// Local URL 
$vegadns_url = 'http://127.0.0.1/shupp/vegadns-1.1.5/';

// IPv6 support (requires patches to djbdns and ucspi-tcp) off by default
$use_ipv6 = 'FALSE';  // can be TRUE or FALSE

// Contact info used in from/to addresses of email notifactions for inactive
// domains
$supportname = "The VegaDNS Team";
$supportemail = "support@example.com";

// Hosts allowed to access get_data
// These are a comma delimited list of IPv4 addresses
// Such a list could look like:
// $trusted_hosts = '127.0.0.1,127.0.0.1,127.0.0.3';

$trusted_hosts = '127.0.0.1';

// Records per page
$per_page = 75;

// Session timeout time.  default: 3600 (1 hour)
$timeout = 3600;

// Directory containing dnsq and dnsqr
$dns_tools_dir = '/usr/local/bin';
$whois_program = '/usr/bin/whois';

// Default Permissions array for senior admin
$senior_perms['inherit_group_perms'] = 0;
$senior_perms['accouedit'] = 1;
$senior_perms['accoucreate'] = 1;
$senior_perms['accoudelete'] = 1;
$senior_perms['self_edit'] = 1;
$senior_perms['group_edit'] = 1;
$senior_perms['group_create'] = 1;
$senior_perms['group_delete'] = 1;
$senior_perms['domain_edit'] = 1;
$senior_perms['domain_create'] = 1;
$senior_perms['domain_delegate'] = 1;
$senior_perms['domain_delete'] = 1;
$senior_perms['record_edit'] = 1;
$senior_perms['record_create'] = 1;
$senior_perms['record_delete'] = 1;
$senior_perms['record_delegate'] = 1;
$senior_perms['default_record_edit'] = 1;
$senior_perms['default_record_create'] = 1;
$senior_perms['default_record_delete'] = 1;
$senior_perms['rrtype_allow_n'] = 1;
$senior_perms['rrtype_allow_a'] = 1;
$senior_perms['rrtype_allow_3'] = 1;
$senior_perms['rrtype_allow_6'] = 1;
$senior_perms['rrtype_allow_m'] = 1;
$senior_perms['rrtype_allow_p'] = 1;
$senior_perms['rrtype_allow_t'] = 1;
$senior_perms['rrtype_allow_v'] = 1;
$senior_perms['rrtype_allow_all'] = 1;

/////////////////////////////////////
// NO NEED TO EDIT BELOW THIS LINE //
/////////////////////////////////////

$version = '1.1.6';

if(!ereg(".*/index.php$", $_SERVER['PHP_SELF']) 
    && !ereg(".*/axfr_get.php$", $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

?>
