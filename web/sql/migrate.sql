-- =========================================================
--  좋은 부모 배움터 · 사용자 페이지 스키마  (참고용 — 실제 적용은 web/install.php)
--  기존 admin 스키마(InnoDB / utf8mb4)에 이어붙임
-- =========================================================
SET NAMES utf8mb4;

-- ---------- 관리자 스키마 확장 ----------
ALTER TABLE `situation`
  ADD COLUMN `expert_conti` text AFTER `solve_conti`,
  ADD COLUMN `expert_html` mediumtext AFTER `expert_conti`;

ALTER TABLE `situation_checklist_item`
  ADD COLUMN `answer_type` varchar(10) NOT NULL DEFAULT 'scale' AFTER `question`,
  ADD COLUMN `scale_steps` tinyint(3) unsigned NOT NULL DEFAULT 5 AFTER `answer_type`;

ALTER TABLE `parenting_checklist_item`
  ADD COLUMN `answer_type` varchar(10) NOT NULL DEFAULT 'scale' AFTER `question`,
  ADD COLUMN `scale_steps` tinyint(3) unsigned NOT NULL DEFAULT 5 AFTER `answer_type`;

-- 체크리스트 4지선다 보기 (상황별/부모양육태도 공용, scope 로 구분)
CREATE TABLE IF NOT EXISTS `checklist_item_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(10) NOT NULL DEFAULT 'situation',
  `item_id` int(10) unsigned NOT NULL,
  `label` varchar(300) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_opt_item` (`scope`,`item_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `checklist_option_score` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option_id` int(10) unsigned NOT NULL,
  `parenting_attitude_id` int(10) unsigned NOT NULL,
  `percentage` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_opt_score` (`option_id`,`parenting_attitude_id`),
  KEY `ix_opt_score_att` (`parenting_attitude_id`),
  CONSTRAINT `fk_opt_score_opt` FOREIGN KEY (`option_id`) REFERENCES `checklist_item_option` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_opt_score_att` FOREIGN KEY (`parenting_attitude_id`) REFERENCES `parenting_attitude` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 회원 ----------
CREATE TABLE IF NOT EXISTS `member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(20) NOT NULL DEFAULT 'temp',
  `provider_uid` varchar(191) DEFAULT NULL,
  `login_id` varchar(60) DEFAULT NULL,
  `nickname` varchar(60) DEFAULT NULL,
  `parent_gender` char(1) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `region_zip` varchar(10) DEFAULT NULL,
  `region_addr` varchar(255) DEFAULT NULL,
  `region_detail` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member_provider` (`provider`,`provider_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `member_child` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `life_cycle_id` int(10) unsigned DEFAULT NULL,
  `grade` tinyint(3) unsigned DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_child_member` (`member_id`,`sort_order`),
  KEY `ix_child_lc` (`life_cycle_id`),
  CONSTRAINT `fk_child_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_child_lc` FOREIGN KEY (`life_cycle_id`) REFERENCES `life_cycle` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 학습 진행 ----------
CREATE TABLE IF NOT EXISTS `learning_session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `situation_id` int(10) unsigned NOT NULL,
  `current_step` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `step1_done` tinyint(1) NOT NULL DEFAULT 0,
  `step2_done` tinyint(1) NOT NULL DEFAULT 0,
  `step3_done` tinyint(1) NOT NULL DEFAULT 0,
  `step4_done` tinyint(1) NOT NULL DEFAULT 0,
  `step5_done` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(12) NOT NULL DEFAULT 'in_progress',
  `started_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session` (`member_id`,`situation_id`),
  KEY `ix_session_sit` (`situation_id`),
  CONSTRAINT `fk_session_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_sit` FOREIGN KEY (`situation_id`) REFERENCES `situation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 체크리스트 응답 ----------
CREATE TABLE IF NOT EXISTS `checklist_submission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `scope` varchar(10) NOT NULL DEFAULT 'situation',
  `situation_id` int(10) unsigned DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_sub_member` (`member_id`,`scope`),
  KEY `ix_sub_sit` (`situation_id`),
  CONSTRAINT `fk_sub_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `checklist_response` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `scale_value` tinyint(3) unsigned DEFAULT NULL,
  `option_id` int(10) unsigned DEFAULT NULL,
  `essay_text` text,
  `ai_text` varchar(255) DEFAULT NULL,
  `ai_status` varchar(10) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_resp_sub` (`submission_id`),
  CONSTRAINT `fk_resp_sub` FOREIGN KEY (`submission_id`) REFERENCES `checklist_submission` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `checklist_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` int(10) unsigned NOT NULL,
  `parenting_attitude_id` int(10) unsigned NOT NULL,
  `score` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_result` (`submission_id`,`parenting_attitude_id`),
  KEY `ix_result_att` (`parenting_attitude_id`),
  CONSTRAINT `fk_result_sub` FOREIGN KEY (`submission_id`) REFERENCES `checklist_submission` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_result_att` FOREIGN KEY (`parenting_attitude_id`) REFERENCES `parenting_attitude` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `practice_note` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `situation_id` int(10) unsigned NOT NULL,
  `content` text,
  `ai_text` varchar(255) DEFAULT NULL,
  `ai_status` varchar(10) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_note` (`member_id`,`situation_id`),
  KEY `ix_note_sit` (`situation_id`),
  CONSTRAINT `fk_note_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_note_sit` FOREIGN KEY (`situation_id`) REFERENCES `situation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 참여 / 성과 ----------
CREATE TABLE IF NOT EXISTS `situation_like` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `situation_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_like` (`member_id`,`situation_id`),
  KEY `ix_like_sit` (`situation_id`),
  CONSTRAINT `fk_like_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_like_sit` FOREIGN KEY (`situation_id`) REFERENCES `situation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `badge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(80) NOT NULL,
  `icon` varchar(120) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_badge_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `member_badge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `badge_id` int(10) unsigned DEFAULT NULL,
  `situation_id` int(10) unsigned DEFAULT NULL,
  `earned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member_badge_sit` (`member_id`,`situation_id`),
  KEY `ix_mb_badge` (`badge_id`),
  CONSTRAINT `fk_mb_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 오프라인 모임 / 교육안내 ----------
CREATE TABLE IF NOT EXISTS `offline_meeting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kind` varchar(12) NOT NULL DEFAULT 'meeting',
  `life_cycle_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text,
  `meet_at` datetime DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `capacity` int(10) unsigned DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_meeting_list` (`kind`,`is_published`,`sort_order`),
  KEY `ix_meeting_lc` (`life_cycle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `offline_meeting_like` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `meeting_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meeting_like` (`member_id`,`meeting_id`),
  KEY `ix_ml_meeting` (`meeting_id`),
  CONSTRAINT `fk_ml_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ml_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `offline_meeting` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `meeting_situation_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meeting_id` int(10) unsigned NOT NULL,
  `situation_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meeting_sit` (`meeting_id`,`situation_id`),
  KEY `ix_msl_sit` (`situation_id`),
  CONSTRAINT `fk_msl_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `offline_meeting` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msl_sit` FOREIGN KEY (`situation_id`) REFERENCES `situation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 학습자료실 (메뉴만, 다음 차수 콘텐츠) ----------
CREATE TABLE IF NOT EXISTS `learning_resource` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(12) NOT NULL DEFAULT 'global',
  `situation_id` int(10) unsigned DEFAULT NULL,
  `life_cycle_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'material',
  `title` varchar(200) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_res_scope` (`scope`,`type`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 시드 ----------
INSERT INTO `member` (`id`,`provider`,`login_id`,`nickname`)
  VALUES (1,'temp','goodparents','홍길동')
  ON DUPLICATE KEY UPDATE `id` = `id`;

INSERT INTO `badge` (`code`,`name`,`description`)
  VALUES ('situation_complete','학습 완료','상황 학습 6단계를 모두 마치면 지급')
  ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
