DROP TABLE IF EXISTS `accounts`;
CREATE TABLE accounts (
  cid int(10) NOT NULL auto_increment,
  gid int(10) default '0',
  Email varchar(60) NOT NULL default '',
  Password varchar(34) NOT NULL default '',
  First_Name varchar(20) NOT NULL default '',
  Last_Name varchar(20) NOT NULL default '',
  Phone varchar(15) NOT NULL default '',
  Account_Type enum('user','group_admin','senior_admin') NOT NULL default 'user',
  Status enum('active','inactive') NOT NULL default 'inactive',
  UNIQUE KEY cid (cid,Email)
) TYPE=MyISAM;

INSERT INTO accounts VALUES (0,0,'test@test.com','098f6bcd4621d373cade4e832627b4f6','Test','User','','senior_admin','active');

DROP TABLE IF EXISTS `active_sessions`;
CREATE TABLE active_sessions (
  sid varchar(32) NOT NULL default '',
  Email varchar(60) NOT NULL default '',
  time int(14) NOT NULL default '0',
  PRIMARY KEY  (sid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `domains`;
CREATE TABLE domains (
  domain_id int(11) NOT NULL auto_increment,
  domain varchar(100) NOT NULL default '',
  owner_id int(11) default NULL,
  group_owner_id int(11) default NULL,
  status enum('active','inactive') NOT NULL default 'inactive',
  KEY domain_id (domain_id,domain)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  domain_id int(11) NOT NULL default '0',
  cid int(11) NOT NULL default '0',
  Email varchar(60) NOT NULL default '',
  Name varchar(60) NOT NULL default '',
  entry varchar(200) NOT NULL default '',
  time int(11) NOT NULL default '0'
) TYPE=MyISAM;

DROP TABLE IF EXISTS `records`;
CREATE TABLE `records` (
  domain_id int(11) NOT NULL default '0',
  record_id int(11) NOT NULL auto_increment,
  host varchar(100) NOT NULL default '',
  type char(1) default NULL,
  val varchar(100) default NULL,
  distance int(4) default '0',
  weight int(4) default NULL,
  port int(4) default NULL,
  ttl int(11) NOT NULL default '86400',
  UNIQUE KEY records_id (record_id),
  KEY records_idx (domain_id,record_id,host)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `default_records`;
CREATE TABLE `default_records` (
  record_id int(11) NOT NULL auto_increment,
  group_owner_id int(11) default NULL,
  host varchar(100) NOT NULL default '',
  type char(1) default NULL,
  val varchar(100) default NULL,
  distance int(4) default '0',
  weight int(4) default NULL,
  port int(4) default NULL,
  ttl int(11) NOT NULL default '86400',
  default_type enum('system','group') NOT NULL default 'system',
  UNIQUE KEY records_id (record_id)
) TYPE=MyISAM;

INSERT INTO default_records VALUES (1,0,'hostmaster.DOMAIN:ns1.myserver.com','S','16384:2048:1048576:2560',0,'','',86400,'system');
