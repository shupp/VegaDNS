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






// build data
$q = "select a.domain, b.host, b.type, b.val, b.distance, b.weight, b.port, b.ttl  from domains a left join records  b on a.domain_id = b.domain_id where a.status='active' order by a.domain, b.type, b.host, b.val";
$result = mysql_query($q) or die(mysql_error());
$out = "";

$lastdomain = "";
while($row = mysql_fetch_array($result)) {
    // Set comment if it's the beginning of a new domain
    if($lastdomain != $row['domain']) $out .= "\n#".$row['domain']."\n";

    // Get records
    $out .= build_data_line($row,$row['domain']);
    $lastdomain = $row['domain'];
}

print($out);
exit;

?>
