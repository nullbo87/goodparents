<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/functions.php';

function is_logged_in() { return !empty($_SESSION['admin_ok']); }

function require_login() {
    if (!is_logged_in()) { redirect('index.php?p=login'); }
}

function attempt_login($pw) {
    if (hash_equals(ADMIN_PASSWORD, (string) $pw)) {
        $_SESSION['admin_ok'] = true;
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
