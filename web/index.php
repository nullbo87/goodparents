<?php
define('GP_WEB', true);
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/layout.php';

$p = route_key(get('p', 'main'), 'main');

/* 인증 라우트 */
if ($p === 'login')  { require __DIR__ . '/pages/login.php';  exit; }
if ($p === 'logout') { do_logout(); redirect('index.php'); }
if ($p === 'signup') { require __DIR__ . '/pages/signup.php'; exit; }

/* 공개 라우트 (비로그인 열람 가능) */
$public = array(
    'main'          => 'main.php',
    'about'         => 'about.php',
    'prep'          => 'prep.php',
    'offline'       => 'offline.php',
    'offline_guide' => 'offline_guide.php',
);

/* 로그인 필요 */
$auth = array(
    'mypage'           => 'mypage_profile.php',
    'mypage_children'  => 'mypage_children.php',
    'parenting'        => 'parenting.php',
    'parenting_result' => 'parenting_result.php',
);

/* 로그인 + 부모양육태도 진단 완료 필요 */
$learning = array(
    'learn'      => 'learn_main.php',
    'learn_step' => 'learn_step.php',
    'like'       => 'action_like.php',
);

if (isset($public[$p])) {
    require __DIR__ . '/pages/' . $public[$p];
} elseif (isset($auth[$p])) {
    require_login();
    require __DIR__ . '/pages/' . $auth[$p];
} elseif (isset($learning[$p])) {
    require_learning_ready();
    require __DIR__ . '/pages/' . $learning[$p];
} else {
    http_response_code(404);
    render_header('페이지 없음');
    echo '<p class="empty">페이지를 찾을 수 없습니다.</p>';
    render_footer();
}
