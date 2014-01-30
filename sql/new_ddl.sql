
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