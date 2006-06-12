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
 * whoisquery is an addon for vegadns
 * adapted from dnsquery.php
 */

if(!ereg(".*/index.php$", $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

if(isset($_REQUEST['query_mode'])) {

    if ( isset($_REQUEST['name']) && $_REQUEST['name'] != "" ) {
        # is it an ip?
        if ( preg_match("/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/", $_REQUEST['name'])) {
            $smarty->assign('name', "");
            $name = "";
        }
        else {
            $smarty->assign('name', $_REQUEST['name']);
            $name = $_REQUEST['name'];
        }

        if ($name) {
            $command = $whois_program .' ' . escapeshellcmd($name).' 2>&1';
            $smarty->assign('command', $command);
            set_time_limit(0);
            $result = shell_exec($command);
            $smarty->assign('result', ereg_replace("\n", '<br>', $result));
        }
    }
    else {
        $smarty->assign('command', "No command executed");
        $smarty->assign('result', ereg_replace("\n", '<br>', "No Name supplied\nYou must fill in the Name field"));
    }
}

$smarty->display('header.tpl');
$smarty->display('whoisquery.tpl');
$smarty->display('footer.tpl');
exit;

?>
