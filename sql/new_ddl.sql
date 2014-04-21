
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
ALTER TABLE `fantasy`.`merchandise_items`     ADD COLUMN `perk_id` INT(3) DEFAULT '0' NULL AFTER `merchandise_type`;

CREATE TABLE ffgame_stats.rank_update_history (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ffgame_stats.job_queue_rank (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `game_id` varchar(31) DEFAULT NULL,
  `since_id` bigint(21) DEFAULT '0',
  `until_id` bigint(21) DEFAULT '0',
  `worker_id` int(11) DEFAULT '0',
  `queue_dt` datetime DEFAULT NULL,
  `finished_dt` datetime DEFAULT NULL,
  `current_id` bigint(21) DEFAULT '0' COMMENT 'latest id been processed.',
  `n_done` int(11) DEFAULT '0',
  `n_status` tinyint(3) DEFAULT '0' COMMENT '0->pending, 1-> in process, 2->done',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_JOB_CLUSTER` (`game_id`,`since_id`,`until_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#index ini diperlukan untuk memproses rank_and_points.worker.js
ALTER TABLE `ffgame_stats`.`game_team_extra_points` ADD INDEX `IDX_GAME_TEAM_ID` (`game_team_id`);


CREATE TABLE ffgame.digital_perks (
  `id` BIGINT(21) NOT NULL AUTO_INCREMENT,
  `game_team_id` BIGINT(21) DEFAULT NULL,
  `master_perk_id` BIGINT(21) DEFAULT NULL,
  `redeem_dt` DATETIME DEFAULT NULL,
  `last_use_dt` DATETIME DEFAULT NULL,
  `available` INT(3) DEFAULT '1' COMMENT 'how many weeks these perk can be used.',
  `n_status` TINYINT(3) DEFAULT '1' COMMENT '0-> disabled, 1->enabled',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_GAME_TEAM_ID` (`game_team_id`,`master_perk_id`),
  KEY `IDX_AVAILABLE` (`game_team_id`,`available`,`n_status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;


CREATE TABLE ffgame.coupon_codes (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(21) DEFAULT NULL,
  `coupon_code` varchar(13) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `redeem_dt` datetime DEFAULT NULL,
  `paid_dt` datetime DEFAULT NULL,
  `game_team_id` bigint(21) DEFAULT NULL,
  `paid` tinyint(3) DEFAULT '0' COMMENT '0->unpaid, 1->paid',
  `n_status` tinyint(3) DEFAULT '0' COMMENT '0->unused, 1->used',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_CODE` (`coupon_code`),
  KEY `IDX_GAME_TEAM_ID` (`game_team_id`),
  KEY `IDX_STATUS` (`coupon_code`,`n_status`,`coupon_id`,`paid`),
  KEY `IDX_AVAILABLE` (`coupon_id`,`paid`,`n_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE ffgame.coupons (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `vendor_name` varchar(140) DEFAULT NULL,
  `service_name` varchar(140) DEFAULT NULL,
  `description` text,
  `coin_amount` int(11) DEFAULT '0',
  `ss_dollar` int(11) DEFAULT '0',
  `img` varchar(140) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `n_status` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_CREATOR` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ffgame_stats`.`job_queue` ADD INDEX `IDX_STATUS` (`n_status`);
ALTER TABLE `ffgame_stats`.`job_queue_rank` ADD INDEX `IDX_STATUS` (`n_status`);

ALTER TABLE `fantasy`.`merchandise_orders` ADD COLUMN `email` VARCHAR(140) NULL AFTER `last_name`;

ALTER TABLE `ffgame_stats`.`master_match_player_points` ADD INDEX `IDX_PLAYER_ID` (`player_id`);


CREATE TABLE `ffgame`.`digital_perks_group`(     `id` BIGINT(21) NOT NULL AUTO_INCREMENT ,     `master_perk_id` BIGINT(21) ,     `category` VARCHAR(32) ,     PRIMARY KEY (`id`)  );

ALTER TABLE `ffgame`.`digital_perks_group` ADD UNIQUE `UNIQUE_GROUP` (`master_perk_id`, `category`);


ALTER TABLE `fantasy`.`merchandise_orders` ADD COLUMN `data` TEXT NULL AFTER `notes`;
ALTER TABLE `fantasy`.`merchandise_orders` ADD COLUMN `payment_method` VARCHAR(12) DEFAULT 'coins' NULL AFTER `data`,     ADD COLUMN `total_sale` INT(11) DEFAULT '0' NULL AFTER `payment_method`;

ALTER TABLE `fantasy`.`merchandise_orders` ADD COLUMN `trace_code` VARCHAR(30) NULL AFTER `total_sale`;

ALTER TABLE `fantasy`.`merchandise_orders`     ADD COLUMN `fb_id` BIGINT(21) NULL AFTER `id`;

ALTER TABLE `fantasy`.`merchandise_orders` ADD INDEX `IDX_FB` (`fb_id`);

ALTER TABLE `fantasy`.`merchandise_orders`    ADD COLUMN `ongkir_id` INT(11) DEFAULT '0' NULL AFTER `trace_code`;

ALTER TABLE `fantasy`.`merchandise_orders` ADD INDEX `IDX_CITY_ID` (`ongkir_id`);
ALTER TABLE `fantasy`.`merchandise_orders`     ADD COLUMN `ongkir_value` INT(11) DEFAULT '0' NULL AFTER `ongkir_id`;

CREATE TABLE fantasy.ongkir (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(64) DEFAULT NULL,
  `kecamatan` varchar(64) DEFAULT NULL,
  `province` varchar(64) DEFAULT NULL,
  `cost` int(11) DEFAULT '10000',
  PRIMARY KEY (`id`),
  KEY `IDX_CITY` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `fantasy`.`notifications` ADD COLUMN `msg_id` VARCHAR(140) NULL AFTER `game_team_id`;
ALTER TABLE `fantasy`.`notifications` ADD UNIQUE `UNIQUE_MSG_ID` (`game_team_id`, `msg_id`);

ALTER TABLE `fantasy`.`merchandise_items`     ADD COLUMN `weight` FLOAT(4,2) DEFAULT '1.0' NULL AFTER `stock`;


CREATE TABLE ffgame.game_bet_winners (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `game_id` varchar(32) DEFAULT NULL,
  `game_team_id` bigint(21) DEFAULT NULL,
  `score` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_USER` (`game_id`,`game_team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ffgame.game_bets (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `game_id` varchar(32) DEFAULT NULL,
  `game_team_id` bigint(21) DEFAULT NULL,
  `bet_name` varchar(64) DEFAULT NULL,
  `home` int(3) DEFAULT '0',
  `away` int(3) DEFAULT '0',
  `coins` int(5) DEFAULT '0',
  `submit_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_BET` (`game_id`,`game_team_id`,`bet_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE fantasy.merchandise_item_perks (
  `id` bigint(21) NOT NULL AUTO_INCREMENT,
  `merchandise_item_id` bigint(21) DEFAULT NULL,
  `perk_id` bigint(21) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_ITEM_PERK` (`merchandise_item_id`,`perk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
