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




if(isset($_REQUEST['name'])) {
    $smarty->assign('name', $_REQUEST['name']);
} else {
    if(isset($_REQUEST['domain']))
        $smarty->assign('name', $_REQUEST['domain']);
}

if($_REQUEST['mode'] == 'records') {
    if ($use_ipv6) {
        $smarty->assign('typearray', array('A','AAAA','AAAA+PTR','NS','MX','PTR','TXT','CNAME','SRV','SPF'));
    } else {
        $smarty->assign('typearray', array('A','NS','MX','PTR','TXT','CNAME','SRV','SPF'));
    }
} else if ($_REQUEST['mode'] == 'default_records') {
    if ($use_ipv6) {
        $smarty->assign('typearray', array('A','AAAA','NS','MX','TXT','CNAME','SRV','SPF'));
    } else {
        $smarty->assign('typearray', array('A','NS','MX','TXT','CNAME','SRV','SPF'));
    }
}
if(isset($_REQUEST['type']))
    $smarty->assign('type_selected', $_REQUEST['type']);
if(isset($_REQUEST['address']))
    $smarty->assign('address', $_REQUEST['address']);

if(isset($_REQUEST['distance'])) {
    $smarty->assign('distance', $_REQUEST['distance']);
} else {
    $smarty->assign('distance', 0);
}

if(isset($_REQUEST['weight'])) {
    $smarty->assign('weight', $_REQUEST['weight']);
} else {
    $smarty->assign('weight', '');
}

if(isset($_REQUEST['port'])) {
    $smarty->assign('port', $_REQUEST['port']);
} else {
    $smarty->assign('port', '');
}



if(isset($_REQUEST['ttl'])) {
    $smarty->assign('ttl', $_REQUEST['ttl']);
} else {
    $smarty->assign('ttl', 3600);
}

$smarty->display('add_record_form.tpl');
