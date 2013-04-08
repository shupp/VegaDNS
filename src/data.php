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






// build data
$q = "select a.domain, b.host, b.type, b.val, b.distance, b.weight, b.port, b.ttl  from domains a left join records  b on a.domain_id = b.domain_id where a.status='active' order by a.domain, b.type, b.host, b.val";
$stmt = $pdo->query($q) or die(print_r($stmt->errorInfo()));
$out = "";

$lastdomain = "";
while($row = $stmt->fetch()) {
    // Set comment if it's the beginning of a new domain
    if($lastdomain != $row['domain']) $out .= "\n#".$row['domain']."\n";

    // Get records
    $out .= build_data_line($row,$row['domain']);
    $lastdomain = $row['domain'];
}

print($out);
exit;

?>
