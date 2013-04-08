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





if(isset($_REQUEST['tldhost'])) {
    $soa_array['tldhost'] =  $_REQUEST['tldhost'];
} else {
    $soa_array['tldhost'] =  $soa['tldhost'];
}
if(isset($_REQUEST['tldemail'])) {
    $soa_array['tldemail'] =  $_REQUEST['tldemail'];
} else {
    $soa_array['tldemail'] =  $soa['tldemail'];
}

if(isset($_REQUEST['ttl'])) {
    $soa_array['ttl'] =  $_REQUEST['ttl'];
} else {
    $soa_array['ttl'] =  $soa['ttl'];
}

if(isset($_REQUEST['refresh'])) {
    $soa_array['refresh'] =  $_REQUEST['refresh'];
} else {
    $soa_array['refresh'] =  $soa['refresh'];
}

if(isset($_REQUEST['retry'])) {
    $soa_array['retry'] =  $_REQUEST['retry'];
} else {
    $soa_array['retry'] =  $soa['retry'];
}

if(isset($_REQUEST['expire'])) {
    $soa_array['expire'] =  $_REQUEST['expire'];
} else {
    $soa_array['expire'] =  $soa['expire'];
}

if(isset($_REQUEST['minimum'])) {
    $soa_array['minimum'] =  $_REQUEST['minimum'];
} else {
    $soa_array['minimum'] =  $soa['minimum'];
}

$smarty->assign('soa_array', $soa_array);
$smarty->display('edit_default_soa_form.tpl');
