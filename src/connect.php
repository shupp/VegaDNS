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

$pdo = VDB::singleton();

mysql_connect("$mysql_host", "$mysql_user", "$mysql_pass")
    or die("error connecting to database");
mysql_select_db("$mysql_db")
    or die("error selecting database");

?>
