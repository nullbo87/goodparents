<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/../inc/learn.php';
require_once __DIR__ . '/../inc/scoring.php';
require_once __DIR__ . '/../inc/checklist_render.php';

$mid = current_member_id();
$sid = int_or_zero(get('sid'));
$sit = get_situation_full($sid);
if (!$sit) {
    render_header('학습', 'learn');
    echo '<div class="wrap"><p class="empty">상황을 찾을 수 없습니다. <a href="index.php?p=learn">학습하기로</a></p></div>';
    render_footer();
    return;
}

$sess = get_or_start_session($mid, $sid);

/* 접근 가능한 최고 단계 계산 */
$highest = 1;
for ($i = 1; $i <= 6; $i++) { if (!empty($sess['step' . $i . '_done'])) { $highest = $i + 1; } }
$all16 = steps_1to6_done($sess);
if (!$all16 && $highest > 6) { $highest = 6; }
$maxAllowed = $all16 ? 7 : min($highest, 6);

/* ---- POST 처리 ---- */
if (is_post()) {
    csrf_check();
    $act = post('act');

    if ($act === 'step_done') {
        $st = clamp_int(post('step', 1), 1, 6);
        mark_step_done($sess['id'], $st);
        redirect('index.php?p=learn_step&sid=' . $sid . '&step=' . min($st + 1, 7));

    } elseif ($act === 'checklist') {
        $items = all("SELECT * FROM situation_checklist_item WHERE situation_id = ? AND is_active = 1
                      ORDER BY sort_order, id", array($sid));
        $optmap = array();
        foreach ($items as $it) {
            if ((isset($it['answer_type']) ? $it['answer_type'] : 'scale') === 'choice') {
                $optmap[(int) $it['id']] = all("SELECT id FROM checklist_item_option
                    WHERE scope = 'situation' AND item_id = ? ORDER BY sort_order, id", array((int) $it['id']));
            }
        }
        $err = false; $answers = array();
        foreach ($items as $it) {
            $iid = (int) $it['id'];
            $type = isset($it['answer_type']) ? $it['answer_type'] : 'scale';
            if ($type === 'scale') {
                $v = int_or_zero(post("s_$iid"));
                $steps = max(1, (int) (isset($it['scale_steps']) ? $it['scale_steps'] : 5));
                if ($v < 1 || $v > $steps) { $err = true; }
                $answers[$iid] = array('scale', $v, null, null);
            } elseif ($type === 'choice') {
                $oid = int_or_zero(post("o_$iid"));
                $ok = false;
                foreach (isset($optmap[$iid]) ? $optmap[$iid] : array() as $o) { if ((int) $o['id'] === $oid) { $ok = true; } }
                if (!$ok) { $err = true; }
                $answers[$iid] = array('choice', null, $oid, null);
            } else {
                $answers[$iid] = array('essay', null, null, trim(post("e_$iid")));
            }
        }
        if ($err || !$items) {
            flash_set($items ? '모든 문항에 응답해 주세요.' : '등록된 체크리스트 문항이 없습니다.', 'err');
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=2');
        }
        $pdo = db();
        $pdo->beginTransaction();
        try {
            /* 재제출: 이전 상황 체크리스트 결과 덮어쓰기 */
            foreach (all("SELECT id FROM checklist_submission
                          WHERE member_id = ? AND scope = 'situation' AND situation_id = ?", array($mid, $sid)) as $old) {
                q('DELETE FROM checklist_submission WHERE id = ?', array((int) $old['id']));
            }
            q("INSERT INTO checklist_submission (member_id, scope, situation_id, submitted_at)
               VALUES (?, 'situation', ?, NOW())", array($mid, $sid));
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
            mark_step_done($sess['id'], 2);
            $pdo->commit();
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=3');
        } catch (Exception $ex) {
            $pdo->rollBack();
            flash_set('저장 실패: ' . $ex->getMessage(), 'err');
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=2');
        }

    } elseif ($act === 'selfcheck') {
        $items = all("SELECT * FROM situation_selfcheck_item WHERE situation_id = ? AND is_active = 1
                      ORDER BY sort_order, id", array($sid));
        $optmap = array();
        foreach ($items as $it) {
            if ((isset($it['answer_type']) ? $it['answer_type'] : 'scale') === 'choice') {
                $optmap[(int) $it['id']] = all("SELECT id FROM checklist_item_option
                    WHERE scope = 'self' AND item_id = ? ORDER BY sort_order, id", array((int) $it['id']));
            }
        }
        $err = false; $answers = array();
        foreach ($items as $it) {
            $iid = (int) $it['id'];
            $type = isset($it['answer_type']) ? $it['answer_type'] : 'scale';
            if ($type === 'scale') {
                $v = int_or_zero(post("s_$iid"));
                $steps = max(1, (int) (isset($it['scale_steps']) ? $it['scale_steps'] : 5));
                if ($v < 1 || $v > $steps) { $err = true; }
                $answers[$iid] = array('scale', $v, null, null);
            } elseif ($type === 'choice') {
                $oid = int_or_zero(post("o_$iid"));
                $ok = false;
                foreach (isset($optmap[$iid]) ? $optmap[$iid] : array() as $o) { if ((int) $o['id'] === $oid) { $ok = true; } }
                if (!$ok) { $err = true; }
                $answers[$iid] = array('choice', null, $oid, null);
            } else {
                $answers[$iid] = array('essay', null, null, trim(post("e_$iid")));
            }
        }
        if ($err || !$items) {
            flash_set($items ? '모든 문항에 응답해 주세요.' : '등록된 문항이 없습니다.', 'err');
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=4');
        }
        $pdo = db();
        $pdo->beginTransaction();
        try {
            foreach (all("SELECT id FROM checklist_submission
                          WHERE member_id = ? AND scope = 'self' AND situation_id = ?", array($mid, $sid)) as $old) {
                q('DELETE FROM checklist_submission WHERE id = ?', array((int) $old['id']));
            }
            q("INSERT INTO checklist_submission (member_id, scope, situation_id, submitted_at)
               VALUES (?, 'self', ?, NOW())", array($mid, $sid));
            $subId = last_id();
            foreach ($answers as $iid => $a) {
                q('INSERT INTO checklist_response
                   (submission_id, item_id, scale_value, option_id, essay_text, ai_text, ai_status)
                   VALUES (?, ?, ?, ?, ?, ?, ?)',
                  array($subId, $iid, $a[1], $a[2], nz($a[3]),
                        $a[0] === 'essay' ? ESSAY_AI_STUB : null,
                        $a[0] === 'essay' ? 'pending' : 'na'));
            }
            /* 양육태도 점수는 계산·저장만 하고 사용자 화면에는 노출하지 않음 */
            save_submission_result($subId);
            mark_step_done($sess['id'], 4);
            $pdo->commit();
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=5');
        } catch (Exception $ex) {
            $pdo->rollBack();
            flash_set('저장 실패: ' . $ex->getMessage(), 'err');
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=4');
        }

    } elseif ($act === 'practice') {
        $txt = trim(post('practice'));
        if ($txt === '') {
            flash_set('나의 다짐을 입력해 주세요.', 'err');
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=5');
        }
        q('INSERT INTO practice_note (member_id, situation_id, content, ai_text, ai_status)
           VALUES (?, ?, ?, ?, "pending")
           ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = NOW()',
          array($mid, $sid, $txt, ESSAY_AI_STUB));
        mark_step_done($sess['id'], 5);
        redirect('index.php?p=learn_step&sid=' . $sid . '&step=6');

    } elseif ($act === 'action_card') {
        $cardIds = isset($_POST['card_id']) && is_array($_POST['card_id']) ? $_POST['card_id'] : array();
        $valid = all('SELECT id FROM situation_action_card WHERE situation_id = ? AND is_active = 1', array($sid));
        $validIds = array();
        foreach ($valid as $v) { $validIds[(int) $v['id']] = true; }
        $picked = array();
        foreach ($cardIds as $cid) {
            $cid = (int) $cid;
            if (isset($validIds[$cid])) { $picked[$cid] = true; }
        }
        if (!$picked) {
            flash_set('Action Card를 1개 이상 선택해 주세요.', 'err');
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=6');
        }
        $pdo = db();
        $pdo->beginTransaction();
        try {
            q('DELETE FROM situation_action_card_select WHERE member_id = ? AND situation_id = ?', array($mid, $sid));
            foreach (array_keys($picked) as $cid) {
                q('INSERT INTO situation_action_card_select (member_id, situation_id, card_id) VALUES (?, ?, ?)',
                  array($mid, $sid, $cid));
            }
            mark_step_done($sess['id'], 6);
            $pdo->commit();
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=7');
        } catch (Exception $ex) {
            $pdo->rollBack();
            flash_set('저장 실패: ' . $ex->getMessage(), 'err');
            redirect('index.php?p=learn_step&sid=' . $sid . '&step=6');
        }
    }
}

/* ---- 표시할 단계 ---- */
$step = clamp_int(get('step', $sess['current_step']), 1, 7);
if ($step > $maxAllowed) {
    flash_set('이전 단계를 먼저 완료해 주세요.', 'err');
    $step = $maxAllowed;
}
if ($step === 7 && $all16) {
    complete_session($sess['id'], $mid, $sid);
    $sess = one('SELECT * FROM learning_session WHERE id = ?', array($sess['id']));
}

$STEPS = learn_steps();

render_header($sit['title'], 'learn');
?>

<div class="wrap learn-step-wrap">
<div class="learn-step-layout">
<aside class="learn-side">
 <div class="learn-side-inner">
  <h4 class="learn-side-h">학습단계</h4>
  <div class="steps">
  <?php foreach ($STEPS as $i => $st):
    $cls = '';
    if ($i < $step) { $cls = 'done'; }
    if ($i === $step) { $cls = 'on'; }
    if ($i > $maxAllowed) { $cls .= ' locked'; }
    $clickable = ($i <= $maxAllowed); ?>
    <div class="step <?= trim($cls) ?>">
      <?php if ($clickable): ?><a href="index.php?p=learn_step&sid=<?= $sid ?>&step=<?= $i ?>"><?php endif; ?>
        <span class="dot"><?= $i ?></span><span class="lb"><?= e($st['label']) ?></span>
      <?php if ($clickable): ?></a><?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>
 </div>
</aside>
<div class="learn-body">
  <div class="learn-sit">
    <span class="learn-sit-lc"><?= e($sit['lc_name']) ?></span>
    <h2 class="learn-sit-tt"><?= e($sit['title']) ?></h2>
  </div>

<?php
/* ---- 단계별 본문 ---- */
function render_video_block($sid, $kind, $title) {
    $vids = situation_videos($sid, $kind);
    echo '<h3 class="page-title">' . e($title) . '</h3>';
    if (!$vids) {
        echo '<div class="videobox"><span class="novid">등록된 동영상이 없습니다.</span></div>';
        return false;
    }
    $detectAny = false;
    foreach ($vids as $idx => $v) {
        $pi = media_playinfo($v);
        $detect = $pi['detect'] ? 1 : 0;
        if ($detect) { $detectAny = true; }
        echo '<div class="videobox"' . ($idx === 0 && $detect ? ' data-detect="1"' : '') . '>';
        if ($pi['type'] === 'file') {
            echo '<video controls preload="metadata" src="' . e($pi['src']) . '"></video>';
        } elseif ($pi['type'] === 'youtube' || $pi['type'] === 'vimeo') {
            echo '<iframe src="' . e($pi['src']) . '" allowfullscreen></iframe>';
        } else {
            echo '<a class="novid" href="' . e($pi['src']) . '" target="_blank" rel="noopener">동영상 열기 &raquo;</a>';
        }
        echo '</div>';
        if ($idx < count($vids) - 1) { echo '<div style="height:12px"></div>'; }
    }
    return $detectAny;
}

if ($step === 1):
    $detect = render_video_block($sid, 'understand', '상황 동영상');
elseif ($step === 3):
    $detect = render_video_block($sid, 'solve', '문제 해결 길라잡이');
    $html = trim((string) (isset($sit['solve_html']) ? $sit['solve_html'] : ''));
    if ($html !== ''):
        echo '<div class="expert-text">' . $html . '</div>';
    endif;
endif;
?>

<?php if ($step === 1 || $step === 3): ?>
  <div class="learn-nav">
    <?php if ($step > 1): ?>
      <a class="btn gray" href="index.php?p=learn_step&sid=<?= $sid ?>&step=<?= $step - 1 ?>">이전학습</a>
    <?php else: ?><span></span><?php endif; ?>
    <form method="post" style="margin:0">
      <?= csrf_field() ?>
      <input type="hidden" name="act" value="step_done">
      <input type="hidden" name="step" value="<?= $step ?>">
      <button type="submit" class="btn" data-next-lock="<?= !empty($detect) ? '1' : '0' ?>">다음학습</button>
    </form>
  </div>

<?php elseif ($step === 2):
  $items = all("SELECT * FROM situation_checklist_item WHERE situation_id = ? AND is_active = 1
                ORDER BY sort_order, id", array($sid));
  $optmap = array();
  foreach ($items as $it) {
      if ((isset($it['answer_type']) ? $it['answer_type'] : 'scale') === 'choice') {
          $optmap[(int) $it['id']] = all("SELECT * FROM checklist_item_option
              WHERE scope = 'situation' AND item_id = ? ORDER BY sort_order, id", array((int) $it['id']));
      }
  }
  $prev = latest_situation_submission($mid, $sid, 'situation');
  $prevAns = array();
  if ($prev) {
      foreach (all('SELECT * FROM checklist_response WHERE submission_id = ?', array((int) $prev['id'])) as $r) {
          $prevAns[(int) $r['item_id']] = $r;
      }
  }
?>
  <h3 class="page-title">아이의 마음 읽기</h3>
  <form method="post" class="panel">
    <?= csrf_field() ?><input type="hidden" name="act" value="checklist">
    <?php if (!$items): ?>
      <p class="empty">등록된 체크리스트 문항이 없습니다.</p>
    <?php endif; ?>
    <?php $n = 1; foreach ($items as $it):
          $iid = (int) $it['id'];
          $pa = isset($prevAns[$iid]) ? $prevAns[$iid] : null;
          render_checklist_question('situation', $it, $n++, $pa, '구체적인 사례를 적어주세요');
    endforeach; ?>
    <div class="learn-nav">
      <a class="btn gray" href="index.php?p=learn_step&sid=<?= $sid ?>&step=1">이전학습</a>
      <button type="submit" class="btn">제출하기</button>
    </div>
  </form>

<?php elseif ($step === 4):
  $items = all("SELECT * FROM situation_selfcheck_item WHERE situation_id = ? AND is_active = 1
                ORDER BY sort_order, id", array($sid));
  $optmap = array();
  foreach ($items as $it) {
      if ((isset($it['answer_type']) ? $it['answer_type'] : 'scale') === 'choice') {
          $optmap[(int) $it['id']] = all("SELECT * FROM checklist_item_option
              WHERE scope = 'self' AND item_id = ? ORDER BY sort_order, id", array((int) $it['id']));
      }
  }
  $prev = latest_situation_submission($mid, $sid, 'self');
  $prevAns = array();
  if ($prev) {
      foreach (all('SELECT * FROM checklist_response WHERE submission_id = ?', array((int) $prev['id'])) as $r) {
          $prevAns[(int) $r['item_id']] = $r;
      }
  }
?>
  <h3 class="page-title">나의 마음 알아보기</h3>
  <form method="post" class="panel">
    <?= csrf_field() ?><input type="hidden" name="act" value="selfcheck">
    <?php if (!$items): ?>
      <p class="empty">등록된 문항이 없습니다.</p>
    <?php endif; ?>
    <?php $n = 1; foreach ($items as $it):
          $iid = (int) $it['id'];
          $pa = isset($prevAns[$iid]) ? $prevAns[$iid] : null;
          render_checklist_question('self', $it, $n++, $pa, '구체적인 사례를 적어주세요');
    endforeach; ?>
    <div class="learn-nav">
      <a class="btn gray" href="index.php?p=learn_step&sid=<?= $sid ?>&step=3">이전학습</a>
      <button type="submit" class="btn">제출하기</button>
    </div>
  </form>

<?php elseif ($step === 5):
  $note = one('SELECT content FROM practice_note WHERE member_id = ? AND situation_id = ?', array($mid, $sid)); ?>
  <h3 class="page-title">나의 다짐</h3>
  <p class="hint">앞으로 똑같은 상황이 오면 이렇게 대응하고 싶은 내용을 적어 주세요.</p>
  <form method="post" class="panel">
    <?= csrf_field() ?><input type="hidden" name="act" value="practice">
    <textarea name="practice" rows="8"><?= e($note ? $note['content'] : '') ?></textarea>
    <div class="learn-nav">
      <a class="btn gray" href="index.php?p=learn_step&sid=<?= $sid ?>&step=4">이전학습</a>
      <button type="submit" class="btn">제출하기</button>
    </div>
  </form>

<?php elseif ($step === 6):
  $cards = all('SELECT * FROM situation_action_card WHERE situation_id = ? AND is_active = 1 ORDER BY sort_order, id', array($sid));
  $selected = array();
  foreach (all('SELECT card_id FROM situation_action_card_select WHERE member_id = ? AND situation_id = ?', array($mid, $sid)) as $r) {
      $selected[(int) $r['card_id']] = true;
  }
  $actionHtml = trim((string) (isset($sit['expert_html']) ? $sit['expert_html'] : '')); ?>
  <h3 class="page-title">Action Card</h3>
  <?php if ($actionHtml !== ''): ?>
    <div class="expert-text"><?= $actionHtml ?></div>
  <?php endif; ?>
  <p class="hint">앞으로 실천하고 싶은 Action Card를 선택해 주세요. (복수 선택 가능)</p>
  <form method="post" class="panel">
    <?= csrf_field() ?><input type="hidden" name="act" value="action_card">
    <?php if (!$cards): ?>
      <p class="empty">등록된 Action Card가 없습니다.</p>
    <?php endif; ?>
    <div class="action-cards">
    <?php foreach ($cards as $c):
        $checked = isset($selected[(int) $c['id']]) ? 'checked' : ''; ?>
      <label class="action-card">
        <input type="checkbox" name="card_id[]" value="<?= (int) $c['id'] ?>" <?= $checked ?>>
        <?php if ($c['card_type'] === 'image' && $c['image']): ?>
          <img src="<?= e(action_card_image_src($c['image'])) ?>" alt="">
        <?php endif; ?>
        <span class="ac-tt"><?= e($c['title']) ?></span>
        <?php if (trim((string) $c['content']) !== ''): ?>
          <span class="ac-tx"><?= nl2br(e($c['content'])) ?></span>
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
    </div>
    <div class="learn-nav">
      <a class="btn gray" href="index.php?p=learn_step&sid=<?= $sid ?>&step=5">이전학습</a>
      <button type="submit" class="btn">제출하기</button>
    </div>
  </form>

<?php else: /* step 7 총평 */
  $sub = latest_situation_submission($mid, $sid, 'situation');

  /* 아이의 마음읽기 - '예'로 답변한 문항만 표출 */
  $mindReadingYes = array();
  if ($sub) {
      $rows = all("SELECT it.question, op.label FROM checklist_response r
                   JOIN situation_checklist_item it ON it.id = r.item_id
                   LEFT JOIN checklist_item_option op ON op.id = r.option_id
                   WHERE r.submission_id = ?", array((int) $sub['id']));
      foreach ($rows as $r) {
          if (trim((string) $r['label']) === '예') { $mindReadingYes[] = $r['question']; }
      }
  }

  /* 나의 마음 알아보기 - '예'로 답변한 문항만 표출 */
  $selfSub = latest_situation_submission($mid, $sid, 'self');
  $selfYes = array();
  if ($selfSub) {
      $rows = all("SELECT it.question, op.label FROM checklist_response r
                   JOIN situation_selfcheck_item it ON it.id = r.item_id
                   LEFT JOIN checklist_item_option op ON op.id = r.option_id
                   WHERE r.submission_id = ?", array((int) $selfSub['id']));
      foreach ($rows as $r) {
          if (trim((string) $r['label']) === '예') { $selfYes[] = $r['question']; }
      }
  }

  /* 나의 다짐 */
  $practice = one('SELECT content FROM practice_note WHERE member_id = ? AND situation_id = ?', array($mid, $sid));

  /* 선택한 Action Card */
  $pickedCards = all("SELECT c.title FROM situation_action_card_select sel
                       JOIN situation_action_card c ON c.id = sel.card_id
                       WHERE sel.member_id = ? AND sel.situation_id = ?
                       ORDER BY c.sort_order, c.id", array($mid, $sid));

  $mr_html = $mindReadingYes ? e(implode(', ', $mindReadingYes)) : '<span class="muted">해당 없음</span>';
  $sc_html = $selfYes ? e(implode(', ', $selfYes)) : '<span class="muted">해당 없음</span>';
  $pr_html = ($practice && trim((string) $practice['content']) !== '')
      ? nl2br(e($practice['content'])) : '<span class="muted">입력 내용 없음</span>';
  $ac_html = $pickedCards ? e(implode(', ', array_column($pickedCards, 'title'))) : '<span class="muted">선택 항목 없음</span>';

  $earned = (int) col('SELECT COUNT(*) FROM member_badge WHERE member_id = ?', array($mid));
  $meetings = all('SELECT m.* FROM offline_meeting m
                   JOIN meeting_situation_link l ON l.meeting_id = m.id
                   WHERE l.situation_id = ? AND m.is_published = 1
                   ORDER BY m.sort_order, m.id LIMIT 6', array($sid));
?>
  <h3 class="page-title" style="margin-bottom:2px">평가 및 실천</h3>
  <div class="result-box review-box" style="margin-top:4px">
    <div class="review-row"><span class="review-lb">아이의 마음읽기</span><span class="review-vv"><?= $mr_html ?></span></div>
    <div class="review-row"><span class="review-lb">나의 마음 알아보기</span><span class="review-vv"><?= $sc_html ?></span></div>
    <div class="review-row"><span class="review-lb">나의 다짐</span><span class="review-vv"><?= $pr_html ?></span></div>
    <div class="review-row"><span class="review-lb">Action Card</span><span class="review-vv"><?= $ac_html ?></span></div>
  </div>

  <h3 class="page-title mt" style="margin-bottom:4px;line-height:1.25">보유 뱃지</h3>
  <p class="hint" style="margin:0">학습을 끝까지 잘 마친 부모님께 드리는 뱃지입니다. 이번 학습 완료로 1개를 획득했어요. (동일 콘텐츠는 1개)</p>
  <?php
  /* 학습 완주 뱃지: 리본 달린 메달 + 별. 색은 CSS(earned=금색 / locked=회색)에서 제어 */
  $badgeSvg =
      '<svg class="badge-ic" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
    .   '<path class="ribbon" d="M8.4 13.2 5.7 21.6 12 18.6l6.3 3-2.7-8.4"/>'
    .   '<circle class="ring" cx="12" cy="9" r="7"/>'
    .   '<path class="star" d="M12 4.7 13.08 7.81 16.38 7.88 13.75 9.87 14.7 13.02 12 11.14 9.3 13.02 10.25 9.87 7.63 7.88 10.92 7.81Z"/>'
    . '</svg>';
  $slots = max(9, $earned);
  ?>
  <div class="badges">
    <?php for ($b = 0; $b < $slots; $b++): $has = $b < $earned; ?>
      <div class="badge <?= $has ? 'earned' : 'locked' ?>" title="<?= $has ? '획득한 학습 완주 뱃지' : '아직 획득하지 않은 뱃지' ?>">
        <?= $badgeSvg ?>
      </div>
    <?php endfor; ?>
  </div>

  <h3 class="page-title mt">연관된 오프라인 모임</h3>
  <div class="cards">
    <?php if (!$meetings): ?>
      <div class="empty" style="grid-column:1/-1">연관된 모임이 없습니다.</div>
    <?php endif; ?>
    <?php foreach ($meetings as $m): ?>
      <div class="card">
        <div class="thumb"><?php if ($m['image']): ?><img src="<?= e($m['image']) ?>" alt=""><?php endif; ?></div>
        <div class="body">
          <div class="tt"><?= e($m['title']) ?></div>
          <div class="meta">
            <span><?= $m['meet_at'] ? e(date('n월 j일 H:i', strtotime($m['meet_at']))) : '' ?></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="learn-nav">
    <a class="btn gray" href="index.php?p=learn_step&sid=<?= $sid ?>&step=6">이전학습</a>
    <a class="btn" href="index.php?p=learn">학습하기 목록</a>
  </div>
<?php endif; ?>
  </div><!-- /.learn-body -->
</div><!-- /.learn-step-layout -->
</div><!-- /.wrap -->
<?php
render_footer(array('bare' => true)); /* 학습 화면은 풀높이 레이아웃 — 푸터 제외 */
