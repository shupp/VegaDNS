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





if(!isset($_REQUEST['user_mode']) || $_REQUEST['user_mode'] == 'delete_cancelled') {
    if(isset($_REQUEST['user_mode']) && $_REQUEST['user_mode'] == 'delete_cancelled') {

        // Set cancelled message
        set_msg('Delete Cancelled');
    }
    $_REQUEST['user_mode'] = 'show_users';
}
$smarty->assign('user_mode', $_REQUEST['user_mode']);




if($_REQUEST['user_mode'] == 'show_users') {

    if(isset($_REQUEST['recursive'])) {
        $groupquery = $my->returnSubgroupsQuery($my->returnGroup($group, NULL), 
NULL);
        $smarty->assign('recursive', ' checked');
    } else {
        $groupquery = " group_id=".$db->Quote($_SESSION['group']);
    }

    // See if we should show the new-user link
    if($my->canCreateUsers(NULL, NULL)) {
        $smarty->assign('add_account_url', $base_url."&mode=users&user_mode=add_account");
    }

    $q = "select * from accounts where group_id='".$_SESSION['group']."' ";
    // Get search string if it exists
    if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
        $tempstring = ereg_replace("[*]", "%", $_REQUEST['search']);
        $tempstring = ereg_replace("[ ]", "%", $tempstring);
        $searchstring = " and last_name like ".$db->Quote('%'.$tempstring.'%')."";
        $smarty->assign('search', $_REQUEST['search']);
        $smarty->assign('searchtexttag', " matching \"".$_REQUEST['search']."\"");
        $search = $_REQUEST['search'];
    } else {
        $searchstring = "";
        $search = "";
    }

    $q .= $searchstring;
    // Get scope of accounts list, if it exists
    if(isset($_REQUEST['scope']) && $_REQUEST['scope'] != "") {
        $searchstring = "";
        $search = "";
        $scope = $_REQUEST['scope'];
        $smarty->assign('scope', $_REQUEST['scope']);

        if($scope != "num") {
            $sq = " and last_name regexp \"^[$scope" . strtoupper($scope) . "]\"";
        } else {
            $sq = " and last_name regexp \"^[0-9]\"";
        }
    } else {
        $sq = "";
    }

    $q .= $sq;
	// sort
	if (!isset($_REQUEST['sortway'])) {
    	$sortway = 'asc';
	} else if ( $_REQUEST['sortway'] == 'desc') {
    	$sortway = 'desc';
	} else {
    	$sortway = 'asc';
	}           

	if (!isset($_REQUEST['sortfield'])) {
    	$sortfield = 'account_type';
	} else {
    	$sortfield = $_REQUEST['sortfield'];
	}


    $q .= " order by $sortfield  $sortway" . (($sortfield == 'account_type') ? ", last_name" :"" ) . "";
    $result = $db->Execute($q) or die($db->ErrorMsg()." q: $q");
    $totalitems = $result->RecordCount();

    $smarty->assign('user_account_type', $user_info['account_type']);

    // Pagination
    require_once("src/pagination.php");

    // sort
    $sort_array['name'] = 'last_name';
    $sort_array['email'] = 'email';
    $sort_array['account_type'] = 'account_type';
    $sort_array['status'] = 'status';

    $sortbaseurl = "$base_url&mode=users&user_mode=show_users";

    while(list($key,$val) = each($sort_array)) {
        $newsortway = get_sortway($sortfield, $val, $sortway);
        $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>".ereg_replace('_', ' ', $key)."</a>";
        if($sortfield == $val) $url .= "&nbsp;<img border=0 alt='$sortway' src=images/$sortway.png>";
        $smarty->assign($key, $url);
    }

    
    $out_array = array();
    if($totalitems > 0) {
        $account_count = 0;
        // Actually list accounts
        while(++$account_count && !$result->EOF && ($row = $result->FetchRow())
            && ($account_count <= $last_item)) {

            if($account_count < $first_item) continue;

            $out_array[$account_count]['name'] = $row['last_name'].', '.$row['first_name'];
            $out_array[$account_count]['email'] = $row['email'];
            $out_array[$account_count]['account_type'] = $row['account_type'];
            $out_array[$account_count]['group_owner_name'] = get_groupowner_name($row['group_id']);
            $out_array[$account_count]['status'] = $row['status'];

            // Check permissions
            if($row['user_id'] == $user_info['user_id']) {
                if($my->canEditSelf())
                    $out_array[$account_count]['edit_url'] = "$base_url&mode=users&user_mode=edit_account&user_id=".$row['user_id'];
            } else if($my->canEditUser($row['user_id'])) {
                $out_array[$account_count]['edit_url'] = "$base_url&mode=users&user_mode=edit_account&user_id=".$row['user_id'];
            }
            if($row['user_id'] != $user_info['user_id']) 
                $out_array[$account_count]['delete_url'] = "$base_url&mode=users&user_mode=delete&user_id=".$row['user_id'];
        }
    }

    $smarty->assign('all_url', "$base_url&mode=users&user_mode=show_users&page=all&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    $smarty->assign('first_item', $first_item);
    $smarty->assign('last_item', $last_item);
    $smarty->assign('totalitems', $totalitems);
    $smarty->assign('totalpages', $totalpages);
    $smarty->assign('out_array', $out_array);

    $smarty->display('header.tpl');
    $smarty->display('show_users.tpl');
    $smarty->display('footer.tpl');
    exit;
} else if($_REQUEST['user_mode'] == 'edit_account') {

    // Make sure the user_id was given
    if(!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == "") {
        $fail = TRUE;
        set_msg_err("Error: no user_id given");

    // See if it's a self edit, and if that's allowed
    } else if($_REQUEST['user_id'] == $my->account['user_id']) {
        $self_edit = TRUE;
        $user_perms = $my->account['permissions'];
        if($my->account['permissions']['self_edit'] != 1) {
            $fail = TRUE;
            set_msg_err("Error: you do not have privileges to edit your own account");
        } else {
            $account_info = $my->account;
        }

    // See if I can edit accounts
    } else if(!$my->canEditUser($_REQUEST['user_id'])) {
        $fail = TRUE;
        set_msg_err("Error: you do not have enough privileges to edit this account");
    }

    // Exit if necesssary
    if(isset($fail)) {
        header("Location: $base_url&mode=users");
        exit;
    }

    // Setup default permissions
    if($my->account['account_type'] == 'senior_admin') {
        // Allow all permissions
        $smarty->assign('default_perms', $senior_perms);
    } else {
        $smarty->assign('default_perms', $my->account['permissions']);
    }
    if(!isset($user_perms)) {
        $q = "select * from user_permissions where user_id=".$db->Quote($_REQUEST['user_id']);
        $result = $db->Execute($q) or die($db->ErrorMsg());
        $user_perms = $result->FetchRow();
    }
    if($user_perms['inherit_group_perms'] == 1) {
        $smarty->assign('perms_type', 'inherit');
    } else {
        $smarty->assign('perms_type', 'define');
    }
    $smarty->assign('user_perms', $user_perms);
    $smarty->assign('group_perms', $my->returnGroupPermissions($_SESSION['group']));

    // Get account info if necessary
    if(!isset($account_info)) 
        $account_info = get_account_info($_REQUEST['user_id']);

    // Set user_mode_next and title
    $smarty->assign('user_mode_next', 'edit_account_now');
    $smarty->assign('account_title',  'Edit Account');
    $smarty->assign('submit', 'edit');

    $smarty->display('header.tpl');
    require('src/account_form.php');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['user_mode'] == 'edit_account_now') {

    // Make sure the user_id was given
    if(!isset($_REQUEST['user_id']) || $_REQUEST['user_id'] == "") {
        $fail = TRUE;
        set_msg_err("Error: no user_id given");

    // See if it's a self edit, and if that's allowed
    } else if($_REQUEST['user_id'] == $my->account['user_id']) {
        $self_edit = TRUE;
        $user_perms = $my->account['permissions'];
        if($my->account['permissions']['self_edit'] != 1) {
            $fail = TRUE;
            set_msg("Error: you do not have privileges to edit your own account");
        } else {
            $account_info = $my->account;
        }

    // See if I can edit accounts
    } else if(!$my->canEditUser($_REQUEST['user_id'])) {
        $fail = TRUE;
        set_msg("Error: you do not have enough privileges to edit this account");
    }

    // Exit if necesssary
    if(isset($fail)) {
        header("Location: $base_url&mode=users");
        exit;
    }

    // Setup default permissions
    if($my->account['account_type'] == 'senior_admin') {
        // Allow all permissions
        $default_perms = $senior_perms;
        $smarty->assign('default_perms', $senior_perms);
    } else {
        $default_perms = $my->account['permissions'];
        $smarty->assign('default_perms', $my->account['permissions']);
    }
    if(!isset($user_perms)) {
        $q = "select * from user_permissions where user_id=".$db->Quote($_REQUEST['user_id']);
        $result = $db->Execute($q) or die($db->ErrorMsg());
        $user_perms = $result->FetchRow();
    }
    $smarty->assign('user_perms', $user_perms);
    $smarty->assign('group_perms', $my->returnGroupPermissions($_SESSION['group']));


    // Get account info if necessary
    if(!isset($account_info)) 
        $account_info = get_account_info($_REQUEST['user_id']);

    // Set title and user_mode_next in case of check failures
    $smarty->assign('user_mode_next', 'edit_account_now');
    $smarty->assign('account_title', 'Edit Account');
    $smarty->assign('submit', 'edit');

    // Check data
    require('src/check_account_data.php');

    // Update records

    $q = "update accounts set ";

    $q .= "

        first_name=".$db->Quote($_REQUEST['first_name']).",
        last_name=".$db->Quote($_REQUEST['last_name']).", 
        phone=".$db->Quote($_REQUEST['phone']).", 
        email=".$db->Quote(strtolower($_REQUEST['email_address']))."";
    if ($_REQUEST['password']!="") {
     $q .=  ", password='".md5($_REQUEST['password'])."'";
    }
    if($user_info['account_type'] == 'senior_admin') {
        $q .= ", account_type='".$_REQUEST['account_type']."'";
        $q .= ", status='".$_REQUEST['status']."'";
    }
    $q .= " where user_id='".get_user_id($account_info['email'])."'";

    $db->Execute($q) or die($db->ErrorMsg());

    // Update email in active sessions if necessary
    if($account_info['email'] != strtolower($_REQUEST['email_address'])) {
        $q = "update active_sessions set email='".
            strtolower($_REQUEST['email_address'])."' where email='".$account_info['email']."'";
        $db->Execute($q) or die($db->ErrorMsg());
    }

    // Update permissions
    if(isset($_REQUEST['perms_type']) && $_REQUEST['perms_type'] == 'inherit') {
        $inherit = 1;
    } else {
        $inherit = NULL;
    }
    $q = $my->returnEditAccountPermQuery($_REQUEST['user_id'], $inherit);
    $result = $db->Execute($q) or die($db->ErrorMsg());

    set_msg("Account edited successfully");
    header("Location: $base_url&mode=users");
    exit;

} else if($_REQUEST['user_mode'] == 'add_account') {

    // Check permissions
    if(!$my->canCreateUsers(NULL,NULL)) {
        set_msg_err("Error: you do not have the rights to add a user");
        header("Location: $base_url&mode=users");
        exit;
    } else {


        // Setup default permissions
        if($my->account['account_type'] == 'senior_admin') {
            // Allow all permissions
            $smarty->assign('default_perms', $senior_perms);
            $smarty->assign('user_perms', $senior_perms);
        } else {
            $smarty->assign('user_perms', $my->account['permissions']);
            $smarty->assign('default_perms', $my->account['permissions']);
        }
        $smarty->assign('group_perms', $my->returnGroupPermissions($_SESSION['group']));

        $smarty->assign('user_mode_next', 'add_account_now');
        $smarty->assign('account_title', 'Add Account');
        $smarty->assign('submit', 'add');

        $smarty->display('header.tpl');
        require('src/account_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

} else if($_REQUEST['user_mode'] == 'add_account_now') {

    // Check permissions
    if(!$my->canCreateUsers(NULL,NULL)) {
        set_msg_err("Error: you do not have the rights to add a user");
        header("Location: $base_url&mode=users");
        exit;
    } else {


        // Setup default permissions
        if($my->account['account_type'] == 'senior_admin') {
            // Allow all permissions
            $smarty->assign('default_perms', $senior_perms);
        } else {
            $smarty->assign('default_perms', $my->account['permissions']);
        }
        $smarty->assign('group_perms', $my->returnGroupPermissions($_SESSION['group']));

        $smarty->assign('user_mode_next', 'add_account_now');
        $smarty->assign('account_title', 'Add Account');
        $smarty->assign('submit', 'add');

        // Check data
        require('src/check_account_data.php');

        // Add account
        $q = "insert into accounts (";
        $q .= "group_id,
                first_name,
                last_name,
                email,
                phone,
                password,
                account_type,
                status)

                values(";
        $q .= " '".$_SESSION['group']."',";
        $q .= " ".$db->Quote($_REQUEST['first_name']).",
                ".$db->Quote($_REQUEST['last_name']).",
                ".$db->Quote($_REQUEST['email_address']).",
                ".$db->Quote($_REQUEST['phone']).",
                '".md5($_REQUEST['password'])."',";
        if($user_info['account_type'] != 'senior_admin') {
            $q .= " 'user',
                    'active')";
        } else {
            $q .=   "'".$_REQUEST['account_type']."',
                    '".$_REQUEST['status']."')";
        }

        $db->Execute($q) or die($db->ErrorMsg());

        // Add permissions
        if($_REQUEST['perms_type'] == "inherit") {
            $q = "insert into user_permissions (user_id,inherit_group_perms)"
                ."values(".$db->Quote($my->returnUserID($_REQUEST['email_address']))
                .",1)";

        } else {
            $q = $my->returnCreateUserPermQuery($_REQUEST['email_address']);
        }
        $db->Execute($q) or die($db->ErrorMsg());

        set_msg("Account added successfully");
        header("Location: $base_url&mode=users");
        exit;

    }


} else if($_REQUEST['user_mode'] == 'delete') {

    // Make sure the id was given
    if(!isset($_REQUEST['user_id'])) {
        $fail = TRUE;
        set_msg_err("Error: no user_id supplied");
    } else if(!$my->canDeleteUsers(NULL,NULL)) {
    // Check permissions
        $fail = TRUE;
        set_msg_err("Error: you do not have the rights to delete a user");
    }

    if(isset($fail)) {
        header("Location: $base_url&mode=users");
        exit;
    } else {

        // Get user info
        $q = "select * from accounts where user_id='".$_REQUEST['user_id']."' LIMIT 1";
        $result = $db->Execute($q) or die($db->ErrorMsg());
        $row = $result->FetchRow();

        $smarty->assign('name', $row['first_name'] ." ".$row['last_name']);
        $smarty->assign('cancel_url', "$base_url&user_mode=delete_cancelled");
        $smarty->assign('delete_url', "$base_url&mode=users&user_mode=delete_now&user_id=".$row['user_id']);

        $smarty->display('header.tpl');
        $smarty->display('delete_user_confirm.tpl');
        $smarty->display('footer.tpl');
        exit;

    }

} else if($_REQUEST['user_mode'] == 'delete_now') {

    // Make sure the id was given
    if(!isset($_REQUEST['user_id'])) {
        $fail = TRUE;
        set_msg_err("Error: no user_id supplied");
    } else {

        $can_delete = $my->canDeleteUser($_REQUEST['user_id']);
        // Check permissions
        if($can_delete == NULL) {
            $fail = TRUE;
            set_msg_err("Error: ".$_REQUEST['user_id']." does not exist");
        } else if($can_delete == FALSE) {
            $fail = TRUE;
            set_msg_err("Error: you do not have permission to delete this user");
        }
    }

    if(isset($fail)) {
        header("Location: $base_url&mode=users");
        exit;
    }

    $q = "delete from accounts where user_id='".$_REQUEST['user_id']."'";
    $db->Execute($q) or die($db->ErrorMsg());

    set_msg("User deleted successfully");
    header("Location: $base_url&mode=users&user_mode=show_users");
    exit;

}

?>
