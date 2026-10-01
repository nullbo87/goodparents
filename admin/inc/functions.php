<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

/* ---- session ---- */
if (function_exists('session_status')) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
} else {
    if (session_id() === '') { session_start(); }
}

/* ---- polyfills (구버전 PHP 대비) ---- */
if (!function_exists('hash_equals')) {
    function hash_equals($known, $user) {
        if (!is_string($known) || !is_string($user) || strlen($known) !== strlen($user)) { return false; }
        $r = 0;
        for ($i = 0, $n = strlen($known); $i < $n; $i++) { $r |= ord($known[$i]) ^ ord($user[$i]); }
        return $r === 0;
    }
}
if (!function_exists('random_bytes')) {
    function random_bytes($n) {
        $s = '';
        for ($i = 0; $i < $n; $i++) { $s .= chr(mt_rand(0, 255)); }
        return $s;
    }
}
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
/* 체크리스트 문항 유형을 관리자 목록에 표시할 짧은 라벨로 변환 */
function checklist_type_label($it) {
    $type = isset($it['answer_type']) ? $it['answer_type'] : 'scale';
    if ($type === 'choice') {
        return '다지선다(' . (isset($it['display_style']) && $it['display_style'] === 'ox' ? 'OX' : '목록') . ')';
    }
    if ($type === 'essay') { return '서술형'; }
    return '척도(' . (isset($it['scale_steps']) ? (int) $it['scale_steps'] : 5) . '단계)';
}

function flash_set($msg, $type = 'ok') { $_SESSION['flash'] = array('m' => $msg, 't' => $type); }
function flash_get() {
    if (empty($_SESSION['flash'])) { return null; }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}
