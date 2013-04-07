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
 *
 *
 *
 * NOTE:
 *          This functionality ONLY exists outside of the main application
 *          because tcplient kept dying fatally due to file descriptor 7
 *          being unavailable, which only occurs AFTER session_start() is
 *          called.
 *
 */


require_once 'src/config.php';


// CHECKS

// Make sure the hostname was given
if(!isset($_REQUEST['hostname']) || $_REQUEST['hostname'] == "") {
    echo "ERROR: no hostname given\n";
    exit;
}

// Make sure that some domains were given
if(!isset($_REQUEST['domain']) || $_REQUEST['domain'] == "") {
    echo "ERROR: no domain was supplied\n";
    exit;
}

$domain = $_REQUEST['domain'];
$hostname = $_REQUEST['hostname'];
$rand = rand();
$file = "/tmp/$domain.$rand";

$command = "$dns_tools_dir/tcpclient -R '".escapeshellcmd($hostname)."' 53 $dns_tools_dir/axfr-get '".escapeshellcmd($domain)."' $file $file.tmp 2>&1";
exec($command, $out);

// Print any errors first
if(strlen($out[0]) > 0) echo $out[0];

if(file_exists($file)) {
    $string = '';
    $newfile = file($file);
    while(list($key,$val) = each($newfile)) {
        $string .= $val;
    }
    unlink($file) or die("unable to unlink $file");
}

// rm the tmp file if it exists
if(file_exists("$file.tmp")) {
    unlink("$file.tmp") or die("unable to unlink $file.tmp");
}

// output data
echo $string;

exit;

?>
