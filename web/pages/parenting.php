<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/../inc/scoring.php';
require_once __DIR__ . '/../inc/checklist_render.php';

$mid = current_member_id();
$items = all("SELECT * FROM parenting_checklist_item WHERE is_active = 1 ORDER BY sort_order, id");

/* 보기(4지선다) 로드 */
$opts = array();
foreach ($items as $it) {
    if ((isset($it['answer_type']) ? $it['answer_type'] : 'scale') === 'choice') {
        $opts[(int) $it['id']] = all("SELECT * FROM checklist_item_option
            WHERE scope = 'parenting' AND item_id = ? ORDER BY sort_order, id", array((int) $it['id']));
    }
}

if (is_post()) {
    csrf_check();
    $err = array();
    $answers = array();
    foreach ($items as $it) {
        $iid  = (int) $it['id'];
        $type = isset($it['answer_type']) ? $it['answer_type'] : 'scale';
        if ($type === 'scale') {
            $v = int_or_zero(post("s_$iid"));
            $steps = max(1, (int) (isset($it['scale_steps']) ? $it['scale_steps'] : 5));
            if ($v < 1 || $v > $steps) { $err[] = true; }
            $answers[$iid] = array('scale', $v, null, null);
        } elseif ($type === 'choice') {
            $oid = int_or_zero(post("o_$iid"));
            $valid = false;
            foreach (isset($opts[$iid]) ? $opts[$iid] : array() as $op) { if ((int) $op['id'] === $oid) { $valid = true; } }
            if (!$valid) { $err[] = true; }
            $answers[$iid] = array('choice', null, $oid, null);
        } else {
            $answers[$iid] = array('essay', null, null, trim(post("e_$iid")));
        }
    }
    if ($err) {
        flash_set('모든 문항에 응답해 주세요.', 'err');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            q("INSERT INTO checklist_submission (member_id, scope, submitted_at)
               VALUES (?, 'parenting', NOW())", array($mid));
            $subId = last_id();
            foreach ($answers as $iid => $a) {
                q('INSERT INTO checklist_response
                   (submission_id, item_id, scale_value, option_id, essay_text, ai_text, ai_status)
                   VALUES (?, ?, ?, ?, ?, ?, ?)',
                  array($subId, $iid, $a[1], $a[2], nz($a[3]),
                        $a[0] === 'essay' ? ESSAY_AI_STUB : null,
                        $a[0] === 'essay' ? 'pending' : 'na'));
            }
            save_submission_result($subId);
            $pdo->commit();
            redirect('index.php?p=parenting_result&s=' . $subId);
        } catch (Exception $ex) {
            $pdo->rollBack();
            flash_set('저장 실패: ' . $ex->getMessage(), 'err');
        }
    }
}

render_header('부모양육태도 체크리스트', '');
?>
<div class="wrap">
<h2 class="section-title">나의 양육 태도는?</h2>
<p class="hint center">당신이 아이를 어떤 성향으로 키우고 있는지 체크하는 항목입니다. 하나하나 정성스럽게 답변해 주시면 감사하겠습니다.</p>

<?php if (!$items): ?>
  <div class="panel empty">등록된 체크리스트 문항이 없습니다. 관리자에서 문항을 먼저 등록하세요.</div>
<?php else: ?>
<form method="post" class="panel">
  <?= csrf_field() ?>
  <?php $n = 1; foreach ($items as $it): render_checklist_question('parenting', $it, $n++); endforeach; ?>

  <div class="center mt">
    <button type="submit" class="btn lg">제출하기</button>
  </div>
</form>
</div>
<?php endif; ?>
<?php
render_footer();
