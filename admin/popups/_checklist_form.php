<?php
/* 공용 체크리스트 문항 입력 폼 (상황별 / 부모양육태도 공용)
 * 호출 전 정의 필요:
 *   $cf_title       화면 제목
 *   $cf_mode        'situation' | 'parenting'  (checklist_item_option/score 의 scope 값과 동일)
 *   $cf_item_table  문항 테이블명
 *   $cf_score_table 배점 테이블명 (척도용, 문항 단위)
 *   $cf_item_fk     배점 테이블의 문항 FK 컬럼명
 *
 * 답변유형(answer_type): scale(척도) | choice(다지선다/OX) | essay(서술형)
 *  - scale : scale_steps + scale_template_id(선택) + 문항단위 %가중치($cf_score_table)
 *  - choice: display_style(list|ox) + 보기(checklist_item_option, 보기별 %가중치 checklist_option_score)
 *  - essay : 배점 없음
 */
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$UPLOAD_DIR  = __DIR__ . '/../uploads';
$ALLOWED_IMG = array('jpg', 'jpeg', 'png', 'gif', 'webp');
$MAX_IMG     = 3 * 1024 * 1024;

$attitudes  = all('SELECT id, name FROM parenting_attitude WHERE is_active = 1 ORDER BY sort_order, id');
$templates  = all('SELECT id, name, steps, labels FROM scale_label_template ORDER BY sort_order, id');

/* $cf_mode 는 checklist_item_option/checklist_option_score 의 scope 값(situation|self|parenting).
 * situation_id 를 요구하는지는 별도 플래그로 판단 (situation, self 모두 situation_id 필요) */
if (!isset($cf_situation_scoped)) { $cf_situation_scoped = ($cf_mode === 'situation'); }

$id      = int_or_zero(get('id'));
$editing = $id ? one("SELECT * FROM {$cf_item_table} WHERE id = ?", array($id)) : null;

$sit = null;
$sid = 0;
if ($cf_situation_scoped) {
    $sid = $editing ? (int) $editing['situation_id'] : int_or_zero(get('sid'));
    $sit = one('SELECT s.title, sc.name AS cat, lc.name AS lc
                FROM situation s
                JOIN situation_category sc ON sc.id = s.situation_category_id
                JOIN life_cycle lc ON lc.id = sc.life_cycle_id
                WHERE s.id = ?', array($sid));
    if (!$sit) {
        popup_header($cf_title);
        echo '<div class="flash flash-err">대상 상황을 찾을 수 없습니다.</div>';
        popup_footer();
        return;
    }
}

/* ---- 폼 표시용 기본값 ---- */
$question   = $editing ? $editing['question'] : '';
$answerType = $editing ? $editing['answer_type'] : 'scale';
$scaleSteps = $editing ? (int) $editing['scale_steps'] : 5;
if ($scaleSteps < 2) { $scaleSteps = 5; }
$scaleTplId = $editing ? (int) $editing['scale_template_id'] : 0;
$dispStyle  = ($editing && $editing['display_style'] === 'ox') ? 'ox' : 'list';

$vals = array();
if ($editing) {
    foreach (all("SELECT parenting_attitude_id AS aid, percentage AS p
                  FROM {$cf_score_table} WHERE {$cf_item_fk} = ?", array($id)) as $r) {
        $vals[(int) $r['aid']] = (int) $r['p'];
    }
}

$options = array();
if ($editing && $answerType === 'choice') {
    $opRows = all('SELECT * FROM checklist_item_option WHERE scope = ? AND item_id = ? ORDER BY sort_order, id',
                  array($cf_mode, $id));
    foreach ($opRows as $op) {
        $ow = array();
        foreach (all('SELECT parenting_attitude_id AS aid, percentage AS p
                      FROM checklist_option_score WHERE option_id = ?', array((int) $op['id'])) as $r) {
            $ow[(int) $r['aid']] = (int) $r['p'];
        }
        $options[] = array('label' => $op['label'], 'image' => $op['image_url'], 'w' => $ow);
    }
}
if (!$options) {
    $options = array(array('label' => '', 'image' => null, 'w' => array()),
                      array('label' => '', 'image' => null, 'w' => array()));
}

$err = '';

/* ---------------------------------------------------------------- */
if (is_post()) {
    csrf_check();
    $question   = trim(post('question'));
    $answerType = in_array(post('answer_type'), array('scale', 'choice', 'essay'), true) ? post('answer_type') : 'scale';
    $scaleSteps = clamp_int(post('scale_steps', 5), 2, 20);
    $scaleTplId = int_or_zero(post('scale_template_id'));
    $dispStyle  = (post('display_style') === 'ox') ? 'ox' : 'list';

    $vals = array();
    $sum  = 0; $any = false;
    foreach ($attitudes as $a) {
        $raw = trim(post('p_' . $a['id'], ''));
        $v = ($raw === '') ? 0 : (int) $raw;
        if ($v < 0)   { $v = 0; }
        if ($v > 100) { $v = 100; }
        $vals[(int) $a['id']] = $v;
        $sum += $v;
        if ($v > 0) { $any = true; }
    }

    /* ---- 보기(다지선다/OX) 파싱 ---- */
    $rawLabels = isset($_POST['opt_label']) && is_array($_POST['opt_label']) ? $_POST['opt_label'] : array();
    $rawOldImg = isset($_POST['opt_oldimage']) && is_array($_POST['opt_oldimage']) ? $_POST['opt_oldimage'] : array();
    $rawDelImg = isset($_POST['opt_delimg']) && is_array($_POST['opt_delimg']) ? $_POST['opt_delimg'] : array();
    $rawW      = isset($_POST['opt_w']) && is_array($_POST['opt_w']) ? $_POST['opt_w'] : array();
    $filesImg  = isset($_FILES['opt_image']) ? $_FILES['opt_image'] : array();

    $options = array();
    $optErr  = '';
    $unlinkAfterCommit = array(); /* 검증 실패 시 파일을 지우지 않도록 커밋 성공 후에만 삭제 */
    foreach ($rawLabels as $idx => $labelRaw) {
        $label = trim((string) $labelRaw);
        $oldImg = isset($rawOldImg[$idx]) ? trim((string) $rawOldImg[$idx]) : '';
        $delImg = !empty($rawDelImg[$idx]);

        $newImg = null;
        if (isset($filesImg['error'][$idx]) && $filesImg['error'][$idx] !== UPLOAD_ERR_NO_FILE) {
            if ($filesImg['error'][$idx] !== UPLOAD_ERR_OK) {
                $optErr = '보기 이미지 업로드에 실패했습니다. (오류코드 ' . (int) $filesImg['error'][$idx] . ')';
            } else {
                $ext = strtolower(pathinfo($filesImg['name'][$idx], PATHINFO_EXTENSION));
                if (!in_array($ext, $ALLOWED_IMG, true)) {
                    $optErr = '보기 이미지는 ' . implode(', ', $ALLOWED_IMG) . ' 형식만 허용됩니다.';
                } elseif ($filesImg['size'][$idx] > $MAX_IMG) {
                    $optErr = '보기 이미지가 너무 큽니다. (최대 3MB)';
                } else {
                    $fname = 'opt_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    if (is_dir($UPLOAD_DIR) && is_writable($UPLOAD_DIR)
                        && move_uploaded_file($filesImg['tmp_name'][$idx], $UPLOAD_DIR . '/' . $fname)) {
                        $newImg = 'uploads/' . $fname;
                    } else {
                        $optErr = '보기 이미지 저장에 실패했습니다.';
                    }
                }
            }
        }

        if ($label === '' && $oldImg === '' && $newImg === null) { continue; } /* 완전히 빈 행은 무시 */

        $image = $newImg !== null ? $newImg : ($delImg ? null : ($oldImg !== '' ? $oldImg : null));

        $ow = array(); $osum = 0; $oany = false;
        if (isset($rawW[$idx]) && is_array($rawW[$idx])) {
            foreach ($attitudes as $a) {
                $raw = isset($rawW[$idx][$a['id']]) ? trim((string) $rawW[$idx][$a['id']]) : '';
                $v = ($raw === '') ? 0 : (int) $raw;
                if ($v < 0)   { $v = 0; }
                if ($v > 100) { $v = 100; }
                $ow[(int) $a['id']] = $v;
                $osum += $v;
                if ($v > 0) { $oany = true; }
            }
        }
        if ($optErr === '' && $oany && $osum !== 100) {
            $optErr = '"' . $label . '" 보기의 양육태도 배점 합계가 100%가 되어야 합니다. (현재 ' . $osum . '%)';
        }

        $options[] = array('label' => $label, 'image' => $image, 'w' => $ow);
        /* 이전 이미지가 교체/삭제되었다면 저장 성공 후 정리할 목록에 추가(검증 실패 시엔 지우지 않음) */
        if ($oldImg !== '' && $oldImg !== $image) {
            $unlinkAfterCommit[] = $oldImg;
        }
    }
    if (!$options) {
        $options = array(array('label' => '', 'image' => null, 'w' => array()),
                          array('label' => '', 'image' => null, 'w' => array()));
    }

    /* ---- 검증 ---- */
    if ($question === '') {
        $err = '문항을 입력하세요.';
    } elseif ($answerType === 'scale') {
        if ($any && $sum !== 100) {
            $err = '양육태도 배점의 합계가 100%가 되어야 합니다. (현재 ' . $sum . '%)  ·  모두 비워 두면 초안으로 저장됩니다.';
        } elseif ($scaleTplId) {
            $tpl = null;
            foreach ($templates as $t) { if ((int) $t['id'] === $scaleTplId) { $tpl = $t; break; } }
            if (!$tpl) {
                $err = '선택한 라벨 템플릿을 찾을 수 없습니다.';
            } elseif ((int) $tpl['steps'] !== $scaleSteps) {
                $err = '선택한 라벨 템플릿의 단계수(' . (int) $tpl['steps'] . ')가 척도 단계수(' . $scaleSteps . ')와 일치하지 않습니다.';
            }
        }
    } elseif ($answerType === 'choice') {
        $filled = array_values(array_filter($options, function ($o) { return $o['label'] !== ''; }));
        if (count($filled) < 2) {
            $err = '다지선다/OX는 보기(문항)가 최소 2개 필요합니다.';
        } elseif ($dispStyle === 'ox' && count($filled) !== 2) {
            $err = 'OX 형은 보기가 정확히 2개여야 합니다.';
        } elseif ($optErr !== '') {
            $err = $optErr;
        }
    }

    if ($err === '') {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            if ($editing) {
                q("UPDATE {$cf_item_table}
                   SET question = ?, answer_type = ?, scale_steps = ?, scale_template_id = ?, display_style = ?
                   WHERE id = ?",
                  array($question, $answerType, $scaleSteps, $scaleTplId ? $scaleTplId : null, $dispStyle, $id));
                $iid = $id;
            } elseif ($cf_situation_scoped) {
                q("INSERT INTO {$cf_item_table}
                   (situation_id, question, answer_type, scale_steps, scale_template_id, display_style, sort_order)
                   VALUES (?, ?, ?, ?, ?, ?, 0)",
                  array($sid, $question, $answerType, $scaleSteps, $scaleTplId ? $scaleTplId : null, $dispStyle));
                $iid = last_id();
            } else {
                q("INSERT INTO {$cf_item_table}
                   (question, answer_type, scale_steps, scale_template_id, display_style, sort_order)
                   VALUES (?, ?, ?, ?, ?, 0)",
                  array($question, $answerType, $scaleSteps, $scaleTplId ? $scaleTplId : null, $dispStyle));
                $iid = last_id();
            }

            /* 문항단위 배점(척도) — 유형이 바뀌었을 경우를 위해 항상 초기화 후 필요시 재삽입 */
            q("DELETE FROM {$cf_score_table} WHERE {$cf_item_fk} = ?", array($iid));
            if ($answerType === 'scale') {
                foreach ($vals as $aid => $v) {
                    if ($v > 0) {
                        q("INSERT INTO {$cf_score_table} ({$cf_item_fk}, parenting_attitude_id, percentage) VALUES (?, ?, ?)",
                          array($iid, $aid, $v));
                    }
                }
            }

            /* 보기(다지선다/OX) — 항상 초기화 후 필요시 재삽입 */
            $oldOptIds = array();
            foreach (all('SELECT id FROM checklist_item_option WHERE scope = ? AND item_id = ?',
                         array($cf_mode, $iid)) as $r) { $oldOptIds[] = (int) $r['id']; }
            if ($oldOptIds) {
                $ph = implode(',', array_fill(0, count($oldOptIds), '?'));
                q("DELETE FROM checklist_option_score WHERE option_id IN ($ph)", $oldOptIds);
                q("DELETE FROM checklist_item_option WHERE id IN ($ph)", $oldOptIds);
            }
            if ($answerType === 'choice') {
                $ord = 0;
                foreach ($options as $o) {
                    if ($o['label'] === '') { continue; }
                    $ord++;
                    q('INSERT INTO checklist_item_option (scope, item_id, label, image_url, sort_order)
                       VALUES (?, ?, ?, ?, ?)',
                      array($cf_mode, $iid, $o['label'], $o['image'], $ord));
                    $oid = last_id();
                    foreach ($o['w'] as $aid => $v) {
                        if ($v > 0) {
                            q('INSERT INTO checklist_option_score (option_id, parenting_attitude_id, percentage) VALUES (?, ?, ?)',
                              array($oid, $aid, $v));
                        }
                    }
                }
            }

            $pdo->commit();
            foreach ($unlinkAfterCommit as $oldImg) {
                $fp = $UPLOAD_DIR . '/' . basename($oldImg);
                if (is_file($fp)) { @unlink($fp); }
            }
            popup_done();
        } catch (Exception $ex) {
            $pdo->rollBack();
            $err = '저장 실패: ' . $ex->getMessage();
        }
    }
}

popup_header($cf_title . ($editing ? ' 수정' : ' 추가'));
if ($cf_situation_scoped) {
    echo '<p class="muted small">' . e($sit['lc']) . ' &gt; ' . e($sit['cat']) . '</p>';
    echo '<p class="sit-title">' . e($sit['title']) . '</p>';
}
if ($err !== '') { echo '<div class="flash flash-err">' . e($err) . '</div>'; }
?>
<form method="post" class="pform" enctype="multipart/form-data" id="cfForm">
    <?= csrf_field() ?>
    <label>문항</label>
    <textarea name="question" rows="3" required autofocus><?= e($question) ?></textarea>

    <label>답변유형</label>
    <div class="atype-tabs">
        <label class="atype-opt"><input type="radio" name="answer_type" value="scale" <?= $answerType === 'scale' ? 'checked' : '' ?>> 척도(단계)</label>
        <label class="atype-opt"><input type="radio" name="answer_type" value="choice" <?= $answerType === 'choice' ? 'checked' : '' ?>> 다지선다 / OX</label>
        <label class="atype-opt"><input type="radio" name="answer_type" value="essay" <?= $answerType === 'essay' ? 'checked' : '' ?>> 서술형</label>
    </div>

    <div class="atype-body" data-atype="scale">
        <label>척도 단계수</label>
        <input type="number" name="scale_steps" min="2" max="20" value="<?= (int) $scaleSteps ?>" style="max-width:120px">

        <label>단계별 라벨 템플릿 <span class="hint" style="margin:0">(사용자 화면에 각 점 아래로 표시됩니다. 선택 안 하면 양끝 라벨만 표시)</span></label>
        <select name="scale_template_id">
            <option value="0">사용 안 함 (양끝 라벨만)</option>
            <?php foreach ($templates as $t): ?>
                <option value="<?= (int) $t['id'] ?>" <?= $scaleTplId === (int) $t['id'] ? 'selected' : '' ?>>
                    <?= e($t['name']) ?> (<?= (int) $t['steps'] ?>단계 · <?= e(str_replace('|', ' / ', $t['labels'])) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <p class="hint">템플릿 관리는 <a href="index.php?p=scale_templates" target="_blank">척도 라벨 템플릿 관리</a>에서 할 수 있습니다.</p>

        <div class="pct-grid">
            <?php foreach ($attitudes as $a):
                $cur = (isset($vals[(int) $a['id']]) && $vals[(int) $a['id']] > 0) ? $vals[(int) $a['id']] : ''; ?>
                <label class="pct-label"><?= e($a['name']) ?></label>
                <div class="pct-in"><input type="number" name="p_<?= (int) $a['id'] ?>" min="0" max="100" value="<?= e($cur) ?>"> %</div>
            <?php endforeach; ?>
        </div>
        <p class="hint">응답 시 각 양육태도에 부여할 비중(%)입니다. 합은 100이어야 하며, 모두 비우면 초안으로 저장됩니다.</p>
    </div>

    <div class="atype-body" data-atype="choice">
        <label>표시방식</label>
        <div class="atype-tabs">
            <label class="atype-opt"><input type="radio" name="display_style" value="list" <?= $dispStyle === 'list' ? 'checked' : '' ?>> 목록형(세로 보기 목록)</label>
            <label class="atype-opt"><input type="radio" name="display_style" value="ox" <?= $dispStyle === 'ox' ? 'checked' : '' ?>> OX형(큰 버튼 2개, 이미지 지원)</label>
        </div>

        <label>보기 <span class="hint" style="margin:0">(각 보기의 이미지는 선택사항입니다. 양육태도 배점 합은 보기별로 100 또는 전체 0)</span></label>
        <div id="optRows"></div>
        <button type="button" id="optAddBtn" class="mini">+ 보기 추가</button>
    </div>

    <div class="atype-body" data-atype="essay">
        <p class="hint">서술형은 채점(배점)에 반영되지 않고 답변 내용만 저장됩니다.</p>
    </div>

    <div class="pbtns">
        <button type="button" onclick="window.close()">닫기</button>
        <button type="submit" class="primary">저장</button>
    </div>
</form>

<template id="optRowTpl">
    <div class="opt-row">
        <div class="opt-row-top">
            <input type="text" class="opt-label-in" placeholder="보기 문구 (예: 예 / 아니오)">
            <button type="button" class="mini danger opt-del">삭제</button>
        </div>
        <div class="opt-row-mid">
            <div class="opt-img-cur"></div>
            <input type="file" class="opt-image-in" accept="image/*">
            <label class="opt-delimg-lb" hidden><input type="checkbox" class="opt-delimg-in"> 이미지 삭제</label>
        </div>
        <div class="opt-w-grid"></div>
    </div>
</template>

<script>
(function () {
    var ATTITUDES = <?= json_encode(array_map(function ($a) { return array('id' => (int) $a['id'], 'name' => $a['name']); }, $attitudes)) ?>;
    var EXISTING  = <?= json_encode($options) ?>;
    var rowsEl = document.getElementById('optRows');
    var tpl    = document.getElementById('optRowTpl');
    var idx    = 0;

    function buildWeightGrid(container, rowIdx, weights) {
        container.innerHTML = '';
        ATTITUDES.forEach(function (a) {
            var lb = document.createElement('label');
            lb.className = 'pct-label';
            lb.textContent = a.name;
            var wrap = document.createElement('div');
            wrap.className = 'pct-in';
            var inp = document.createElement('input');
            inp.type = 'number'; inp.min = 0; inp.max = 100;
            inp.name = 'opt_w[' + rowIdx + '][' + a.id + ']';
            var v = (weights && weights[a.id]) ? weights[a.id] : (weights && weights[String(a.id)] ? weights[String(a.id)] : '');
            if (v) { inp.value = v; }
            wrap.appendChild(inp);
            wrap.appendChild(document.createTextNode(' %'));
            container.appendChild(lb);
            container.appendChild(wrap);
        });
    }

    function addRow(data) {
        var rowIdx = idx++;
        var node = tpl.content.firstElementChild.cloneNode(true);

        var labelIn = node.querySelector('.opt-label-in');
        labelIn.name = 'opt_label[' + rowIdx + ']';
        if (data && data.label) { labelIn.value = data.label; }

        var imgCur = node.querySelector('.opt-img-cur');
        var oldImgIn = document.createElement('input');
        oldImgIn.type = 'hidden';
        oldImgIn.name = 'opt_oldimage[' + rowIdx + ']';
        oldImgIn.value = (data && data.image) ? data.image : '';
        node.appendChild(oldImgIn);
        if (data && data.image) {
            var img = document.createElement('img');
            img.src = data.image; img.alt = '';
            imgCur.appendChild(img);
            var delLb = node.querySelector('.opt-delimg-lb');
            delLb.hidden = false;
            delLb.querySelector('.opt-delimg-in').name = 'opt_delimg[' + rowIdx + ']';
        }

        var imageIn = node.querySelector('.opt-image-in');
        imageIn.name = 'opt_image[' + rowIdx + ']';

        node.querySelector('.opt-del').addEventListener('click', function () { node.remove(); });

        buildWeightGrid(node.querySelector('.opt-w-grid'), rowIdx, data ? data.w : null);
        rowsEl.appendChild(node);
    }

    document.getElementById('optAddBtn').addEventListener('click', function () { addRow(null); });
    EXISTING.forEach(function (o) { addRow(o); });

    /* 답변유형 탭 전환 */
    var form = document.getElementById('cfForm');
    function syncType() {
        var t = form.querySelector('input[name=answer_type]:checked').value;
        document.querySelectorAll('.atype-body').forEach(function (el) {
            el.hidden = el.getAttribute('data-atype') !== t;
        });
    }
    form.querySelectorAll('input[name=answer_type]').forEach(function (r) { r.addEventListener('change', syncType); });
    syncType();
})();
</script>
<?php popup_footer(); ?>
