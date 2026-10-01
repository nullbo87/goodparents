<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

/* 오프라인 모임 / 교육안내 공용 렌더 (kind: meeting | guide) */
function render_meeting_page($kind, $title, $active_gnb) {

    /* 좋아요 토글 (로그인 필요) */
    if (is_post() && post('act') === 'like') {
        csrf_check();
        if (!is_logged_in()) { redirect('index.php?p=login'); }
        $mid = current_member_id();
        $meId = int_or_zero(post('meeting_id'));
        $has = one('SELECT id FROM offline_meeting_like WHERE member_id = ? AND meeting_id = ?', array($mid, $meId));
        if ($has) {
            q('DELETE FROM offline_meeting_like WHERE id = ?', array((int) $has['id']));
        } else {
            q('INSERT IGNORE INTO offline_meeting_like (member_id, meeting_id) VALUES (?, ?)', array($mid, $meId));
        }
        redirect('index.php?p=' . ($kind === 'guide' ? 'offline_guide' : 'offline'));
    }

    $mid = current_member_id();
    $rows = all("SELECT m.*, lc.name AS lc_name,
                 (SELECT COUNT(*) FROM offline_meeting_like l WHERE l.meeting_id = m.id) AS likes
                 FROM offline_meeting m
                 LEFT JOIN life_cycle lc ON lc.id = m.life_cycle_id
                 WHERE m.kind = ? AND m.is_published = 1
                 ORDER BY m.sort_order, m.id", array($kind));

    $myLikes = array();
    if ($mid) {
        foreach (all('SELECT meeting_id FROM offline_meeting_like WHERE member_id = ?', array($mid)) as $r) {
            $myLikes[(int) $r['meeting_id']] = true;
        }
    }

    render_header($title, $active_gnb);
    echo '<div class="hero"><div class="cont"><h2>' . e($title) . '</h2>';
    if ($kind === 'meeting') {
        echo '<p><a class="btn sm" href="index.php?p=offline_guide">오프라인 교육안내</a></p>';
    } else {
        echo '<p><a class="btn sm" href="index.php?p=offline">오프라인 모임</a></p>';
    }
    echo '</div>';
    echo '<div class="photo"><img src= "./assets/img/offline-timg.png" title=""></div>';
    echo '</div>';
    echo '<div class="wrap">';
    echo '<h2 class="page-title mt">' . e($title) . '</h2>';
    echo '<div class="cards">';
    if (!$rows) { echo '<div class="empty" style="grid-column:1/-1">등록된 항목이 없습니다.</div>'; }
    foreach ($rows as $m) {
        echo '<div class="card">';
        echo '<div class="thumb movie">';
        if ($m['image']) { echo '<img src="' . e($m['image']) . '" alt="">'; }
        echo '</div><div class="body">';
        if ($m['lc_name']) { echo '<div class="cat">' . e($m['lc_name']) . '</div>'; }
        echo '<div class="tt">' . e($m['title']) . '</div>';
        if ($m['description']) { echo '<p class="exp">' . e(mb_strimwidth($m['description'], 0, 80, '…', 'UTF-8')) . '</p>'; }
        echo '<div class="meta">';
        if ($m['meet_at']) { echo '<span>' . e(date('n월 j일 H:i', strtotime($m['meet_at']))) . '</span>'; }
        if ($m['location']) { echo '<span>' . e($m['location']) . '</span>'; }
        echo '</div>';
        echo '<form method="post" style="margin-top:8px">' . csrf_field()
           . '<input type="hidden" name="act" value="like">'
           . '<input type="hidden" name="meeting_id" value="' . (int) $m['id'] . '">'
           . '<button type="submit" class="mini">'
           . (isset($myLikes[(int) $m['id']]) ? '♥' : '♡') . ' ' . (int) $m['likes'] . '</button>';
        if ($m['external_url']) {
            echo ' <a class="mini" href="' . e($m['external_url']) . '" target="_blank" rel="noopener">자세히</a>';
        }
        echo '</form>';
        echo '</div></div>';
    }
    echo '</div>';
    echo '</div>';	
    render_footer();
}
