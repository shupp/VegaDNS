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



// PHP INIT/SECURITY STUFF
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('allow_url_fopen', 0);
ini_set('session.use_cookies',0);

if(ini_get('register_globals')) {
    die('Error: register_globals is on.');
}
if(ini_get('safe_mode')) {
    die('Error: safe_mode is on.');
}

// Pass any error messages triggered to error handler
error_reporting(E_ALL);

// Use 4.1.1 patch only if necessary
if(phpversion() < '4.1.1') {
    if(!isset($_REQUEST)) {
        while(list($key,$value)=each($HTTP_GET_VARS)) {
            $_REQUEST[$$key] = $value;
        }
        while(list($key,$value)=each($HTTP_POST_VARS)) {
            $_REQUEST[$$key] = $value;
        }
    }
    if(!isset($_SERVER)) $_SERVER = $HTTP_SERVER_VARS;
}


// Smarty
define('SMARTY_DIR', 'smarty/');
require(SMARTY_DIR.'/Smarty.class.php');
$smarty = new Smarty;
$smarty->assign('php_self', $_SERVER['PHP_SELF']);

// Get configuration settings
require('src/config.php');

// Set mysql sockeet if necessary
if($mysql_socket != '') ini_set('mysql.default_socket', $mysql_socket);

// Check that register_globals and safe_mode are off

// Set version
$smarty->assign('version', $version);

// Get functions
require('src/functions.php');
$start_time = getmicrotime();

// Get IPv6 Functions
if($use_ipv6 == 'TRUE') require('src/ipv6.php');

// Make sure the private_dirs exist and are writable
if(!is_writable($session_dir))
    die("Error: $session_dir is not writabale.  Please read INSTALL");
if(!is_writable("$private_dirs/templates_c"))
    die("Error: $private_dirs/templates_c is not writabale.  Please read INSTALL");
if(!is_writable("$private_dirs/configs"))
    die("Error: $private_dirs/configs is not writabale.  Please read INSTALL");
if(!is_writable("$private_dirs/cache"))
    die("Error: $private_dirs/cache is not writabale.  Please read INSTALL");

// Connect to database
require('src/connect.php');


// For update_data.sh 
if(isset($_REQUEST['state']) && $_REQUEST['state'] == 'get_data') {

    // Check trusted hosts
    $trusted = 0;
    $array = explode(',',$trusted_hosts);
    while((list($key,$value) = each($array)) && $trusted == 0) {
        if(trim($value) == $_SERVER['REMOTE_ADDR']) $trusted = 1;
    }

    if($trusted == 1) {
        // EXPORT DATA
        header("Content-type: text/plain");
        require('src/data.php');
    } else {
        echo "Error: Host ".$_SERVER['REMOTE_ADDR']." is not authorized to access this page";
    }
    exit;
}

// Setup session
session_name('VDNSSessid');
session_save_path($session_dir);
session_start();
$smarty->assign('session_name', session_name());
$smarty->assign('session_id', session_id());


if(!isset($_REQUEST['state'])) {

    if(check_first_use() == 1) {
        // Add tables
        require('src/create_tables.php');
        set_msg("Welcome to VegaDNS!<br>Please edit your account settings for the initial 'senior_admin'");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=logged_in&mode=users&user_mode=edit_account&user_id=1");
        exit;
    }

    // LOGIN SCREEN

    $smarty->display('header.tpl');
    $smarty->display('login_screen.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['state'] == "end") {

    // LOGOUT

    // End session
    $q = "delete from active_sessions where sid='".session_id()."'";
    $db->Execute($q) or die($db->ErrorMsg());
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;

} else if($_REQUEST['state'] == "login") {

    // LOGIN
    require('src/auth.php');
    exit;

} else if($_REQUEST['state'] == "logged_in") {

    // SHOW MAIN SCREEN

    // First make sure they are really logged in!
    $email = verify_session();
    if($email == 'TIMEOUT') {
        set_msg_err("Error: your session has expired.  Please log in again.");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID);
        exit;
    } else if($email == "") {
        set_msg_err("Error: you do not appear to be logged in.");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID);
        exit;
    } else {


        // Set base url for convenience
        $base_url = $_SERVER['PHP_SELF']."?".SID."&state=logged_in";

        // Permissions stuff
        require('src/permissions.php');
        $my = new permissions($email);
        // Get current account settings
        $user_info = $my->account;

        // Setup smarty stuff
        $smarty->assign('logged_in_email', $email);
        $smarty->assign('state', $_REQUEST['state']);
	    if(isset($_REQUEST['mode']))
        	$smarty->assign('mode', $_REQUEST['mode']);
        $smarty->assign('base_url', $base_url);
        $smarty->assign('logout_url', $_SERVER['PHP_SELF'].'?'.SID.'&state=end');
        $smarty->assign('account_type', $user_info['account_type']);
        $smarty->assign('user_id', $user_info['user_id']);
        $smarty->assign('home', $my->groups['name']);
        if(isset($_REQUEST['expanded'])) {
            $_SESSION['expanded'] = $_REQUEST['expanded'];
        }
        if(isset($_REQUEST['group'])) {
            if($user_info['account_type'] == 'senior_admin') {
                if($my->returnGroup($_REQUEST['group'], NULL) == NULL) {
                    set_msg_err("Error: group with id ".$_REQUEST['group']." does not exist");
                    header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=logged_in");
                    exit;
                }
            } else {
                // Check if this is their group
                if($my->isMyGroup($_REQUEST['group']) == NULL) {
                    set_msg_err("Error: you do not have permission to access resources for requested group with id ".$_REQUEST['group']);
                    header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=logged_in");
                    exit;
                }
            }
            $_SESSION['group'] = $_REQUEST['group'];
        } else if(!isset($_SESSION['group'])) {
            $_SESSION['group'] = $my->account['group_id'];
        }
        $smarty->assign('group', $_SESSION['group']);

        // Figure h_bottom_image to display in menu
        if(!isset($my->groups['subgroups'])) {
            $last_sub = 1;
        } else if(count($my->groups['subgroups']) == 1) {
            $last_sub = 1;
        } else {
            $last_sub = NULL;
        }

        // Help calculate whether to display menu row padding image
        if(isset($my->groups['subgroups'])) {
            $parent_subs = count($my->groups['subgroups']);
        } else {
            $parent_subs = 0;
        }
        $smarty->assign('menurows', $my->getMenuRows($my->groups,1,$last_sub));


        if(!isset($_REQUEST['mode'])) $_REQUEST['mode'] = 'groups';

        if($_REQUEST['mode'] == "main_menu") {

            // Main Menu - no longer used
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;

        } else if($_REQUEST['mode'] == "groups") {

            // GROUPS

            require('src/groups.php');
            exit;

        } else if($_REQUEST['mode'] == "domains") {

            // DOMAINS

            require('src/domains.php');
            exit;

        } else if($_REQUEST['mode'] == "users") {

            // USERS

            require('src/users.php');
            exit;

        } else if($_REQUEST['mode'] == "records") {

            // DOMAIN RECORDS

            require('src/records.php');
            exit;

        } else if($_REQUEST['mode'] == "default_records") {

            // DEFAULT RECORDS FOR NEW DOMAINS

            require('src/default_records.php');
            exit;

        } else if($_REQUEST['mode'] == "log") {

            // DISPLAY LOG

            require('src/view_log.php');
            exit;

        } else if($_REQUEST['mode'] == "dnsquery") {

            // DNS QUERIES

            require('src/dnsquery.php');
            exit;

        } else if($_REQUEST['mode'] == "whoisquery") {

            // WHOIS QUERIES

            require('src/whoisquery.php');
            exit;

        } else {
            die("Error: illegal mode\n");
        }
    }

} else if($_REQUEST['state'] == "help") {

        require('src/help.php');
        exit;


}




?>
