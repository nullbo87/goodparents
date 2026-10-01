<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

/* ---- session ---- */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* ---- polyfills (PHP 7.2 이하 대비) ---- */
if (!function_exists('str_contains')) {
    function str_contains($h, $n) { return $n === '' || strpos($h, $n) !== false; }
}

/* ---- helpers ---- */
function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header('Location: ' . $url); exit; }
function is_post() { return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST'; }
function post($k, $d = '') { return isset($_POST[$k]) ? $_POST[$k] : $d; }
function get($k, $d = '') { return isset($_GET[$k]) ? $_GET[$k] : $d; }
function nz($v) { $v = trim((string) $v); return $v === '' ? null : $v; }
function int_or_zero($v) { return (int) preg_replace('/[^0-9\-]/', '', (string) $v); }
function clamp_int($v, $lo, $hi) { $v = (int) $v; return $v < $lo ? $lo : ($v > $hi ? $hi : $v); }

/* 페이지 라우팅 키 정리 */
function route_key($v, $default) {
    $v = preg_replace('/[^a-z0-9_]/', '', (string) $v);
    return $v === '' ? $default : $v;
}

/* ---- CSRF ---- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check() {
    if (is_post()) {
        $t = post('csrf');
        if (!$t || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string) $t)) {
            http_response_code(400);
            exit('잘못된 요청입니다. (CSRF 토큰 불일치) 뒤로 가서 다시 시도하세요.');
        }
    }
}

/* ---- flash ---- */
function flash_set($msg, $type = 'ok') { $_SESSION['flash'] = array('m' => $msg, 't' => $type); }
function flash_get() {
    if (empty($_SESSION['flash'])) { return null; }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

/* ---- SNS 로그인 버튼 (login / signup 공용) ----
   OAuth 연동 전까지는 클릭해도 아무 동작 없음(type=button, 핸들러 없음).
   시각적으로는 활성 상태로 보이되 aria-disabled 로 보조기기에 상태 안내. */
function sns_icon($key) {
    switch ($key) {
        case 'fb':
            return '<svg class="sns-ic" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M24 12.07C24 5.41 18.63 0 12 0S0 5.41 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.43c0-3.01 1.79-4.67 4.53-4.67 1.31 0 2.69.24 2.69.24v2.96H15.8c-1.49 0-1.95.93-1.95 1.88v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07"/></svg>';
        case 'gg':
            return '<svg class="sns-ic" viewBox="0 0 24 24" aria-hidden="true">'
                 . '<path fill="#4285F4" d="M23.06 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h6.2a5.31 5.31 0 0 1-2.3 3.48v2.9h3.72c2.18-2.01 3.44-4.96 3.44-8.39"/>'
                 . '<path fill="#34A853" d="M12 24c3.1 0 5.71-1.03 7.61-2.78l-3.72-2.9c-1.03.69-2.35 1.1-3.89 1.1-2.98 0-5.5-2.01-6.4-4.72H1.75v2.98A12 12 0 0 0 12 24"/>'
                 . '<path fill="#FBBC05" d="M5.6 14.8a7.2 7.2 0 0 1 0-4.6V7.22H1.75a12 12 0 0 0 0 10.56z"/>'
                 . '<path fill="#EA4335" d="M12 4.75c1.68 0 3.19.58 4.38 1.71l3.28-3.28C17.7 1.19 15.1 0 12 0A12 12 0 0 0 1.75 6.22L5.6 9.2C6.5 6.49 9.02 4.75 12 4.75"/>'
                 . '</svg>';
        case 'nv':
            return '<svg class="sns-ic" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16.27 12.84 7.38 0H0v24h7.73V11.16L16.62 24H24V0h-7.73z"/></svg>';
        case 'kk':
            return '<svg class="sns-ic" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 3C6.48 3 2 6.53 2 10.88c0 2.8 1.87 5.26 4.68 6.63-.15.53-.96 3.3-.99 3.52 0 0-.02.17.09.23.11.07.24.02.24.02.31-.04 3.58-2.34 4.14-2.74.57.08 1.16.13 1.75.13 5.52 0 10-3.53 10-7.88S17.52 3 12 3"/></svg>';
        case 'ap':
            return '<svg class="sns-ic" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.05 12.54c-.03-2.9 2.37-4.29 2.47-4.36-1.35-1.97-3.44-2.24-4.19-2.27-1.78-.18-3.48 1.05-4.38 1.05-.9 0-2.3-1.02-3.78-1-1.95.03-3.74 1.13-4.74 2.87-2.02 3.5-.52 8.69 1.45 11.54.96 1.39 2.11 2.96 3.61 2.9 1.44-.06 1.99-.94 3.74-.94 1.74 0 2.24.94 3.77.91 1.55-.03 2.54-1.41 3.49-2.81.55-.8.98-1.68 1.31-2.61-.03-.01-2.5-.96-2.53-3.82M14.14 3.66c.8-.96 1.33-2.31 1.18-3.66-1.14.05-2.53.76-3.35 1.72-.74.85-1.38 2.22-1.21 3.53 1.27.1 2.58-.64 3.38-1.59"/></svg>';
    }
    return '';
}

function render_sns_buttons() {
    $items = array(
        array('fb', 'Facebook 로그인'),
        array('gg', 'Google 로그인'),
        array('nv', '네이버 로그인'),
        array('kk', 'Kakao 로그인'),
        array('ap', '애플 로그인'),
    );
    echo '<div class="sns">';
    foreach ($items as $it) {
        echo '<button class="' . $it[0] . '" type="button" aria-disabled="true">'
           . sns_icon($it[0]) . '<span>' . e($it[1]) . '</span></button>';
    }
    echo '</div>';
}

/* ---- 생애주기명(관리자 등록명 그대로) ---- */
function life_cycles($active_only = true) {
    $sql = 'SELECT id, name FROM life_cycle';
    if ($active_only) { $sql .= ' WHERE is_active = 1'; }
    $sql .= ' ORDER BY sort_order, id';
    return all($sql);
}
function life_cycle_name($id) {
    $id = (int) $id;
    if (!$id) { return ''; }
    $r = one('SELECT name FROM life_cycle WHERE id = ?', array($id));
    return $r ? $r['name'] : '';
}
