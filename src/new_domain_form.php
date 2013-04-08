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



if(isset($_REQUEST['domain'])) $smarty->assign('domain', $_REQUEST['domain']);
if(isset($_REQUEST['ttl'])) {
    $smarty->assign('ttl', $_REQUEST['ttl']);
} else {
    $smarty->assign('ttl', '86400');
}
if(isset($_REQUEST['refresh'])) {
    $smarty->assign('refresh', $_REQUEST['refresh']);
} else {
    $smarty->assign('refresh', '1364');
}
if(isset($_REQUEST['retry'])) {
    $smarty->assign('retry', $_REQUEST['retry']);
} else {
    $smarty->assign('retry', '2048');
}
if(isset($_REQUEST['expire'])) {
    $smarty->assign('expire', $_REQUEST['expire']);
} else {
    $smarty->assign('expire', '1048576');
}
if(isset($_REQUEST['minimum'])) {
    $smarty->assign('minimum', $_REQUEST['minimum']);
} else {
    $smarty->assign('minimum', '2560');
}
if(isset($_REQUEST['contactaddr'])) {
    $smarty->assign('contactaddr', $_REQUEST['contactaddr']);
} else {
    $smarty->assign('contactaddr', 'hostmaster.DOMAIN');
}

$smarty->display('new_domain_form.tpl');
