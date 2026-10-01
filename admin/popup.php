<?php
define('GP_ADMIN', true);
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/layout.php';

require_login();

$f = preg_replace('/[^a-z_]/', '', (string) get('f', ''));
$allow = array(
    'lifecycle_add', 'category_add', 'attitude_add',
    'situation_add', 'situation_checklist_add', 'parenting_checklist_add',
    'situation_selfcheck_add', 'situation_action_card_add', 'scale_template_add',
);
if (!in_array($f, $allow, true)) { http_response_code(404); exit('요청한 화면을 찾을 수 없습니다.'); }
require __DIR__ . '/popups/' . $f . '.php';
