<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

/* 상황(콘텐츠) 좋아요 토글 — POST 전용 */
if (!is_post()) { redirect('index.php?p=learn'); }
csrf_check();

$mid = current_member_id();
$sid = int_or_zero(post('situation_id'));
$back = post('back');
if (!$back || strpos($back, 'index.php') !== 0) { $back = 'index.php?p=learn'; }

if ($sid > 0 && one('SELECT id FROM situation WHERE id = ?', array($sid))) {
    $has = one('SELECT id FROM situation_like WHERE member_id = ? AND situation_id = ?', array($mid, $sid));
    if ($has) {
        q('DELETE FROM situation_like WHERE id = ?', array((int) $has['id']));
    } else {
        q('INSERT IGNORE INTO situation_like (member_id, situation_id) VALUES (?, ?)', array($mid, $sid));
    }
}
redirect($back);
