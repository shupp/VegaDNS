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



if($_REQUEST['user_mode'] != 'add_account' 
    && $_REQUEST['user_mode'] != 'add_account_now')
    $smarty->assign('user_id', $account_info['user_id']);

if(isset($_REQUEST['first_name'])) {
     $smarty->assign('first_name', $_REQUEST['first_name']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
     $smarty->assign('first_name', $account_info['first_name']);
}
if(isset($_REQUEST['last_name'])) {
     $smarty->assign('last_name', $_REQUEST['last_name']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
     $smarty->assign('last_name', $account_info['last_name']);
}
if(isset($_REQUEST['email_address'])) {
     $smarty->assign('email_address', $_REQUEST['email_address']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
     $smarty->assign('email_address', $account_info['email']);
}
if(isset($_REQUEST['phone'])) {
     $smarty->assign('phone', $_REQUEST['phone']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
     $smarty->assign('phone', $account_info['phone']);
}


if(isset($_REQUEST['password'])) {
    $smarty->assign('password', $_REQUEST['password']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
    $smarty->assign('password', $account_info['password']);
}
if(isset($_REQUEST['password2'])) {
    $smarty->assign('password2', $_REQUEST['password2']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
    $smarty->assign('password2', $account_info['password']);
}

$smarty->assign('user_account_type', $user_info['account_type']);

if($user_info['account_type'] == 'senior_admin') {

    $smarty->assign('select_account_type', array('user', 'senior_admin'));
    if(isset($_REQUEST['account_type'])) {
        $smarty->assign('type_selected', $_REQUEST['account_type']);
    } else if(isset($account_info)) {
        $smarty->assign('type_selected', $account_info['account_type']);
    }

    $smarty->assign('select_status', array('active', 'inactive'));
    if(isset($_REQUEST['status'])) {
        $smarty->assign('status_selected', $_REQUEST['status']);
    } else if(isset($account_info)) {
        $smarty->assign('status_selected', $account_info['status']);
    }

} else if($_REQUEST['user_mode'] != 'add_account' && $_REQUEST['user_mode'] != 'add_account_now'){
    $smarty->assign('account_type', $account_info['account_type']);
    $smarty->assign('account_status', $account_info['status']);
}


$smarty->display('account_form.tpl');
