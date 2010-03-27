ALTER TABLE `records` ADD weight INT(4) AFTER distance;
ALTER TABLE `records` ADD port INT(4) AFTER weight;

ALTER TABLE `default_records` ADD weight INT(4) AFTER distance;
ALTER TABLE `default_records` ADD port INT(4) AFTER weight;
