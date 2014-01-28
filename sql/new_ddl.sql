
CREATE TABLE ffgame.newsletter (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `subject` varchar(140) DEFAULT NULL,
  `content` text,
  `created_dt` datetime DEFAULT NULL,
  `last_send` datetime DEFAULT NULL,
  `n_status` tinyint(3) DEFAULT '0' COMMENT '0->pending, 1->sent, 2->resent',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `fantasy`.`teams` ADD INDEX `IDX_USER_ID` (`user_id`);

ALTER TABLE `fantasy`.`merchandise_items`     ADD COLUMN `merchandise_type` INT(3) DEFAULT '0' NULL COMMENT '0-> non-digital, 1-> digital' AFTER `stock`;

