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






$q = "CREATE TABLE accounts (
  user_id           INT(10) NOT NULL AUTO_INCREMENT,
  group_id           INT(10) DEFAULT '1',
  email         VARCHAR(60) NOT NULL DEFAULT '',
  password      VARCHAR(34) NOT NULL DEFAULT '',
  first_name    VARCHAR(20) NOT NULL DEFAULT '',
  last_name     VARCHAR(20) NOT NULL DEFAULT '',
  phone         VARCHAR(15) NOT NULL DEFAULT '',
  account_type  ENUM('user', 'senior_admin') NOT NULL DEFAULT 'user',
  status        ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
  UNIQUE KEY user_id (user_id,email)
) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());

$q = "INSERT INTO accounts VALUES (1,1,'test@test.com','".md5('test')."','Test','User','','senior_admin','active')";
$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);

$q = "CREATE TABLE active_sessions (
  sid               VARCHAR(32) NOT NULL DEFAULT '',
  email             VARCHAR(60) NOT NULL DEFAULT '',
  time              INT(14) NOT NULL DEFAULT '0',
  PRIMARY KEY       (sid)
) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());

$q = "INSERT INTO active_sessions VALUES ('".session_id()."','test@test.com',".time().")";
$db->Execute($q) or die($db->ErrorMsg());

$q = "CREATE TABLE domains (
  domain_id         INT(11) NOT NULL AUTO_INCREMENT,
  domain            VARCHAR(100) NOT NULL DEFAULT '',
  group_id    INT(11) DEFAULT NULL,
  description       VARCHAR(255) NOT NULL DEFAULT '',
  status            ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
  KEY               domain_id (domain_id,domain)
) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());

$q = "CREATE TABLE log (
  domain_id         INT(11) NOT NULL DEFAULT '0',
  user_id           INT(11) NOT NULL DEFAULT '0',
  group_id          INT(11) NOT NULL DEFAULT '0',
  email             VARCHAR(60) NOT NULL DEFAULT '',
  name              VARCHAR(60) NOT NULL DEFAULT '',
  entry             VARCHAR(200) NOT NULL DEFAULT '',
  time              INT(11) NOT NULL DEFAULT '0'
) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());

$q = "CREATE TABLE records (
  domain_id         INT(11) NOT NULL DEFAULT '0',
  record_id         INT(11) NOT NULL AUTO_INCREMENT,
  host              VARCHAR(100) NOT NULL DEFAULT '',
  type              CHAR(1) DEFAULT NULL,
  val               VARCHAR(100) DEFAULT NULL,
  distance          INT(4) DEFAULT '0',
  weight            INT(4) DEFAULT NULL,
  port              INT(4) DEFAULT NULL,
  ttl               INT(11) NOT NULL DEFAULT '86400',
  description       VARCHAR(255) NOT NULL DEFAULT '',
  UNIQUE KEY        records_id (record_id),
  KEY records_idx (domain_id,record_id,host)
) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());

$q = "CREATE TABLE default_records (
  record_id         INT(11) NOT NULL AUTO_INCREMENT,
  group_id    INT(11) DEFAULT NULL,
  host              VARCHAR(100) NOT NULL DEFAULT '',
  type              CHAR(1) DEFAULT NULL,
  val               VARCHAR(100) DEFAULT NULL,
  distance          INT(4) DEFAULT '0',
  weight            INT(4) DEFAULT NULL,
  port              INT(4) DEFAULT NULL,
  ttl               INT(11) NOT NULL DEFAULT '86400',
  description       VARCHAR(255) NOT NULL DEFAULT '',
  DEFAULT_type      ENUM('system','group') NOT NULL DEFAULT 'system',
  UNIQUE KEY        records_id (record_id)
) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());
$q = "INSERT INTO default_records VALUES (1,1,'hostmaster.DOMAIN:ns1.myserver.com','S','16384:2048:1048576:2560',0,'','',86400,'','system')";
$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);

$q = "CREATE TABLE user_permissions(
    perm_id                 INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    user_id                     INT(10) NOT NULL,
    inherit_group_perms     INT UNSIGNED DEFAULT NULL,

    accouedit               TINYINT UNSIGNED NOT NULL DEFAULT 0,
    accoucreate             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    accoudelete             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    accoupermsedit          TINYINT UNSIGNED NOT NULL DEFAULT 0,

    self_edit               TINYINT UNSIGNED NOT NULL DEFAULT 0,

    group_edit              TINYINT UNSIGNED NOT NULL DEFAULT 0,
    group_create            TINYINT UNSIGNED NOT NULL DEFAULT 0,
    group_delete            TINYINT UNSIGNED NOT NULL DEFAULT 0,

    domain_edit             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    domain_create           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    domain_delegate         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    domain_delete           TINYINT UNSIGNED NOT NULL DEFAULT 0,

    record_edit             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    record_create           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    record_delete           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    record_delegate         TINYINT UNSIGNED NOT NULL DEFAULT 0,

    default_record_edit     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    default_record_create   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    default_record_delete   TINYINT UNSIGNED NOT NULL DEFAULT 0,

    rrtype_allow_n          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_a          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_3          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_6          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_m          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_p          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_t          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_v          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_all        TINYINT UNSIGNED NOT NULL DEFAULT 0,

    KEY perm_idx (user_id)

) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());


$q = "CREATE TABLE groups (
    group_id INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    parent_group_id        INT UNSIGNED NOT NULL DEFAULT '1',
    name                VARCHAR(255) NOT NULL,
    KEY group_idx1 (parent_group_id),
    KEY group_idx2 (name)
) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());


// $q = "CREATE TABLE subgroups(
    // group_id            INT UNSIGNED NOT NULL,
    // subgroup_id         INT UNSIGNED NOT NULL,
    // rank                INT UNSIGNED NOT NULL,
    // KEY                 group_subgroups_idx1 (group_id),
    // KEY                 group_subgroups_idx2 (subgroup_id)
// ) TYPE=MyISAM";
// $db->Execute($q) or die($db->ErrorMsg());

$q = "INSERT INTO groups(group_id, parent_group_id, name) VALUES (1, 1, 'VegaDNS')";
$db->Execute($q) or die($db->ErrorMsg());

$q = "CREATE TABLE group_permissions(
    perm_id                 INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    group_id                 INT(10) DEFAULT '1',
    inherit_group_perms     INT UNSIGNED DEFAULT NULL,

    accouedit               TINYINT UNSIGNED NOT NULL DEFAULT 0,
    accoucreate             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    accoudelete             TINYINT UNSIGNED NOT NULL DEFAULT 0,

    self_edit               TINYINT UNSIGNED NOT NULL DEFAULT 0,

    group_edit              TINYINT UNSIGNED NOT NULL DEFAULT 0,
    group_create            TINYINT UNSIGNED NOT NULL DEFAULT 0,
    group_delete            TINYINT UNSIGNED NOT NULL DEFAULT 0,

    domain_edit             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    domain_create           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    domain_delegate         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    domain_delete           TINYINT UNSIGNED NOT NULL DEFAULT 0,

    record_edit             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    record_create           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    record_delete           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    record_delegate         TINYINT UNSIGNED NOT NULL DEFAULT 0,

    default_record_edit     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    default_record_create   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    default_record_delete   TINYINT UNSIGNED NOT NULL DEFAULT 0,

    rrtype_allow_n          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_a          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_3          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_6          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_m          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_p          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_t          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_v          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rrtype_allow_all        TINYINT UNSIGNED NOT NULL DEFAULT 0,

    KEY group_perm_idx (group_id)

) TYPE=MyISAM";
$db->Execute($q) or die($db->ErrorMsg());

$q = "INSERT INTO `group_permissions` VALUES (1,1,NULL,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);";
$db->Execute($q) or die($db->ErrorMsg());


// CREATE TABLE group_log(
    // group_log_id     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    // group_id         INT UNSIGNED NOT NULL,
    // user_id          INT UNSIGNED NOT NULL,
    // action              ENUM('added','modified','deleted','moved') NOT NULL,
    // timestamp           INT UNSIGNED NOT NULL,
    // modified_group_id   INT UNSIGNED NOT NULL,
    // paregroup_id     INT UNSIGNED,
    // name                VARCHAR(255)
// );

// CREATE INDEX group_log_idx1 on group_log(group_id); 
// CREATE INDEX group_log_idx2 on group_log(timestamp);
// INSERT INTO group_log(group_id, user_id, action, timestamp, modified_group_id, paregroup_id) VALUES (1, 1, 'added', UNIX_TIMESTAMP(), 1, 0);

?>
