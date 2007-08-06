DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  user_id       INT(11) NOT NULL AUTO_INCREMENT,
  group_id      INT(11) DEFAULT '1',
  email         CHAR(60) NOT NULL DEFAULT '',
  password      CHAR(34) NOT NULL DEFAULT '',
  first_name    CHAR(20) NOT NULL DEFAULT '',
  last_name     CHAR(20) NOT NULL DEFAULT '',
  phone         CHAR(15) NOT NULL DEFAULT '',
  account_type  ENUM('user', 'senior_admin') NOT NULL DEFAULT 'user',
  status        ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
  UNIQUE KEY user_id (user_id,email)
) TYPE=InnoDB;

INSERT INTO accounts VALUES (1,1,'anon@example.com', MD5('test'),'Anonymous','Coward','','user','inactive');
INSERT INTO accounts VALUES (2,1,'admin@example.com', MD5('test'),'Default','Administrator','','senior_admin','active');

DROP TABLE IF EXISTS `domains`;
CREATE TABLE domains (
  domain_id         INT(11) NOT NULL AUTO_INCREMENT,
  domain            CHAR(100) NOT NULL DEFAULT '',
  group_id    INT(11) DEFAULT NULL,
  description       VARCHAR(255) NOT NULL DEFAULT '',
  status            ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
  KEY               domain_id (domain_id,domain)
) TYPE=InnoDB;

DROP TABLE IF EXISTS `log`;
CREATE TABLE log (
  domain_id         INT(11) NOT NULL DEFAULT '0',
  user_id           INT(11) NOT NULL DEFAULT '0',
  group_id          INT(11) NOT NULL DEFAULT '0',
  email             CHAR(60) NOT NULL DEFAULT '',
  name              VARCHAR(60) NOT NULL DEFAULT '',
  entry             VARCHAR(200) NOT NULL DEFAULT '',
  time              INT(11) NOT NULL DEFAULT '0'
) TYPE=InnoDB;

DROP TABLE IF EXISTS `records`;
CREATE TABLE records (
  domain_id         INT(11) NOT NULL DEFAULT '0',
  record_id         INT(11) NOT NULL AUTO_INCREMENT,
  host              CHAR(100) NOT NULL DEFAULT '',
  type              CHAR(1) DEFAULT NULL,
  val               CHAR(200) DEFAULT NULL,
  distance          INT(4) DEFAULT '0',
  weight            INT(4) DEFAULT NULL,
  port              INT(4) DEFAULT NULL,
  ttl               INT(11) NOT NULL DEFAULT '86400',
  description       VARCHAR(255) NOT NULL DEFAULT '',
  UNIQUE KEY        records_id (record_id),
  KEY records_idx (domain_id,record_id,host)
) TYPE=InnoDB;

DROP TABLE IF EXISTS `default_records`;
CREATE TABLE default_records (
  record_id         INT(11) NOT NULL AUTO_INCREMENT,
  group_id          INT(11) DEFAULT NULL,
  host              CHAR(100) NOT NULL DEFAULT '',
  type              CHAR(1) DEFAULT NULL,
  val               CHAR(200) DEFAULT NULL,
  distance          INT(4) DEFAULT '0',
  weight            INT(4) DEFAULT NULL,
  port              INT(4) DEFAULT NULL,
  ttl               INT(11) NOT NULL DEFAULT '86400',
  description       VARCHAR(255) NOT NULL DEFAULT '',
  DEFAULT_type      ENUM('system','group') NOT NULL DEFAULT 'system',
  UNIQUE KEY        records_id (record_id)
) TYPE=InnoDB;

INSERT INTO default_records VALUES (0,0,'hostmaster.DOMAIN:ns1.example.com','S','16384:2048:1048576:2560',0,'','',86400,'','system');

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE user_permissions(
    perm_id                 INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    user_id                 INT(10) NOT NULL,
    perm_value              INT(9) DEFAULT '0',
    KEY perm_idx (user_id)

) TYPE=InnoDB;

DROP TABLE IF EXISTS `groups`;
CREATE TABLE groups (
    group_id                INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    parent_group_id         INT UNSIGNED NOT NULL DEFAULT '0',
    name                    CHAR(255) NOT NULL,
    KEY group_idx1 (parent_group_id),
    KEY group_idx2 (name)
) TYPE=InnoDB;

INSERT INTO groups(group_id, parent_group_id, name) VALUES (0, 0, 'VegaDNS');

DROP TABLE IF EXISTS `group_permissions`;
CREATE TABLE group_permissions(
    perm_id                 INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    group_id                INT(10) DEFAULT '1',
    perm_value              INT(9) DEFAULT '0',
    KEY group_perm_idx (group_id)
) TYPE=InnoDB;

INSERT INTO `group_permissions` VALUES (1,1,NULL);
