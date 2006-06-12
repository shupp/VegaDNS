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






$q = "select * from log where group_id = '". $_SESSION['group'] . "' ";
if(isset($_REQUEST['domain_id'])) {
    $q .= " and domain_id=".$db->Quote($_REQUEST['domain_id'])." ";
}
$result = $db->Execute($q) or die($db->ErrorMsg());

if($result->RecordCount() == 0) {
    set_msg("No log entries found");
    $smarty->display('header.tpl');
    $smarty->display('footer.tpl');
    exit;
} else {
    // Build array
    $counter = 0;
    while(!$result->EOF && $row = $result->FetchRow()) {
        $log[$counter]['user_id'] = $row['user_id'];
        $log[$counter]['email'] = $row['email'];
        $log[$counter]['name'] = $row['name'];
        $log[$counter]['entry'] = $row['entry'];
        $log[$counter]['time'] = strftime("%m/%d/%Y %T", $row['time']);
        $counter++;
    }
    $smarty->assign('log', $log);

    $smarty->display('header.tpl');
    $smarty->display('view_log.tpl');
    $smarty->display('footer.tpl');
    exit;
}





?>
