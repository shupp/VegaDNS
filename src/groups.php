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


if(isset($_SESSION['group']) && $_SESSION['group'] != 'NULL') {
    $group = $_SESSION['group'];
} else {
    $group = $my->account['group_id'];
}

if(!isset($_REQUEST['group_mode']) || $_REQUEST['group_mode'] == 'delete_cancelled') {

    if(isset($_REQUEST['group_mode']) && $_REQUEST['group_mode'] == 'delete_cancelled') {
        // Set cancel message
        set_msg("Delete Cancelled");
    }

    $group_array = $my->returnGroup($group, NULL);
    $smarty->assign('group_array', $group_array);

    if($my->canCreateSubGroups()) {
        $smarty->assign('new_sub_url', $base_url."&mode=groups&group_mode=create_sub");
    }

    if($my->canEditSubGroups()) {
        $smarty->assign('edit_sub_url_base', $base_url."&mode=groups&group_mode=edit_sub");
    }

    $smarty->display('header.tpl');
    $smarty->display('show_group.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['group_mode'] == 'create_sub') {

    // Check permissions
    if(!$my->canCreateSubGroups()) {
        set_msg_err("Error: You cannot create sub-groups");
        header("Location: $base_url&mode=groups");
        exit;
    }

    // Setup default permissions
    if($my->account['account_type'] == 'senior_admin') {
        // Allow all permissions
        $smarty->assign('default_perms', $senior_perms);
    } else {
        $smarty->assign('default_perms', $my->account['permissions']);
    }

    $smarty->display('header.tpl');
    $smarty->display('create_sub_group.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['group_mode'] == 'create_sub_now') {

    // Check permissions
    if(!$my->canCreateSubGroups()) {
        set_msg_err("Error: You cannot create sub-groups");
        header("Location: $base_url&mode=groups");
        exit;
    }

    // Make sure group name is not empty
    if(!isset($_REQUEST['name']) || $_REQUEST['name'] == "") {
        set_msg_err("Error: no Group Name supplied");
        header("Location: $base_url&mode=groups");
        exit;
    }

    // Make sure group doesn't exist
    $q = "select * from groups where name=".$db->Quote($_REQUEST['name']);
    $result = $db->Execute($q) or die($db->ErrorMsg());
    if($result->RecordCount() > 0) {
        set_msg_err("Error: group name already exists");
        $smarty->display('header.tpl');
        $smarty->display('create_sub_group.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    if(isset($fail)) {
        $smarty->display('header.tpl');
        $smarty->display('create_sub_group.tpl');
        $smarty->display('footer.tpl');
        exit;
    } else {
        $q = "insert into groups (parent_group_id, name) values($group, ".$db->Quote($_REQUEST['name']).")";
        $result = $db->Execute($q) or die($db->ErrorMsg());

        // Now set up permissions
        $q = $my->returnCreateGroupPermQuery($_REQUEST['name']);
        $db->Execute($q) or die($db->ErrorMsg());

        set_msg("Group added successfully");
        header("Location: $base_url&mode=groups");
        exit;
    }

} else if($_REQUEST['group_mode'] == 'delete') {

    // Make sure the group_to_delete was given
    if(!isset($_REQUEST['group_to_delete']) || $_REQUEST['group_to_delete'] == "") {
        $fail = TRUE;
        set_msg_err("Error: no group was given for deletion");

    // Check permissions
    } else if(!$my->canDeleteGroup($_REQUEST['group_to_delete'])) {

        $fail = TRUE;
        set_msg_err("Error: you do not have enough privliges to delete this group");
    }

    if(isset($fail)) {
        header("Location: $base_url&mode=groups");
        exit;
    }

    $smarty->assign('delete_url', $base_url."&mode=groups&group_mode=delete_now&group_to_delete=".$_REQUEST['group_to_delete']);
    $smarty->assign('cancel_url', $base_url."&mode=groups&group_mode=delete_cancelled");

    $smarty->display('header.tpl');
    $smarty->display('confirm_group_delete.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['group_mode'] == 'delete_now') {

    // Make sure the group_to_delete was given
    if(!isset($_REQUEST['group_to_delete']) || $_REQUEST['group_to_delete'] == "") {
        $fail = TRUE;
        set_msg_err("Error: no group was given for deletion");
    // Check permissions
    } else if(!$my->canDeleteGroup($_REQUEST['group_to_delete'])) {
        $fail = TRUE;
        set_msg_err("Error: you do not have enough privliges to delete this group");
    }


    // Check that the group is empty
    if(!isset($fail)) {
        $q = "select * from accounts where group_id=".$db->Quote($_REQUEST['group_to_delete']);
        $result = $db->Execute($q) or die($db->ErrorMsg());
        if($result->RecordCount() > 0) {
            $fail = TRUE;
            set_msg_err("Error: This group still contains accounts");
        }
    }
    if(!isset($fail)) {
        $q = "select * from domains where group_id=".$db->Quote($_REQUEST['group_to_delete']);
        $result = $db->Execute($q) or die($db->ErrorMsg());
        if($result->RecordCount() > 0) {
            $fail = TRUE;
            set_msg_err("Error: This group still contains domains");
        }
    }
    if(!isset($fail)) {
        $q = "select * from groups where parent_group_id=".$db->Quote($_REQUEST['group_to_delete']);
        $result = $db->Execute($q) or die($db->ErrorMsg());
        if($result->RecordCount() > 0) {
            $fail = TRUE;
            set_msg_err("Error: This group still contains sub-groups");
        }
    }

    if(isset($fail)) {
            
        header("Location: $base_url&mode=groups");
        exit;

    } else {

        // Delete group

        $q = "delete from groups where group_id=".$db->Quote($_REQUEST['group_to_delete']);
        $result = $db->Execute($q) or die($db->ErrorMsg());
        $q = "delete from group_permissions where group_id=".$db->Quote($_REQUEST['group_to_delete']);
        $result = $db->Execute($q) or die($db->ErrorMsg());
        set_msg("Group deleted successfully");
        header("Location: $base_url&mode=groups");
        exit;

    }

} else if($_REQUEST['group_mode'] == 'edit_sub') {

    // Make sure the group_to_edit was given
    if(!isset($_REQUEST['group_to_edit']) || $_REQUEST['group_to_edit'] == "") {
        $fail = TRUE;
        set_msg_err("Error: no group was given for editing");

    // Check permissions
    } else if(!$my->canEditGroup($_REQUEST['group_to_edit'])) {

        $fail = TRUE;
        set_msg_err("Error: you do not have enough privliges to edit this group");
    }

    if(isset($fail)) {
        header("Location: $base_url&mode=groups");
        exit;
    }

    // Setup default permissions
    if($my->account['account_type'] == 'senior_admin') {
        // Allow all permissions
        $smarty->assign('default_perms', $senior_perms);
    } else {
        $smarty->assign('default_perms', $my->account['permissions']);
    }

    $group_array = $my->returnGroup($_REQUEST['group_to_edit'], NULL);

    // Display current settings in edit form
    $smarty->assign('group_to_edit', $_REQUEST['group_to_edit']);
    $smarty->assign('group_perms', $my->returnGroupPermissions($_REQUEST['group_to_edit']));
    $smarty->assign('group_name', $group_array['name']);

    $smarty->display('header.tpl');
    $smarty->display('edit_sub_group.tpl');
    $smarty->display('footer.tpl');
    exit;


} else if($_REQUEST['group_mode'] == 'edit_sub_now') {

    // Make sure the group_to_edit was given
    if(!isset($_REQUEST['group_to_edit']) || $_REQUEST['group_to_edit'] == "") {
        $fail = TRUE;
        set_msg_err("Error: no group was given for editing");

    // Check permissions
    } else if(!$my->canEditGroup($_REQUEST['group_to_edit'])) {

        $fail = TRUE;
        set_msg_err("Error: you do not have enough privliges to edit this group");
    }

    if(isset($fail)) {
        header("Location: $base_url&mode=groups");
        exit;
    }

    // Edit group permissions
    $q = $my->returnEditGroupPermQuery($_REQUEST['group_to_edit']);
    $db->Execute($q) or die($db->ErrorMsg()."q: $q");

    set_msg("Group edited successfully");
    header("Location: $base_url&mode=groups");
    exit;
    

} else {

    die("Error: Illegal group mode");

}
