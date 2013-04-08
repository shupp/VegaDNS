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



$smarty->assign('domain_id', $row['domain_id']);
$smarty->assign('domain', $row['domain']);


if($owner_row != NULL) {
    $smarty->assign('owner_row', $owner_row['First_Name'].' '.$owner_row['Last_Name'].' '.$owner_row['Email']);
} else {
    $smarty->assign('owner_row', 'none');
}
if($group_owner_row != NULL) {
    $smarty->assign('group_owner_row', $group_owner_row['First_Name'].' '.$group_owner_row['Last_Name'].' '.$group_owner_row['Email']);
} else {
    $smarty->assign('group_owner_row', 'none');
}

$smarty->assign('user_account_type', $user_info['Account_Type']);

if($user_info['Account_Type'] == 'group_admin') {
    // Show list of available users
    $q = "select Email from accounts where gid='".$user_info['cid']."'";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $counter = 0;
    while($users_row = $stmt->fetch()) {
        $users_email_array[$counter] = $users_row['Email'];
        if($users_row['Email'] == $owner_row['Email'])
            $smarty->assign('user_email_selected', $users_row['Email']);
        $counter++;
    }

    // conditions for the group_admin being the owner
    if($user_info['Email'] == $owner_row['Email'])
        $smarty->assign('user_email_selected', $user_info['Email']);
    $users_email_array[$counter] = $user_info['Email'];
    $smarty->assign('users_email_array', $users_email_array);

} else if($user_info['Account_Type'] == 'senior_admin') {
    if(isset($_REQUEST['email_address'])) {
        $smarty->assign('email_address', $_REQUEST['email_address']);
    } else if($owner_row != NULL) {
        $smarty->assign('email_address', $owner_row['Email']);
    }
}

if($user_info['Account_Type'] == 'senior_admin') {
    if(isset($_REQUEST['group_email_address'])) {
        $smarty->assign('group_email_address', $_REQUEST['group_email_address']);
    } else if($group_owner_row != NULL) {
        $smarty->assign('group_email_address', $group_owner_row['Email']);
    }

}

$smarty->display('change_owner.tpl');
