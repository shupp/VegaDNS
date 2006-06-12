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

if(!ereg(".*/index.php$", $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}



// Check data
if($_REQUEST['first_name'] == '') {
    set_msg_err("Error: no First Name supplied");
    $smarty->display('header.tpl');
    require('src/account_form.php');
    $smarty->display('footer.tpl');
    exit;
}
if($_REQUEST['last_name'] == '') {
    set_msg_err("Error: no Last Name supplied");
    $smarty->display('header.tpl');
    require('src/account_form.php');
    $smarty->display('footer.tpl');
    exit;
}

if(!check_email_format($_REQUEST['email_address'])) {
    set_msg_err("Error: invalid email address");
    $smarty->display('header.tpl');
    require('src/account_form.php');
    $smarty->display('footer.tpl');
    exit;
}
// If the email address is changing, check that it's not already in use
if($_REQUEST['user_mode'] == 'edit_account_now') {
    if($account_info['email'] != strtolower($_REQUEST['email_address'])) {
        if(get_user_id($_REQUEST['email_address']) != NULL) {
            set_msg_err("Error: email address already in use");
            $smarty->display('header.tpl');
            require('src/account_form.php');
            $smarty->display('footer.tpl');
            exit;
        }
    }
} else if($_REQUEST['user_mode'] == 'add_account_now') {
    if(get_user_id($_REQUEST['email_address']) != NULL) {
        set_msg_err("Error: email address already in use");
        $smarty->display('header.tpl');
        require('src/account_form.php');
        $smarty->display('footer.tpl');
        exit;
    }
}

if($_REQUEST['password'] != $_REQUEST['password2']) {
    set_msg_err("Error: passwords do not match");
    $smarty->display('header.tpl');
    require('src/account_form.php');
    $smarty->display('footer.tpl');
    exit;
}


?>
