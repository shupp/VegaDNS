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




if(isset($_REQUEST['location']))
    $smarty->assign('location', $_REQUEST['location']);

if(isset($_REQUEST['prefix']))
    $smarty->assign('prefix', $_REQUEST['address']);




$smarty->display('add_location_form.tpl');
