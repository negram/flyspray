CREATE TABLE `flyspray_reminders` (
  `reminder_id` mediumint(10) NOT NULL auto_increment,
  `task_id` mediumint(10) NOT NULL default '0',
  `to_user_id` mediumint(3) NOT NULL default '0',
  `from_user_id` mediumint(3) NOT NULL default '0',
  `start_time` varchar(12) NOT NULL default '0',
  `how_often` mediumint(12) NOT NULL default '0',
  `last_sent` varchar(12) NOT NULL default '0',
  `reminder_message` longtext NOT NULL,
  PRIMARY KEY  (`reminder_id`)
) TYPE=MyISAM COMMENT='Scheduled reminders about tasks' AUTO_INCREMENT=19 ;


ALTER TABLE `flyspray_tasks` ADD `is_closed` MEDIUMINT( 1 ) NOT NULL AFTER `opened_by` ;

UPDATE flyspray_tasks SET is_closed = '1' WHERE item_status = '8';

ALTER TABLE `flyspray_projects` ADD `inline_images` MEDIUMINT( 1 ) NOT NULL AFTER `show_logo` ;

ALTER TABLE `flyspray_tasks` ADD `closure_comment` LONGTEXT NOT NULL AFTER `closed_by` ;

ALTER TABLE flyspray_users ADD dateformat varchar(30) NOT NULL default '';
ALTER TABLE flyspray_users ADD dateformat_extended varchar(30) NOT NULL default '';

INSERT INTO flyspray_prefs VALUES (18, 'dateformat', '', 'Default date format for new users and guests used in the task list');
INSERT INTO flyspray_prefs VALUES (19, 'dateformat_extended', '', 'Default date format for new users and guests used in task details');

ALTER TABLE `flyspray_list_category` ADD `parent_id` MEDIUMINT( 1 ) NOT NULL ;

CREATE TABLE `flyspray_history` (
  `history_id` mediumint(10) NOT NULL auto_increment,
  `task_id` mediumint(10) NOT NULL default '0',
  `user_id` mediumint(3) NOT NULL default '0',
  `event_date` text NOT NULL default '',
  `event_type` mediumint(2) NOT NULL default '0',
  `field_changed` text NOT NULL default '',
  `old_value` text NOT NULL default '',
  `new_value` text NOT NULL default '',
  PRIMARY KEY  (`history_id`)
) TYPE=MyISAM;

ALTER TABLE `flyspray_projects` ADD `visible_columns` VARCHAR( 255 ) NOT NULL;
UPDATE flyspray_projects SET visible_columns = 'id category tasktype severity summary dateopened status progress';

ALTER TABLE `flyspray_list_version` ADD `version_tense` MEDIUMINT( 1 ) NOT NULL ;
UPDATE flyspray_list_version SET version_tense = '2';

ALTER TABLE `flyspray_tasks` ADD `task_priority` MEDIUMINT( 3 ) NOT NULL AFTER `task_severity` ;
UPDATE flyspray_tasks SET task_priority = '2';

UPDATE flyspray_tasks SET last_edited_time=date_opened WHERE last_edited_time=0 OR last_edited_time='' OR last_edited_time IS NULL;
