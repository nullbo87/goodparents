<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

/* =========================================================
 *  임시 인증 (SNS 연동 전)
 *  - 로그인 성공 시 $_SESSION['member_id'] = TEMP_MEMBER_ID
 *  - member 행이 없으면 즉석 생성
 * ========================================================= */

function is_logged_in() { return !empty($_SESSION['member_id']); }

function current_member_id() { return is_logged_in() ? (int) $_SESSION['member_id'] : 0; }

function current_member() {
    static $m = null;
    if ($m === null && is_logged_in()) {
        $m = one('SELECT * FROM member WHERE id = ?', array(current_member_id()));
    }
    return $m ?: null;
}

function ensure_temp_member() {
    $m = one('SELECT id FROM member WHERE id = ?', array(TEMP_MEMBER_ID));
    if (!$m) {
        q('INSERT INTO member (id, provider, login_id, nickname, created_at, updated_at)
           VALUES (?, ?, ?, ?, NOW(), NOW())',
          array(TEMP_MEMBER_ID, 'temp', TEMP_LOGIN_ID, '홍길동'));
    }
    return TEMP_MEMBER_ID;
}

function attempt_login($id, $pw) {
    if (hash_equals(TEMP_LOGIN_ID, (string) $id) && hash_equals(TEMP_LOGIN_PW, (string) $pw)) {
        $_SESSION['member_id'] = ensure_temp_member();
        if (function_exists('session_regenerate_id')) { @session_regenerate_id(true); }
        return true;
    }
    return false;
}

function do_logout() {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* 로그인 필수 페이지 가드 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['after_login'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'index.php';
        redirect('index.php?p=login');
    }
}

/* 부모양육태도 진단 완료 여부 (학습하기 잠금 조건) */
function has_parenting_result($member_id = null) {
    $member_id = $member_id ? $member_id : current_member_id();
    if (!$member_id) { return false; }
    return (int) col("SELECT COUNT(*) FROM checklist_submission
                      WHERE member_id = ? AND scope = 'parenting'", array($member_id)) > 0;
}

/* 학습하기 계열 페이지 가드: 로그인 + 부모양육태도 진단 완료 */
function require_learning_ready() {
    require_login();
    if (!has_parenting_result()) {
        flash_set('먼저 부모양육태도 체크리스트를 완료해 주세요.', 'err');
        redirect('index.php?p=parenting');
    }
}
