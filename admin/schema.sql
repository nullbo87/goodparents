-- =========================================================
--  좋은 부모 배움터 · 관리자   스키마 (참고용)
--  실제 설치는 브라우저에서 install.php 실행을 권장합니다.
--  DB: goodparents / charset: utf8mb4
-- =========================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS life_cycle (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(40) NOT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  description VARCHAR(200) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_life_cycle_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS parenting_attitude (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(40) NOT NULL,
  description TEXT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_parenting_attitude_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS situation_category (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  life_cycle_id INT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  description TEXT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_category (life_cycle_id, name),
  KEY ix_category_lc (life_cycle_id, sort_order),
  CONSTRAINT fk_category_lc FOREIGN KEY (life_cycle_id) REFERENCES life_cycle (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS situation (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  situation_category_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_situation_cat (situation_category_id, sort_order),
  CONSTRAINT fk_situation_cat FOREIGN KEY (situation_category_id) REFERENCES situation_category (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS situation_media (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  situation_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NULL,
  url VARCHAR(500) NOT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_media_situation (situation_id, sort_order),
  CONSTRAINT fk_media_situation FOREIGN KEY (situation_id) REFERENCES situation (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- 상황별 체크리스트 (항상 하나의 상황에 소속) ----
CREATE TABLE IF NOT EXISTS situation_checklist_item (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  situation_id INT UNSIGNED NOT NULL,
  question TEXT NOT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_scitem_situation (situation_id, sort_order),
  CONSTRAINT fk_scitem_situation FOREIGN KEY (situation_id) REFERENCES situation (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS situation_checklist_score (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  situation_checklist_item_id INT UNSIGNED NOT NULL,
  parenting_attitude_id INT UNSIGNED NOT NULL,
  percentage SMALLINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sc_score (situation_checklist_item_id, parenting_attitude_id),
  KEY ix_sc_score_att (parenting_attitude_id),
  CONSTRAINT fk_sc_score_item FOREIGN KEY (situation_checklist_item_id) REFERENCES situation_checklist_item (id) ON DELETE CASCADE,
  CONSTRAINT fk_sc_score_att FOREIGN KEY (parenting_attitude_id) REFERENCES parenting_attitude (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- 부모양육태도 체크리스트 (상황과 무관, 전역) ----
CREATE TABLE IF NOT EXISTS parenting_checklist_item (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  question TEXT NOT NULL,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS parenting_checklist_score (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  parenting_checklist_item_id INT UNSIGNED NOT NULL,
  parenting_attitude_id INT UNSIGNED NOT NULL,
  percentage SMALLINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pc_score (parenting_checklist_item_id, parenting_attitude_id),
  KEY ix_pc_score_att (parenting_attitude_id),
  CONSTRAINT fk_pc_score_item FOREIGN KEY (parenting_checklist_item_id) REFERENCES parenting_checklist_item (id) ON DELETE CASCADE,
  CONSTRAINT fk_pc_score_att FOREIGN KEY (parenting_attitude_id) REFERENCES parenting_attitude (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- 기준 데이터 ----
INSERT INTO life_cycle (name, sort_order, is_active) VALUES
  ('영유아기', 1, 1), ('초등입학', 2, 1), ('초등과정', 3, 1), ('중등과정', 4, 1), ('고등과정', 5, 1);

INSERT INTO parenting_attitude (name, sort_order, is_active) VALUES
  ('성장지원형', 1, 1), ('통제주도형', 2, 1), ('허용대신형', 3, 1), ('거리두기형', 4, 1);
