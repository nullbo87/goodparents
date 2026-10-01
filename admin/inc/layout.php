<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

function nav_icon($key) {
    $p = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
    $paths = array(
        'main'                => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/>',
        'situations'          => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3.6" cy="6" r="1"/><circle cx="3.6" cy="12" r="1"/><circle cx="3.6" cy="18" r="1"/>',
        'lifecycles'          => '<path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v5h-5"/>',
        'categories'          => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'attitudes'          => '<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
        'parenting_checklist' => '<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M9 5V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1"/><path d="m8.5 13 2.2 2.2L15 11"/>',
        'scale_templates'     => '<line x1="4" y1="20" x2="4" y2="12"/><line x1="9" y1="20" x2="9" y2="8"/><line x1="14" y1="20" x2="14" y2="4"/><line x1="19" y1="20" x2="19" y2="10"/>',
    );
    if (!isset($paths[$key])) { return ''; }
    return $p . $paths[$key] . '</svg>';
}

function nav_link($key, $href, $label, $active, $sub = false) {
    $cls = 'gnb-link' . ($sub ? ' sub' : '') . ($key === $active ? ' on' : '');
    return '<a class="' . $cls . '" href="' . e($href) . '">'
         . nav_icon($key) . '<span>' . e($label) . '</span></a>';
}

function nav_lc_icon($name) {
    $wrap = '<svg class="lc-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
    $m = array(
        '영유아기' => '<circle cx="12" cy="12" r="8"/><path d="M9 15a3.5 3.5 0 0 0 6 0"/><path d="M9 10h.01"/><path d="M15 10h.01"/>',
        '초등입학' => '<path d="M6 9a6 6 0 0 1 12 0v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2z"/><path d="M9 9V7a3 3 0 0 1 6 0v2"/><path d="M8 13h8"/>',
        '초등과정' => '<path d="M5 4h11a1 1 0 0 1 1 1v15H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M17 4v16"/>',
        '중등과정' => '<path d="m12 4 9 4-9 4-9-4 9-4z"/><path d="M6 10v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>',
        '고등과정' => '<path d="M3 21h18"/><path d="M5 21V9l7-4 7 4v12"/><path d="M10 21v-5h4v5"/>',
    );
    $inner = isset($m[$name]) ? $m[$name] : '<circle cx="12" cy="12" r="3.4"/><path d="M12 5v2.5M12 16.5V19M5 12h2.5M16.5 12H19"/>';
    return $wrap . $inner . '</svg>';
}

function render_header($title, $active) {
    header('Content-Type: text/html; charset=utf-8');
    $f = flash_get();
    echo '<!doctype html><html lang="ko"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' · ' . e(APP_TITLE) . '</title>';
    echo '<link rel="stylesheet" href="assets/style.css?v=10"></head><body>';
    echo '<header class="topbar"><span class="logo">로고</span>';
    echo '<h1>' . e(APP_TITLE) . '</h1>';
    echo '<span class="topbar-right"><a href="index.php?p=logout">로그아웃</a></span></header>';
    echo '<div class="layout"><nav class="gnb">';

    echo nav_link('main', 'index.php', '메인화면', $active);
    echo nav_link('parenting_checklist', 'index.php?p=parenting_checklist', '부모양육태도 체크리스트', $active);

    echo '<div class="gnb-group">생애주기별 상황목록</div>';
    $cur_lc = isset($_GET['lc']) ? (int) $_GET['lc'] : 0;
    foreach (all('SELECT id, name FROM life_cycle WHERE is_active = 1 ORDER BY sort_order, id') as $lc) {
        $on = ($active === 'situations' && $cur_lc === (int) $lc['id']) ? ' on' : '';
        echo '<a class="gnb-link sub lc' . $on . '" href="index.php?p=situations&lc=' . (int) $lc['id'] . '">'
           . nav_lc_icon($lc['name']) . '<span>' . e($lc['name']) . '</span></a>';
    }

    echo '<div class="gnb-group">기본정보관리</div>';
    echo nav_link('lifecycles', 'index.php?p=lifecycles', '생애주기 관리', $active, true);
    echo nav_link('categories', 'index.php?p=categories', '생애주기별 상황분류 관리', $active, true);
    echo nav_link('attitudes', 'index.php?p=attitudes', '양육태도 관리', $active, true);
    echo nav_link('scale_templates', 'index.php?p=scale_templates', '척도 라벨 템플릿 관리', $active, true);

    echo '</nav><main class="content">';
    if ($f) { echo '<div class="flash flash-' . e($f['t']) . '">' . e($f['m']) . '</div>'; }
}

function render_footer() {
    echo '</main></div>';
    echo '<script>function pop(a){window.open(a.href,"gp_popup","width=560,height=620,scrollbars=yes,resizable=yes");return false;}</script>';
    echo '</body></html>';
}

/* ---------- popup chrome ---------- */
function popup_header($title) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="ko"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . '</title>';
    echo '<link rel="stylesheet" href="assets/style.css?v=10"></head><body class="popup">';
    echo '<div class="popup-box"><h2 class="popup-title">' . e($title) . '</h2>';
}
function popup_footer() { echo '</div></body></html>'; }

function popup_done() {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8">';
    echo '<body style="font-family:system-ui,sans-serif;padding:24px;background:#0e1b2c;color:#bcc7e4">저장되었습니다. 창을 닫습니다…';
    echo '<script>try{if(window.opener&&!window.opener.closed){window.opener.location.reload();}}catch(e){}';
    echo 'setTimeout(function(){window.close();},250);</script>';
    exit;
}
