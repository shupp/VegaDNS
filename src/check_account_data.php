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
if($account_info['Email'] != strtolower($_REQUEST['email_address'])) {
    $params = array(':email' => strtolower($_REQUEST['email_address']));
    $q = 'select Email from accounts where Email=:email';
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    $email_rows = $stmt->rowCount();
    if($email_rows > 0) {
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

// group owner change stuff
if($user_info['Account_Type'] == 'senior_admin' && strlen($_REQUEST['group_email_address']) > 0) {
    $new_gid = get_cid($_REQUEST['group_email_address']);
}


?>
