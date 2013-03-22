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



// PHP INIT/SECURITY STUFF
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('allow_url_fopen', 0);
ini_set('session.use_cookies',0);
ini_set('session.use_only_cookies', 0);
ini_set('error_reporting', E_ALL & ~E_DEPRECATED);

// Check that register_globals is off
if(ini_get('register_globals')) {
    die('Error: register_globals is on.');
}
if(ini_get('safe_mode')) {
    die('Error: safe_mode is on.');
}

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

// Set version
$smarty->assign('version', $version);

// Get functions
require('src/functions.php');

// Get IPv6 Functions
require_once 'src/Net/IPv6.php';

// Connect to database
require('src/connect.php');

// Make sure the private_dirs exist and are writable
if(!is_writable($session_dir)) die("Error: $session_dir is not writabale.  Please read INSTALL");
if(!is_writable("$private_dirs/templates_c")) die("Error: $private_dirs/templates_c is not writabale.  Please read INSTALL");
if(!is_writable("$private_dirs/configs")) die("Error: $private_dirs/configs is not writabale.  Please read INSTALL");
if(!is_writable("$private_dirs/cache")) die("Error: $private_dirs/cache is not writabale.  Please read INSTALL");


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
        header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=logged_in&mode=users&user_mode=edit_account&cid=1");
        exit;
    }

    // MAIN

    $smarty->display('header.tpl');
    $smarty->display('login_screen.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['state'] == "end") {

    // CANCEL

    // End session
    $q = "delete from active_sessions where sid='".session_id()."'";
    mysql_query($q) or die(mysql_error());
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;

} else if($_REQUEST['state'] == "login_screen") {

    // LOGIN SCREEN

    $smarty->display('header.tpl');
    require('src/login_screen.php');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['state'] == "login") {

    // LOGIN

    if(!isset($_REQUEST['mode'])) {
        $auth = "FALSE";
        if(isset($_REQUEST['email']) && isset($_REQUEST['password'])) {
            $auth = authenticate_user($_REQUEST['email'], $_REQUEST['password']);
        } else {
            set_msg_err("Error: You must supply a username and password");
            header("Location: ".$_SERVER['PHP_SELF']."?".SID);
            exit;
        }
        if($auth == "TRUE") {
            header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=logged_in");
            exit;
        } else {
            set_msg_err("Error signing on: incorrect email address or password<p><a href=".$_SERVER['PHP_SELF']."?".SID."&state=help>forgot your password?</a>");
            header("Location: ".$_SERVER['PHP_SELF']."?".SID);
            exit;
        }
    } else {
        // Make sure they are logged in
        $email = verify_session();
        if($email == "") {
            set_msg_err("Error: you do not appear to be logged in");
            header("Location: ".$_SERVER['PHP_SELF']."?".SID);
            exit;
        } else {
            header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=logged_in");
            exit;
        }


    }

} else if($_REQUEST['state'] == "logged_in") {
    // SHOW MAIN SCREEN

    // First make sure they are really logged in!
    $email = verify_session();
    if($email == "") {
        set_msg_err("Error: you do not appear to be logged in.");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID);
        exit;
    } else {


        // Set base url for convenience
        $base_url = $_SERVER['PHP_SELF']."?".SID."&state=logged_in";
        // Get current account settings
        $result = mysql_query("select * from accounts where Email='$email'")
            or die(mysql_error());
        $user_info = mysql_fetch_array($result);

        // Setup smarty stuff
        $smarty->assign('email', $email);
        $smarty->assign('state', $_REQUEST['state']);
        if(isset($_REQUEST['mode']))
            $smarty->assign('mode', $_REQUEST['mode']);
        $smarty->assign('base_url', $base_url);
        $smarty->assign('logout_url', $_SERVER['PHP_SELF'].'?'.SID.'&state=end');
        $smarty->assign('account_type', $user_info['Account_Type']);
        $smarty->assign('cid', $user_info['cid']);


        if(!isset($_REQUEST['mode']) || $_REQUEST['mode'] == "main_menu") {
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;

        } else if($_REQUEST['mode'] == "domains") {

            // LIST DOMAINS

            require('src/domains.php');
            exit;

        } else if($_REQUEST['mode'] == "users") {

            // USERS

            require('src/users.php');
            exit;

        } else if($_REQUEST['mode'] == "records") {

            // LIST RECORDS FOR DOMAIN

            require('src/records.php');
            exit;

        } else if($_REQUEST['mode'] == "default_records") {

            // LIST DEFAULT RECORDS FOR NEW DOMAINS

            require('src/default_records.php');
            exit;

        } else if($_REQUEST['mode'] == "dnsquery") {

            // LIST DEFAULT RECORDS FOR NEW DOMAINS

            require('src/dnsquery.php');
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
