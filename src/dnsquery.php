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


$smarty->assign('typearray', array('A','NS','MX','PTR','TXT','CNAME','SOA'));

if(isset($_REQUEST['query_mode'])) {

    if (isset($_REQUEST['name']) && $_REQUEST['name'] != "" ) {
        $program = $dns_tools_dir.'/dnsq';
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

        // reverse lookup
        if (validate_ip($name)) {
            $program = $dns_tools_dir.'/dnsname';
            $command = $program .' ' . escapeshellcmd($name).' 2>&1';
        } else {
            // regular lookup
            $command = $program.' '.
                escapeshellcmd($type).' '.
                escapeshellcmd($name).' '.
                escapeshellcmd($host).' 2>&1';
        }
        $smarty->assign('command', $command);
        set_time_limit(0);
        $result = shell_exec($command);
        $smarty->assign('result', ereg_replace("\n", '<br>', $result));
    } else {
        $smarty->assign('command', "No command executed");
        $result = ereg_replace("\n", '<br>', "ERROR: No Name supplied\nYou must fill in the Name field");
        $smarty->assign('result', $result );
    }
}

$smarty->display('header.tpl');
$smarty->display('dnsquery.tpl');
$smarty->display('footer.tpl');
exit;

?>
