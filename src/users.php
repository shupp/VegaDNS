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





// Display Cancel message if necessary
if(isset($_REQUEST['user_mode']) && $_REQUEST['user_mode'] == 'cancelled') {
    set_msg("Cancelled");
}

$smarty->assign('user_mode', $_REQUEST['user_mode']);

if($_REQUEST['user_mode'] == 'edit_account') {

    // Set account id based on user type
    $id = set_edit_id($user_info);

    // Get account info
    $account_info = get_account_info($id);

    // Set user_mode_next and title
    $smarty->assign('user_mode_next', 'edit_account_now');
    $smarty->assign('account_title',  'Edit Account');
    $smarty->assign('submit', 'edit');

    $smarty->display('header.tpl');
    require('src/account_form.php');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['user_mode'] == 'edit_account_now') {

    // Set account id based on user type
    $id = set_edit_id($user_info);

    // Get account info
    $account_info = get_account_info($id);

    // Make sure this user has the right to edit this account

    // If group_admin, make sure this account belongs to the user
    if($user_info['Account_Type'] != 'senior_admin' 
            && $user_info['cid'] != $account_info['cid']) {
        if($user_info['Account_Type'] == 'group_admin' && 
            $account_info['gid'] != $user_info['cid']) {
            set_msg_err("Error: you do not have permission to edit this account");
            header("Location: $base_url");
            exit;
        } else if($user_info['Account_Type'] == 'user' && 
                    $_REQUEST['cid'] != $user_info['cid']) {
            set_msg_err("Error: you do not have permission to edit this account");
            header("Location: $base_url");
            exit;
        }
    }

    // Set title and user_mode_next in case of check failures
    $smarty->assign('user_mode_next', 'edit_account_now');
    $smarty->assign('account_title', 'Edit Account');
    $smarty->assign('submit', 'edit');

    // Check data
    require('src/check_account_data.php');

    // Update records

    $q = "update accounts set ";

    if(isset($new_gid) && $new_gid != NULL) $q .= "gid='$new_gid', ";

    $q .= "

        First_Name='".mysql_escape_string($_REQUEST['first_name'])."',
        Last_Name='".mysql_escape_string($_REQUEST['last_name'])."', 
        Phone='".mysql_escape_string($_REQUEST['phone'])."', 
        Email='".mysql_escape_string(strtolower($_REQUEST['email_address']))."'";
    if ($_REQUEST['password']!="") {
     $q .=  ", Password='".md5($_REQUEST['password'])."'";
    }
    if($user_info['Account_Type'] == 'senior_admin') {
        $q .= ", Account_Type='".$_REQUEST['account_type']."'";
        $q .= ", Status='".$_REQUEST['status']."'";
    }
    $q .= " where cid='".get_cid($account_info['Email'])."'";

    mysql_query($q) or die(mysql_error());

    // Update email in active sessions if necessary
    if($account_info['Email'] != strtolower($_REQUEST['email_address'])) {
        $q = "update active_sessions set Email='".
            strtolower($_REQUEST['email_address'])."' where Email='".$account_info['Email']."'";
        mysql_query($q) or die(mysql_error());
    }

    set_msg("Account edited successfully");
    header("Location: $base_url");
    exit;

} else if($_REQUEST['user_mode'] == 'add_account') {

    // Make sure this is a senior admin
    if($user_info['Account_Type'] != 'senior_admin'
        && $user_info['Account_Type'] != 'group_admin') {

        set_msg_err("Error: you do not have the rights to add a user");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    } else {
        $smarty->assign('user_mode_next', 'add_account_now');
        $smarty->assign('account_title', 'Add Account');
        $smarty->assign('submit', 'add');

        $smarty->display('header.tpl');
        require('src/account_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

} else if($_REQUEST['user_mode'] == 'add_account_now') {

    // Set account id based on user type
    $id = set_edit_id($user_info);

    // Get account info
    $account_info = get_account_info($id);

    // Make sure this is a senior admin
    if($user_info['Account_Type'] != 'senior_admin'
        && $user_info['Account_Type'] != 'group_admin') {

        set_msg_err("Error: you do not have the rights to add a user");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    } else {
        $smarty->assign('user_mode_next', 'add_account_now');
        $smarty->assign('account_title', 'Add Account');
        $smarty->assign('submit', 'add');

        // Check data
        require('src/check_account_data.php');

        // Add account
        $q = "insert into accounts (";
        if($user_info['Account_Type'] == 'group_admin')
        $q .= "gid,";
        $q .="  First_Name,
                Last_Name,
                Email,
                Phone,
                Password,
                Account_Type,
                Status)

                values(";
        if($user_info['Account_Type'] == 'group_admin')
        $q .= " '".$user_info['cid']."',";
        $q .= " '".mysql_escape_string($_REQUEST['first_name'])."',
                '".mysql_escape_string($_REQUEST['last_name'])."',
                '".mysql_escape_string($_REQUEST['email_address'])."',
                '".mysql_escape_string($_REQUEST['phone'])."',
                '".md5($_REQUEST['password'])."',";
        if($user_info['Account_Type'] == 'group_admin') {
            $q .= " 'user',
                    'active')";
        } else if($user_info['Account_Type'] == 'senior_admin') {
            $q .=   "'".$_REQUEST['account_type']."',
                    '".$_REQUEST['status']."')";
        }

        mysql_query($q) or die(mysql_error());
        set_msg("Account added successfully");
        header("Location: $base_url");
        exit;

    }

} else if($_REQUEST['user_mode'] == 'show_users') {

    // Make sure this is a senior admin
    if($user_info['Account_Type'] != 'senior_admin'
        && $user_info['Account_Type'] != 'group_admin') {

        set_msg_err("Error: you do not have priviledges to view user accounts");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    } else {

        if($user_info['Account_Type'] == 'group_admin') {
            $q = "select * from accounts where gid='".$user_info['cid']."'";
        } else if($user_info['Account_Type'] == 'senior_admin') {
            $q = "select * from accounts";
        }

    	// sort
    	if (!isset($_REQUEST['sortway'])) {
        	$sortway = 'asc';
    	} else if ( $_REQUEST['sortway'] == 'desc') {
        	$sortway = 'desc';
    	} else {
        	$sortway = 'asc';
    	}           

    	if (!isset($_REQUEST['sortfield'])) {
        	$sortfield = 'Account_Type';
    	} else {
        	$sortfield = $_REQUEST['sortfield'];
    	}



		$q .= " order by $sortfield  $sortway" . (($sortfield == 'Account_Type') ? ", Last_Name" :"" ) . "";
        $result = mysql_query($q) or die(mysql_error());


        $smarty->assign('user_account_type', $user_info['Account_Type']);

        // sort
        $sort_array['Name'] = 'Last_Name';
        $sort_array['Email'] = 'Email';
        $sort_array['Account_Type'] = 'Account_Type';
        $sort_array['Group_Owner'] = 'gid';
        $sort_array['Status'] = 'Status';

        $sortbaseurl = "$base_url&mode=users&user_mode=show_users";

        while(list($key,$val) = each($sort_array)) {
            $newsortway = get_sortway($sortfield, $val, $sortway);
            $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>".ereg_replace('_', ' ', $key)."</a>";
            if($sortfield == $val) $url .= "&nbsp;<img border=0 alt='$sortway' src=images/$sortway.png>";
            $smarty->assign($key, $url);
        }

        $counter = 0;
        while($row = mysql_fetch_array($result)) {
            $out_array[$counter]['name'] = $row['First_Name'].' '.$row['Last_Name'];
            $out_array[$counter]['email'] = $row['Email'];
            $out_array[$counter]['account_type'] = $row['Account_Type'];
            $out_array[$counter]['group_owner_name'] = get_groupowner_name($row['gid']);
            $out_array[$counter]['status'] = $row['Status'];
            $out_array[$counter]['edit_url'] = "$base_url&mode=users&user_mode=edit_account&cid=".$row['cid'];
            if($row['cid'] != $user_info['cid']) 
                $out_array[$counter]['delete_url'] = "$base_url&mode=users&user_mode=delete&cid=".$row['cid'];
            $counter++;
        }


        $smarty->assign('out_array', $out_array);
        $smarty->display('header.tpl');
        $smarty->display('show_users.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

} else if($_REQUEST['user_mode'] == 'delete') {

    // Make sure this is a senior admin
    if($user_info['Account_Type'] != 'senior_admin'
        && $user_info['Account_Type'] != 'group_admin') {

        set_msg_err("Error: you do not have privileges to delete this user");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    } else {
        if(!isset($_REQUEST['cid'])) {

            set_msg_err("Error: no cid supplied");
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;
        }

        // Get user info
        $q = "select * from accounts where cid='".$_REQUEST['cid']."' LIMIT 1";
        $result = mysql_query($q) or die(mysql_error());
        $row = mysql_fetch_array($result);

        $smarty->assign('name', $row['First_Name'] ." ".$row['Last_Name']);
        $smarty->assign('cancel_url', "$base_url&mode=users&user_mode=cancelled");
        $smarty->assign('delete_url', "$base_url&mode=users&user_mode=delete_now&cid=".$row['cid']);

        $smarty->display('header.tpl');
        $smarty->display('delete_user_confirm.tpl');
        $smarty->display('footer.tpl');
        exit;

    }

} else if($_REQUEST['user_mode'] == 'delete_now') {

    // Make sure the cid was given
    if(!isset($_REQUEST['cid'])) {

        set_msg_err("Error: no cid supplied");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure this is not type 'user'
    if($user_info['Account_Type'] == 'user') {

        set_msg_err("Error: you do not have privileges to delete this user");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure this group_admin has rights to delete
    if($user_info['Account_Type'] == 'group_admin') {
        $q = "select gid from accounts where cid='".$_REQUEST['cid']."'";
        $result = mysql_query($q) or die(mysql_error());
        $owner_info = mysql_fetch_array($result);

        if($user_info['cid'] != $owner_info['gid']) {
            set_msg_err("Error: you do not have privileges to delete this user");
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;
        }
    }

    // Set domains/records to user 0 for senior admins, or group id
    if($user_info['Account_Type'] == 'group_admin') {
        $q1 = "update domains set owner_id='".$user_info['cid']."' where owner_id='".$_REQUEST['cid']."'";
    } else if($user_info['Account_Type'] == 'senior_admin') {
        $q1 = "update domains set owner_id=0 where owner_id='".$_REQUEST['cid']."'";
    }
    $q2 = "delete from accounts where cid='".$_REQUEST['cid']."'";
    mysql_query($q1) or die(mysql_error());
    mysql_query($q2) or die(mysql_error());

    set_msg("User deleted successfully");
    header("Location: $base_url&mode=users&user_mode=show_users");
    exit;

}

?>
