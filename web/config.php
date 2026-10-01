<?php
/* =========================================================
 *  좋은 부모 배움터 · 사용자 페이지  환경설정
 *  admin/config.php 와 동일한 DB 를 바라봅니다.
 * ========================================================= */
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

/* --- 데이터베이스 (admin/config.php 와 동일) --- */
define('DB_HOST',    'localhost');
define('DB_NAME',    'goodparents');
define('DB_USER',    'goodparents');
define('DB_PASS',    'Skan0420!@');
define('DB_CHARSET', 'utf8mb4');

/* --- 임시 로그인 (SNS 연동 전까지 사용) --- */
define('TEMP_LOGIN_ID', 'goodparents');
define('TEMP_LOGIN_PW', '123456');
define('TEMP_MEMBER_ID', 1);   /* 임시 로그인이 매핑되는 member.id */

/* --- install.php 실행 키 (설치 후 삭제 권장) --- */
define('SETUP_KEY', 'gp-web-setup-6741');

/* --- 서술형 AI 분석 임시 문구 --- */
define('ESSAY_AI_STUB', 'AI 분석 준비 중입니다');

/* --- 업로드된 상황 동영상 경로 (admin 과 공유) --- */
define('ADMIN_UPLOAD_URL', '../admin/uploads');

define('APP_TITLE', '좋은 부모 배움터');
define('APP_BRAND', '좋은부모교육 플랫폼');
date_default_timezone_set('Asia/Seoul');
