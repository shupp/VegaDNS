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





function authenticate_user($email, $password) {
    global $timeout, $db;

    // Garbage collection for sessions
    $oldsessions = time()-$timeout;
    $db->Execute("delete from active_sessions where time < $oldsessions")
        or die($db->ErrorMsg());
    $q = "select email from accounts where 
        email=".$db->Quote(strtolower($email))." and 
        password='".md5($password)."' and
        status='active' LIMIT 1";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    $row = $result->FetchRow();
    if($row['email'] != "") {
        // Kill any other sessions by this user
        $db->Execute("delete from active_sessions where email=".
            $db->Quote($email)) or die("error logging in");
        $db->Execute("insert into active_sessions (
            sid, 
            email, 
            time) 

            VALUES (
            '".session_id()."', 
            ".$db->Quote($email).", 
            '".time()."')");
        return 'TRUE';
    } else {
        return 'FALSE';
    }
}


if(!isset($_REQUEST['mode'])) {
    $auth = "FALSE";
    if(isset($_REQUEST['email']) && isset($_REQUEST['password'])) {
        $auth = authenticate_user($_REQUEST['email'], $_REQUEST['password']);
    } else {
        set_msg_err("Error: You must supply a username and password");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID);
        exit;
    }
    if($auth == "TRUE") {
        header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=logged_in");
        exit;
    } else {
        set_msg_err("Error signing on: incorrect email address or password<p><a href=".$_SERVER['PHP_SELF']."?".SID."&state=help>forgot your password?</a>");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID);
        exit;
    }
} else {
    // Make sure they are logged in
    $email = verify_session();
    if($email == 'TIMEOUT') {
        set_msg_err("Error: your session has expired.  Please log in again.");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID);
        exit;
    } else if($email == "") {
        set_msg_err("Error: you do not appear to be logged in");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID);
        exit;
    } else {
        header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=logged_in");
        exit;
    }
}

?>
