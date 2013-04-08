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


$smarty->assign('typearray', array('A','NS','MX','PTR','TXT','CNAME','SOA'));


if(isset($_REQUEST['query_mode'])) {

    $program = $dns_tools_dir.'/dnsq';

    if(isset($_REQUEST['name']))
        $smarty->assign('name', $_REQUEST['name']);
        $name = $_REQUEST['name'];
    if(isset($_REQUEST['type'])) {
        $smarty->assign('type_selected', $_REQUEST['type']);
        $type = $_REQUEST['type'];
    } else {
        $type = "";
    }
    if(isset($_REQUEST['recursive'])) {
        $program = $program.'r';
        $smarty->assign('recursive', $_REQUEST['recursive']);
        $host = "";
    }
    if(!isset($_REQUEST['recursive'])) {
        $host = $_REQUEST['host'];
    }
    if(isset($_REQUEST['host'])) {
        $smarty->assign('host', $_REQUEST['host']);
    }

    $command = $program.' '.
                escapeshellcmd($type).' '.
                escapeshellcmd($name).' '.
                escapeshellcmd($host).' 2>&1';
    $smarty->assign('command', $command);
    set_time_limit(0);
    $result = shell_exec($command);
    $smarty->assign('result', preg_replace("/\n/", '<br>', $result));

}

$smarty->display('header.tpl');
$smarty->display('dnsquery.tpl');
$smarty->display('footer.tpl');
exit;

?>
