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




if(isset($_REQUEST['name'])) {
    $smarty->assign('name', $_REQUEST['name']);
} else {
    if(isset($_REQUEST['domain']))
        $smarty->assign('name', $_REQUEST['domain']);
}

if($_REQUEST['mode'] == 'records') {
    $smarty->assign('typearray', array('A','NS','MX','PTR','TXT','CNAME'));
} else if ($_REQUEST['mode'] == 'default_records') {
    $smarty->assign('typearray', array('A','NS','MX','TXT','CNAME'));
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

if(isset($_REQUEST['ttl'])) {
    $smarty->assign('ttl', $_REQUEST['ttl']);
} else {
    $smarty->assign('ttl', 3600);
}

$smarty->display('add_record_form.tpl');
