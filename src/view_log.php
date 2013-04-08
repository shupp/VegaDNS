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


if(!isset($dom_row['domain_id'])) {
    echo "<span class=\"error\">Error: domain_id is not set</span>\n";
} else {

    $q = "select * from log where domain_id='".$dom_row['domain_id']."'";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));

    if($stmt->rowCount() == 0) {
        echo "<span class=\"error\">Error: no log entries found</span>\n";
    } else {
        // Build array
        $counter = 0;
        while($row = $stmt->fetch()) {
            $logs[$counter]['cid'] = $row['cid'];
            $logs[$counter]['email'] = $row['Email'];
            $logs[$counter]['name'] = $row['Name'];
            $logs[$counter]['entry'] = $row['entry'];
            $logs[$counter]['time'] = strftime("%m/%d/%Y %T", $row['time']);
            $counter++;
        }
        $smarty->assign('logs', $logs);
        $smarty->display('view_log.tpl');
    }

}




?>
