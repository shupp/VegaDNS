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

    $params = array(
        ':first_name' => $_REQUEST['first_name'],
        ':last_name'  => $_REQUEST['last_name'],
        ':phone'      => $_REQUEST['phone'],
        ':email'      => strtolower($_REQUEST['email_address'])
    );
    $q .= '
        First_Name=:first_name,
        Last_Name=:last_name,
        Phone=:phone,
        Email=:email';
    if ($_REQUEST['password']!="") {
     $q .=  ", Password='".md5($_REQUEST['password'])."'";
    }
    if($user_info['Account_Type'] == 'senior_admin') {
        $q .= ", Account_Type='".$_REQUEST['account_type']."'";
        $q .= ", Status='".$_REQUEST['status']."'";
    }
    $q .= " where cid='".get_cid($account_info['Email'])."'";

    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));

    // Update email in active sessions if necessary
    if($account_info['Email'] != strtolower($_REQUEST['email_address'])) {
        $params = array(':email' => strtolower($_REQUEST['email_address']));
        $q = "update active_sessions set Email=:email where Email='".$account_info['Email']."'";
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
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
        $params = array(
            ':first_name' => $_REQUEST['first_name'],
            ':last_name'  => $_REQUEST['last_name'],
            ':email'      => $_REQUEST['email_address'],
            ':phone'      => $_REQUEST['phone']
        );
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
        $q .= ":first_name,:last_name,:email,:phone,'".md5($_REQUEST['password'])."',";
        if($user_info['Account_Type'] == 'group_admin') {
            $q .= " 'user',
                    'active')";
        } else if($user_info['Account_Type'] == 'senior_admin') {
            $q .=   "'".$_REQUEST['account_type']."',
                    '".$_REQUEST['status']."')";
        }

        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
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
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));


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
            $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>".preg_replace('/_/', ' ', $key)."</a>";
            if($sortfield == $val) $url .= "&nbsp;<img border=0 alt='$sortway' src=images/$sortway.png>";
            $smarty->assign($key, $url);
        }

        $counter = 0;
        while($row = $stmt->fetch()) {
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
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        $row = $stmt->fetch();

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
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        $owner_info = $stmt->fetch();

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
    $pdo->query($q1) or die(print_r($pdo->errorInfo()));
    $pdo->query($q1) or die(print_r($pdo->errorInfo()));

    set_msg("User deleted successfully");
    header("Location: $base_url&mode=users&user_mode=show_users");
    exit;

}

?>
