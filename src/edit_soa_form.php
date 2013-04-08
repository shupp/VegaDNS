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





if(isset($_REQUEST['primary_name_server'])) {
    $smarty->assign('primary_name_server',  $_REQUEST['primary_name_server']);
} else {
    $smarty->assign('primary_name_server',  $soa['tldhost']);
}
if(isset($_REQUEST['contactaddr'])) {
    $smarty->assign('contactaddr',  $_REQUEST['contactaddr']);
} else {
    $smarty->assign('contactaddr',  $soa['tldemail']);
}

if(isset($_REQUEST['ttl'])) {
    $smarty->assign('ttl',  $_REQUEST['ttl']);
} else {
    $smarty->assign('ttl',  $soa['ttl']);
}

if(isset($_REQUEST['refresh'])) {
    $smarty->assign('refresh',  $_REQUEST['refresh']);
} else {
    $smarty->assign('refresh',  $soa['refresh']);
}

if(isset($_REQUEST['retry'])) {
    $smarty->assign('retry',  $_REQUEST['retry']);
} else {
    $smarty->assign('retry',  $soa['retry']);
}

if(isset($_REQUEST['expire'])) {
    $smarty->assign('expire',  $_REQUEST['expire']);
} else {
    $smarty->assign('expire',  $soa['expire']);
}

if(isset($_REQUEST['minimum'])) {
    $smarty->assign('minimum',  $_REQUEST['minimum']);
} else {
    $smarty->assign('minimum',  $soa['minimum']);
}

if(isset($_REQUEST['serial'])) {
    $smarty->assign('serial',  $_REQUEST['serial']);
} else {
    $smarty->assign('serial',  $soa['serial']);
}

if($_REQUEST['mode'] == 'default_records') {
    $smarty->display('edit_default_soa_form.tpl');
} else {
    $smarty->display('edit_soa_form.tpl');
}
