<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/* 7단계 정의 (6개 콘텐츠 단계 + 총평) */
function learn_steps() {
    return array(
        1 => array('key' => 'understand', 'label' => '상황 동영상'),
        2 => array('key' => 'checklist',  'label' => '아이의 마음 읽기'),
        3 => array('key' => 'solve',      'label' => '문제 해결 길라잡이'),
        4 => array('key' => 'selfcheck',  'label' => '나의 마음 알아보기'),
        5 => array('key' => 'practice',   'label' => '나의 다짐'),
        6 => array('key' => 'action',     'label' => 'Action Card'),
        7 => array('key' => 'summary',    'label' => '총평'),
    );
}

/* 상황 + 분류 + 생애주기 조회 */
function get_situation_full($sid) {
    return one('SELECT s.*, sc.name AS cat_name, lc.id AS lc_id, lc.name AS lc_name
                FROM situation s
                JOIN situation_category sc ON sc.id = s.situation_category_id
                JOIN life_cycle lc ON lc.id = sc.life_cycle_id
                WHERE s.id = ?', array((int) $sid));
}

/* 사용 중인 동영상 목록 (kind: understand|solve) */
function situation_videos($sid, $kind) {
    return all("SELECT * FROM situation_media
                WHERE situation_id = ? AND kind = ? AND in_use = 1
                ORDER BY sort_order, id", array((int) $sid, $kind));
}

/* media -> 재생용 정보  array(type: file|youtube|vimeo|other, src, detect) */
function media_playinfo($m) {
    $url = trim((string) $m['url']);
    if ($m['media_type'] === 'file') {
        if (strpos($url, 'uploads/') === 0) {
            $src = '../admin/' . $url;                 /* admin/situation_view 가 저장한 형식 */
        } elseif ($url !== '' && (strpos($url, 'http') === 0 || $url[0] === '/')) {
            $src = $url;
        } else {
            $src = ADMIN_UPLOAD_URL . '/' . basename($url);
        }
        return array('type' => 'file', 'src' => $src, 'detect' => true);
    }
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $url, $mm)) {
        return array('type' => 'youtube', 'src' => 'https://www.youtube.com/embed/' . $mm[1], 'detect' => false);
    }
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $mm)) {
        return array('type' => 'vimeo', 'src' => 'https://player.vimeo.com/video/' . $mm[1], 'detect' => false);
    }
    return array('type' => 'other', 'src' => $url, 'detect' => false);
}

/* Action Card 이미지 -> 표시용 경로 */
function action_card_image_src($url) {
    $url = (string) $url;
    if ($url === '') { return ''; }
    if (strpos($url, 'uploads/') === 0) { return '../admin/' . $url; }
    return $url;
}

/* 학습 세션 조회/생성 */
function get_or_start_session($member_id, $sid) {
    $s = one('SELECT * FROM learning_session WHERE member_id = ? AND situation_id = ?',
             array((int) $member_id, (int) $sid));
    if (!$s) {
        q('INSERT INTO learning_session (member_id, situation_id, current_step, started_at)
           VALUES (?, ?, 1, NOW())', array((int) $member_id, (int) $sid));
        $s = one('SELECT * FROM learning_session WHERE id = ?', array(last_id()));
    }
    return $s;
}

function mark_step_done($session_id, $step) {
    $step = (int) $step;
    if ($step < 1 || $step > 6) { return; }
    q("UPDATE learning_session SET step{$step}_done = 1,
        current_step = GREATEST(current_step, ?) WHERE id = ?",
      array(min($step + 1, 7), (int) $session_id));
}

function steps_1to6_done($s) {
    for ($i = 1; $i <= 6; $i++) { if (empty($s['step' . $i . '_done'])) { return false; } }
    return true;
}

/* 7단계 완료 처리 + 뱃지 지급 (상황당 1회) */
function complete_session($session_id, $member_id, $sid) {
    q("UPDATE learning_session SET status = 'completed', current_step = 7, completed_at = NOW()
       WHERE id = ? AND status <> 'completed'", array((int) $session_id));
    $badge = one("SELECT id FROM badge WHERE code = 'situation_complete'");
    q('INSERT IGNORE INTO member_badge (member_id, badge_id, situation_id, earned_at)
       VALUES (?, ?, ?, NOW())',
      array((int) $member_id, $badge ? (int) $badge['id'] : null, (int) $sid));
}

/* 진행률 % (current_step / 7) */
function session_progress($s) {
    return (int) round(((int) $s['current_step']) / 7 * 100);
}

/* 상황별 최신 체크리스트 submission (scope: 'situation' | 'self') */
function latest_situation_submission($member_id, $sid, $scope = 'situation') {
    return one("SELECT * FROM checklist_submission
                WHERE member_id = ? AND scope = ? AND situation_id = ?
                ORDER BY id DESC LIMIT 1", array((int) $member_id, $scope, (int) $sid));
}
