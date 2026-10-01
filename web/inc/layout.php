<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

/* 헤더의 "○○과정" — 첫째 자녀의 생애주기명 */
function member_course_label($member_id = null) {
    $member_id = $member_id ? $member_id : current_member_id();
    if (!$member_id) { return ''; }
    $row = one('SELECT lc.name
                FROM member_child mc
                JOIN life_cycle lc ON lc.id = mc.life_cycle_id
                WHERE mc.member_id = ?
                ORDER BY mc.sort_order, mc.id
                LIMIT 1', array($member_id));
    return $row ? $row['name'] : '';
}

function gnb_items() {
    return array(
        array('key' => 'about',   'href' => 'index.php?p=about',   'label' => '좋은부모란?'),
        array('key' => 'prep',    'href' => 'index.php?p=prep',    'label' => '부모되기 준비'),
        array('key' => 'learn',   'href' => 'index.php?p=learn',   'label' => '학습하기'),
        array('key' => 'offline', 'href' => 'index.php?p=offline', 'label' => '오프라인 모임'),
    );
}

/* $active: gnb key | '' ,  $opts: ['bare'=>true] 로 헤더/GNB 없이(학습 모달용) */
function render_header($title, $active = '', $opts = array()) {
    header('Content-Type: text/html; charset=utf-8');
    $bare = !empty($opts['bare']);
    $f = flash_get();
    $m = current_member();

    echo '<!doctype html><html lang="ko"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' · ' . e(APP_TITLE) . '</title>';
    echo '<link rel="stylesheet" href="assets/css/style.css?v=30"></head><body' . ($bare ? ' class="bare"' : '') . '>';
    echo '<link rel="stylesheet" href="assets/css/themify-icons/themify-icons.css">';
    if (!$bare) {
        echo '<header class="topbar"><div class="topbar-in">';
        echo '<a class="brand" href="index.php"><span class="logo">P</span><b>' . e(APP_BRAND) . '</b></a>';
        echo '<nav class="gnb">';
        foreach (gnb_items() as $it) {
            $on = ($it['key'] === $active) ? ' on' : '';
            echo '<a class="gnb-link' . $on . '" href="' . e($it['href']) . '">' . e($it['label']) . '</a>';
        }
        echo '</nav>';
        echo '<div class="topbar-right">';
        if ($m) {
            $course = member_course_label();
            echo '<span class="me"><i class="ava"></i><span class="me-t"><b>' . e($m['nickname'] ? $m['nickname'] : '회원') . '</b>';
            if ($course !== '') { echo '<small>' . e($course) . '</small>'; }
            echo '</span></span>';
            echo '<a class="tb-btn" href="index.php?p=mypage">내정보</a>';
            echo '<a class="tb-btn" href="index.php?p=logout">로그아웃</a>';
        } else {
            echo '<a class="tb-btn" href="index.php?p=login">로그인</a>';
            echo '<a class="tb-btn primary" href="index.php?p=signup">회원가입</a>';
        }
        echo '</div></div></header>';


    }

    echo '<main>';
    if ($f) { echo '<div class="flash flash-' . e($f['t']) . '">' . e($f['m']) . '</div>'; }
}

function render_footer($opts = array()) {
    echo '</main>';
    if (empty($opts['bare'])) {
        echo '<footer class="site-foot"><div class="foot-in">© ' . date('Y') . ' ' . e(APP_TITLE) . '</div></footer>';
    }
    echo '<script src="assets/app.js?v=2"></script>';
    echo '</body></html>';
}
