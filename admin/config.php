<?php
/* =========================================================
 *  좋은 부모 배움터 · 관리자   환경설정
 *  이 파일의 값만 바꾸면 접속/보안 설정이 반영됩니다.
 * ========================================================= */
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

/* --- 데이터베이스 (byus.net 계정 안내 기준) --- */
define('DB_HOST',    'localhost');
define('DB_NAME',    'goodparents');
define('DB_USER',    'goodparents');
define('DB_PASS',    'Skan0420!@');   /* 계정 비밀번호와 동일 */
define('DB_CHARSET', 'utf8mb4');

/* --- 관리자 로그인 비밀번호 (원하는 값으로 바꾸세요) --- */
define('ADMIN_PASSWORD', '123465');

/* --- install.php 1회 실행용 키 (설치 후 install.php 삭제 권장) --- */
define('SETUP_KEY', 'gp-setup-6741');

define('APP_TITLE', '좋은 부모 배움터');
date_default_timezone_set('Asia/Seoul');
