-- =========================================================
--  좋은 부모 배움터 · 학습 플로우 6단계 확장
--  (상황동영상 / 아이의 마음 읽기 / 문제 해결 길라잡이 /
--   나의 마음 알아보기[신규] / 나의 다짐 / Action Card[신규])
--  실제 적용은 admin/migrate_run.php (관리자 로그인 후 1회 실행)
-- =========================================================
SET NAMES utf8mb4;

ALTER TABLE `learning_session`
  ADD COLUMN `step6_done` tinyint(1) NOT NULL DEFAULT 0 AFTER `step5_done`;

-- ---------- 4. 나의 마음 알아보기 (situation_checklist_item 과 동일 구조, 대상만 부모 자신) ----------
CREATE TABLE IF NOT EXISTS `situation_selfcheck_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `situation_id` int(10) unsigned NOT NULL,
  `question` text NOT NULL,
  `answer_type` varchar(10) NOT NULL DEFAULT 'scale',
  `scale_steps` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `scale_template_id` int(10) unsigned DEFAULT NULL,
  `display_style` varchar(10) NOT NULL DEFAULT 'list',
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_selfcheck_sit` (`situation_id`,`sort_order`),
  CONSTRAINT `fk_selfcheck_sit` FOREIGN KEY (`situation_id`) REFERENCES `situation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `situation_selfcheck_score` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `situation_selfcheck_item_id` int(10) unsigned NOT NULL,
  `parenting_attitude_id` int(10) unsigned NOT NULL,
  `percentage` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_selfcheck_score` (`situation_selfcheck_item_id`,`parenting_attitude_id`),
  KEY `ix_selfcheck_score_att` (`parenting_attitude_id`),
  CONSTRAINT `fk_selfcheck_score_item` FOREIGN KEY (`situation_selfcheck_item_id`) REFERENCES `situation_selfcheck_item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_selfcheck_score_att` FOREIGN KEY (`parenting_attitude_id`) REFERENCES `parenting_attitude` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- checklist_item_option / checklist_option_score 는 scope='self' 값으로 그대로 재사용 (컬럼 변경 없음)

-- ---------- 6. Action Card ----------
CREATE TABLE IF NOT EXISTS `situation_action_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `situation_id` int(10) unsigned NOT NULL,
  `card_type` varchar(10) NOT NULL DEFAULT 'text',
  `title` varchar(200) NOT NULL,
  `content` text,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_actioncard_sit` (`situation_id`,`sort_order`),
  CONSTRAINT `fk_actioncard_sit` FOREIGN KEY (`situation_id`) REFERENCES `situation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `situation_action_card_select` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `situation_id` int(10) unsigned NOT NULL,
  `card_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_card_select` (`member_id`,`card_id`),
  KEY `ix_select_sit` (`situation_id`),
  KEY `ix_select_card` (`card_id`),
  CONSTRAINT `fk_select_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_select_sit` FOREIGN KEY (`situation_id`) REFERENCES `situation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_select_card` FOREIGN KEY (`card_id`) REFERENCES `situation_action_card` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
