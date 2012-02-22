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
 * Copyright 2003-2012, Bill Shupp
 * see COPYING for details
 * 
 */ 

if(!ereg(".*/index.php$", $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

if(isset($_REQUEST['hostname'])) 
    $smarty->assign('hostname', $_REQUEST['hostname']);
if(isset($_REQUEST['domains']))
    $smarty->assign('domains', $_REQUEST['domains']);

$smarty->display('import_form.tpl');
