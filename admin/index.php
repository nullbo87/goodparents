<?php
define('GP_ADMIN', true);
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/layout.php';

$p = preg_replace('/[^a-z_]/', '', (string) get('p', 'main'));
if ($p === '') { $p = 'main'; }

if ($p === 'login')  { require __DIR__ . '/pages/login.php'; exit; }
if ($p === 'logout') { do_logout(); redirect('index.php?p=login'); }

require_login();

$routes = array(
    'main'                => 'dashboard.php',
    'situations'          => 'situations.php',
    'situation_view'      => 'situation_view.php',
    'lifecycles'          => 'lifecycles.php',
    'categories'          => 'categories.php',
    'attitudes'           => 'attitudes.php',
    'parenting_checklist' => 'parenting_checklist.php',
    'scale_templates'     => 'scale_templates.php',
);
if (!isset($routes[$p])) { http_response_code(404); exit('페이지를 찾을 수 없습니다.'); }
require __DIR__ . '/pages/' . $routes[$p];
