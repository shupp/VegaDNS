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





if(!isset($_REQUEST['mode'])) {

    $smarty->display('header.tpl');
    $smarty->display('help.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['mode'] == "send_pass") {

    // Make sure it's a valid address
    if(!check_email_format($_REQUEST['username'])) {
        set_msg_err("Error: \"".$_REQUEST['username']."\" does not appear to be a properly formatted email address");
        header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=help");
        exit;
    }

    // Make sure it's in the database
    require('src/connect.php');
        $result = $db->Execute("select user_id from accounts where email='".
            strtolower($_REQUEST['username'])."'") or die($db->ErrorMsg());

        if($result->RecordCount() == 0) {
            set_msg_err("Error: \"".$_REQUEST['username']."\" does not appear in our database");
            header("Location: ".$_SERVER['PHP_SELF']."?".SID."&state=help");
            exit;
        } else {
	    $fa = $result->FetchRow();
        // Send password
	    $newpass = substr(md5(rand(0,10000)."vegadns_".$_REQUEST['username'].rand(0,10000)),0,rand(5,8));
	    $db->Execute("update accounts set password='".md5($newpass)."' where user_id=".$fa[0]) or die($db->ErrorMsg);
            $body = "Here is your requested information:\n\n";
            $body .= "Your new password is: ".$newpass."\n\n";
            $body .= "If you have further questions, please contact $supportemailm\n";
            $body .= "\n\nThanks,\n\n";
            $body .= "The VegaDNS Team";

            mail(strtolower($_REQUEST['username']),"Requested information",$body, "Return-path: $supportemail\r\nFrom: \"$supportname\" <$supportemail>");
            set_msg("Your password has been mailed to you";
            header("Location: ".$_SERVER['PHP_SELF']."?".SID);

        }
        exit;
}
?>
