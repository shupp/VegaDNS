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



$smarty->assign('domain_id', $row['domain_id']);
$smarty->assign('domain', $row['domain']);


if($owner_row != NULL) {
    $smarty->assign('owner_row', $owner_row['first_name'].' '.$owner_row['last_name'].' '.$owner_row['email']);
} else {
    $smarty->assign('owner_row', 'none');
}
if($group_owner_row != NULL) {
    $smarty->assign('group_owner_row', $group_owner_row['first_name'].' '.$group_owner_row['last_name'].' '.$group_owner_row['email']);
} else {
    $smarty->assign('group_owner_row', 'none');
}

$smarty->assign('user_account_type', $user_info['account_type']);

if($user_info['account_type'] == 'group_admin') {
    // Show list of available users
    $q = "select email from accounts where group_id='".$user_info['user_id']."'";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    $counter = 0;
    while($users_row = $result->FetchRow()) {
        $users_email_array[$counter] = $users_row['email'];
        if($users_row['email'] == $owner_row['email'])
            $smarty->assign('user_email_selected', $users_row['email']);
        $counter++;
    }

    // conditions for the group_admin being the owner
    if($user_info['email'] == $owner_row['email'])
        $smarty->assign('user_email_selected', $user_info['email']);
    $users_email_array[$counter] = $user_info['email'];
    $smarty->assign('users_email_array', $users_email_array);

} else if($user_info['account_type'] == 'senior_admin') {
    if(isset($_REQUEST['email_address'])) {
        $smarty->assign('email_address', $_REQUEST['email_address']);
    } else if($owner_row != NULL) {
        $smarty->assign('email_address', $owner_row['email']);
    }
}

if($user_info['account_type'] == 'senior_admin') {
    if(isset($_REQUEST['group_email_address'])) {
        $smarty->assign('group_email_address', $_REQUEST['group_email_address']);
    } else if($group_owner_row != NULL) {
        $smarty->assign('group_email_address', $group_owner_row['email']);
    }

}

$smarty->display('change_owner.tpl');
