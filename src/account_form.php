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

if(!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}



if($_REQUEST['user_mode'] != 'add_account'
    && $_REQUEST['user_mode'] != 'add_account_now')
    $smarty->assign('cid', $id);

if(isset($_REQUEST['first_name'])) {
     $smarty->assign('first_name', $_REQUEST['first_name']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
     $smarty->assign('first_name', $account_info['First_Name']);
}
if(isset($_REQUEST['last_name'])) {
     $smarty->assign('last_name', $_REQUEST['last_name']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
     $smarty->assign('last_name', $account_info['Last_Name']);
}
if(isset($_REQUEST['email_address'])) {
     $smarty->assign('email_address', $_REQUEST['email_address']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
     $smarty->assign('email_address', $account_info['Email']);
}
if(isset($_REQUEST['phone'])) {
     $smarty->assign('phone', $_REQUEST['phone']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
     $smarty->assign('phone', $account_info['Phone']);
}


if(isset($_REQUEST['password'])) {
    $smarty->assign('password', $_REQUEST['password']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
    $smarty->assign('password', $account_info['Password']);
}
if(isset($_REQUEST['password2'])) {
    $smarty->assign('password2', $_REQUEST['password2']);
} else if($_REQUEST['user_mode'] == 'edit_account') {
    $smarty->assign('password2', $account_info['Password']);
}

$smarty->assign('user_account_type', $user_info['Account_Type']);

if($user_info['Account_Type'] == 'senior_admin') {

    $smarty->assign('select_account_type', array('user', 'group_admin', 'senior_admin'));
    if(isset($_REQUEST['account_type'])) {
        $smarty->assign('type_selected', $_REQUEST['account_type']);
    } else if(isset($account_info)) {
        $smarty->assign('type_selected', $account_info['Account_Type']);
    }

    $smarty->assign('select_status', array('active', 'inactive'));
    if(isset($_REQUEST['status'])) {
        $smarty->assign('status_selected', $_REQUEST['status']);
    } else if(isset($account_info)) {
        $smarty->assign('status_selected', $account_info['Status']);
    }

} else if($_REQUEST['user_mode'] != 'add_account' && $_REQUEST['user_mode'] != 'add_account_now'){
    $smarty->assign('account_type', $account_info['Account_Type']);
    $smarty->assign('account_status', $account_info['Status']);
}

if($user_info['Account_Type'] == 'senior_admin') {
    if(isset($_REQUEST['group_email_address'])) {
        $smarty->assign('group_email_address', $_REQUEST['group_email_address']);
    } else if($_REQUEST['user_mode'] == 'edit_account') {
        $smarty->assign('group_email_address', get_groupowner_email($account_info['gid']));
    }
}

$smarty->display('account_form.tpl');
