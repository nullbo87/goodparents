<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
/* 학습하기 메인 좌측 자료실 rail + 이전 학습 항목  (learn_main / learn_step 공용) */
require_once __DIR__ . '/../inc/learn.php';

/* 자료실 메뉴 라벨 -> 인라인 SVG 아이콘 (stroke, currentColor) */
function rail_icon($label) {
    $paths = array(
        '학습교재'    => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        '팁쉬트'      => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8A6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>',
        '연관 동영상' => '<circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/>',
        '전문가 그룹' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        '오프라인 모임' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        '성과보기'    => '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>',
    );
    $p = isset($paths[$label]) ? $paths[$label] : '<circle cx="12" cy="12" r="9"/>';
    return '<svg class="rail-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
         . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

function render_learn_rail() {
    $mid = current_member_id();
    $menu = array(
        array('학습교재', 1), array('팁쉬트', 1), array('연관 동영상', 1),
        array('전문가 그룹', 0), array('오프라인 모임', 1), array('성과보기', 0),
    );
    echo '<aside><div class="rail-inner">';
    echo '<div class="rail rail-sec"><h4>학습자료실</h4>';
    foreach ($menu as $m) {
        $cls = $m[1] ? '' : ' class="disabled"';
        $href = ($m[0] === '오프라인 모임') ? 'index.php?p=offline' : '#';
        echo '<a' . $cls . ' href="' . e($href) . '">' . rail_icon($m[0]) . e($m[0])
           . ($m[1] ? '' : ' <span class="small">(준비중)</span>') . '</a>';
    }
    echo '</div>';

    $sessions = all('SELECT ls.*, s.title, sc.name AS cat, lc.name AS lc
                     FROM learning_session ls
                     JOIN situation s ON s.id = ls.situation_id
                     JOIN situation_category sc ON sc.id = s.situation_category_id
                     JOIN life_cycle lc ON lc.id = sc.life_cycle_id
                     WHERE ls.member_id = ?
                     ORDER BY ls.updated_at DESC LIMIT 8', array($mid));
    echo '<div class="rail rail-sec"><h4>이전 학습 항목</h4>';
    if (!$sessions) {
        echo '<div class="prev-item muted">아직 학습 이력이 없습니다.</div>';
    }
    foreach ($sessions as $s) {
        $pct = session_progress($s);
        echo '<a class="prev-item" href="index.php?p=learn_step&sid=' . (int) $s['situation_id'] . '">';
        echo '<span class="pi-cat">' . e($s['lc']) . '.' . e($s['cat']) . '</span>';
        echo '<span class="pi-tt">' . e($s['title']) . '</span>';
        echo '<span class="pbar"><i style="width:' . $pct . '%"></i></span>';
        echo '</a>';
    }
    echo '</div>';
    echo '</div></aside>';
}
