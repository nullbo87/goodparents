<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$UPLOAD_DIR   = __DIR__ . '/../uploads';
$ALLOWED_VID  = array('mp4', 'm4v', 'webm', 'ogg', 'ogv', 'mov', 'avi', 'mkv');
$MAX_UPLOAD   = 20 * 1024 * 1024;

$sid = int_or_zero(get('id'));

/* 업로드 용량이 post_max_size 초과 시 $_POST/$_FILES 가 비어서 CSRF 오탐 → 먼저 처리 */
if (is_post() && !$_POST && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 0) {
    flash_set('업로드 용량이 서버 허용치(약 20MB)를 초과했습니다. 더 작은 파일을 쓰거나 URL로 등록하세요.', 'err');
    redirect('index.php?p=situation_view&id=' . $sid);
}

$sit = one('SELECT s.*, sc.name AS cat_name, COALESCE(sc.description, \'\') AS cat_desc,
            lc.name AS lc_name, lc.id AS lc_id
            FROM situation s
            JOIN situation_category sc ON sc.id = s.situation_category_id
            JOIN life_cycle lc ON lc.id = sc.life_cycle_id
            WHERE s.id = ?', array($sid));

if (!$sit) {
    render_header('상황상세정보', 'situations');
    echo '<p class="empty">상황을 찾을 수 없습니다.</p>';
    render_footer();
    return;
}

if (is_post()) {
    csrf_check();
    $act  = post('act');
    $kind = in_array(post('kind'), array('understand', 'solve'), true) ? post('kind') : 'understand';

    if ($act === 'del_item') {
        $delId = int_or_zero(post('item_id'));
        q("DELETE FROM checklist_item_option WHERE scope = 'situation' AND item_id = ?", array($delId));
        q('DELETE FROM situation_checklist_item WHERE id = ? AND situation_id = ?', array($delId, $sid));
        flash_set('체크리스트 문항을 삭제했습니다.');

    } elseif ($act === 'del_selfitem') {
        $delId = int_or_zero(post('item_id'));
        q("DELETE FROM checklist_item_option WHERE scope = 'self' AND item_id = ?", array($delId));
        q('DELETE FROM situation_selfcheck_item WHERE id = ? AND situation_id = ?', array($delId, $sid));
        flash_set('나의 마음 알아보기 문항을 삭제했습니다.');

    } elseif ($act === 'save_conti') {
        $conti_col = array('understand' => 'understand_conti', 'solve' => 'solve_conti');
        $colc = isset($conti_col[$kind]) ? $conti_col[$kind] : 'understand_conti';
        q("UPDATE situation SET {$colc} = ? WHERE id = ?", array(trim(post('conti')), $sid));
        flash_set('콘티를 저장했습니다.');

    } elseif ($act === 'save_solve_html') {
        q('UPDATE situation SET solve_html = ? WHERE id = ?', array(trim(post('solve_html')), $sid));
        flash_set('문제해결 조언 내용을 저장했습니다.');

    } elseif ($act === 'save_action_html') {
        q('UPDATE situation SET expert_html = ? WHERE id = ?', array(trim(post('expert_html')), $sid));
        flash_set('Action Card 안내글을 저장했습니다.');

    } elseif ($act === 'toggle_media') {
        $v = (post('in_use') === '1') ? 1 : 0;
        q('UPDATE situation_media SET in_use = ? WHERE id = ? AND situation_id = ?',
          array($v, int_or_zero(post('media_id')), $sid));
        flash_set('사용여부를 변경했습니다.');

    } elseif ($act === 'del_media') {
        $m = one('SELECT * FROM situation_media WHERE id = ? AND situation_id = ?',
                 array(int_or_zero(post('media_id')), $sid));
        if ($m) {
            q('DELETE FROM situation_media WHERE id = ?', array((int) $m['id']));
            if ($m['media_type'] === 'file' && strpos($m['url'], 'uploads/') === 0) {
                $fp = __DIR__ . '/../' . $m['url'];
                if (is_file($fp)) { @unlink($fp); }
            }
            flash_set('동영상을 삭제했습니다.');
        }

    } elseif ($act === 'add_media') {
        $mtype = (post('media_type') === 'file') ? 'file' : 'url';
        $ord   = (int) col('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM situation_media WHERE situation_id = ? AND kind = ?',
                           array($sid, $kind));

        if ($mtype === 'url') {
            $url = trim(post('url'));
            if ($url === '') {
                flash_set('URL을 입력하세요.', 'err');
            } else {
                q('INSERT INTO situation_media (situation_id, kind, media_type, in_use, url, sort_order)
                   VALUES (?, ?, \'url\', 1, ?, ?)', array($sid, $kind, $url, $ord));
                flash_set('동영상 URL을 추가했습니다.');
            }
        } else {
            $f = isset($_FILES['mediafile']) ? $_FILES['mediafile'] : null;
            if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
                flash_set('파일을 선택하세요.', 'err');
            } elseif ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
                flash_set('파일이 너무 큽니다. (최대 20MB)', 'err');
            } elseif ($f['error'] !== UPLOAD_ERR_OK) {
                flash_set('업로드에 실패했습니다. (오류코드 ' . (int) $f['error'] . ')', 'err');
            } else {
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $ALLOWED_VID, true)) {
                    flash_set('허용되지 않는 형식입니다. (' . implode(', ', $ALLOWED_VID) . ')', 'err');
                } elseif ($f['size'] > $MAX_UPLOAD) {
                    flash_set('파일이 너무 큽니다. (최대 20MB)', 'err');
                } elseif (!is_dir($UPLOAD_DIR) || !is_writable($UPLOAD_DIR)) {
                    flash_set('uploads 폴더에 쓸 수 없습니다. 관리자에게 문의하세요.', 'err');
                } else {
                    $fname  = $sid . '_' . $kind . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    if (move_uploaded_file($f['tmp_name'], $UPLOAD_DIR . '/' . $fname)) {
                        q('INSERT INTO situation_media (situation_id, kind, media_type, in_use, url, sort_order)
                           VALUES (?, ?, \'file\', 1, ?, ?)', array($sid, $kind, 'uploads/' . $fname, $ord));
                        flash_set('동영상 파일을 업로드했습니다.');
                    } else {
                        flash_set('파일 저장에 실패했습니다.', 'err');
                    }
                }
            }
        }

    } elseif ($act === 'toggle_card') {
        $v = (post('in_use') === '1') ? 1 : 0;
        q('UPDATE situation_action_card SET is_active = ? WHERE id = ? AND situation_id = ?',
          array($v, int_or_zero(post('card_id')), $sid));
        flash_set('Action Card 사용여부를 변경했습니다.');

    } elseif ($act === 'del_card') {
        $c = one('SELECT * FROM situation_action_card WHERE id = ? AND situation_id = ?',
                 array(int_or_zero(post('card_id')), $sid));
        if ($c) {
            q('DELETE FROM situation_action_card WHERE id = ?', array((int) $c['id']));
            if (!empty($c['image']) && strpos($c['image'], 'uploads/') === 0) {
                $fp = __DIR__ . '/../' . $c['image'];
                if (is_file($fp)) { @unlink($fp); }
            }
            flash_set('Action Card를 삭제했습니다.');
        }
    }
    redirect('index.php?p=situation_view&id=' . $sid);
}

/* ---- 체크리스트 데이터 (2.아이의 마음 읽기) ---- */
$attitudes = all('SELECT id, name FROM parenting_attitude WHERE is_active = 1 ORDER BY sort_order, id');
$items     = all('SELECT * FROM situation_checklist_item WHERE situation_id = ? ORDER BY id DESC', array($sid));
$scores = array();
if ($items) {
    $ids = array();
    foreach ($items as $it) { $ids[] = (int) $it['id']; }
    $ph = implode(',', array_fill(0, count($ids), '?'));
    foreach (all("SELECT situation_checklist_item_id AS iid, parenting_attitude_id AS aid, percentage AS p
                  FROM situation_checklist_score WHERE situation_checklist_item_id IN ($ph)", $ids) as $r) {
        $scores[(int) $r['iid']][(int) $r['aid']] = (int) $r['p'];
    }
}

/* ---- 체크리스트 데이터 (4.나의 마음 알아보기) ---- */
$selfItems = all('SELECT * FROM situation_selfcheck_item WHERE situation_id = ? ORDER BY id DESC', array($sid));
$selfScores = array();
if ($selfItems) {
    $ids = array();
    foreach ($selfItems as $it) { $ids[] = (int) $it['id']; }
    $ph = implode(',', array_fill(0, count($ids), '?'));
    foreach (all("SELECT situation_selfcheck_item_id AS iid, parenting_attitude_id AS aid, percentage AS p
                  FROM situation_selfcheck_score WHERE situation_selfcheck_item_id IN ($ph)", $ids) as $r) {
        $selfScores[(int) $r['iid']][(int) $r['aid']] = (int) $r['p'];
    }
}

/* ---- 동영상 데이터 ---- */
$media_understand = all("SELECT * FROM situation_media WHERE situation_id = ? AND kind = 'understand' ORDER BY sort_order, id", array($sid));
$media_solve      = all("SELECT * FROM situation_media WHERE situation_id = ? AND kind = 'solve' ORDER BY sort_order, id", array($sid));

/* ---- Action Card 데이터 (6.Action Card) ---- */
$action_cards = all('SELECT * FROM situation_action_card WHERE situation_id = ? ORDER BY sort_order, id', array($sid));

render_header('상황상세정보', 'situations');
?>
<h2 class="page-title">상황상세정보</h2>

<div class="detail">
    <div><span class="dl">생애주기</span><b><?= e($sit['lc_name']) ?></b></div>
    <div><span class="dl">상황분류</span><b><?= e($sit['cat_name']) ?></b>
        <span class="muted"><?= e($sit['cat_desc']) ?></span></div>
    <div><span class="dl">상황</span><b><?= e($sit['title']) ?></b>
        <a class="mini" href="popup.php?f=situation_add&id=<?= $sid ?>" onclick="return pop(this)">수정</a></div>
    <?php if (trim((string) $sit['description']) !== ''): ?>
    <div><span class="dl">설명</span><span><?= nl2br(e($sit['description'])) ?></span></div>
    <?php endif; ?>
</div>

<?php
$sid = (int) $sid;
$vs_num = '1'; $vs_kind = 'understand'; $vs_title = '상황동영상';
$vs_conti = isset($sit['understand_conti']) ? (string) $sit['understand_conti'] : '';
$vs_rows = $media_understand;
include __DIR__ . '/_video_section.php';
?>

<?php
$cl_num = '2'; $cl_title = '아이의 마음 읽기'; $cl_add_popup = 'situation_checklist_add'; $cl_del_act = 'del_item';
$cl_sid = $sid; $cl_items = $items; $cl_scores = $scores;
include __DIR__ . '/_checklist_list.php';
?>

<div class="mt"></div>
<?php
$vs_num = '3'; $vs_kind = 'solve'; $vs_title = '문제 해결 길라잡이';
$vs_conti = isset($sit['solve_conti']) ? (string) $sit['solve_conti'] : '';
$vs_rows = $media_solve;
include __DIR__ . '/_video_section.php';
?>

<div class="vbody" style="margin-top:12px">
    <div class="vlabel">문제해결 조언 (텍스트 &middot; 사용자 화면에 노출)</div>
    <form method="post" id="solveHtmlForm">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save_solve_html">
        <div id="solveEditor" class="quill-host"></div>
        <textarea name="solve_html" id="solveHtml" rows="8" style="width:100%"><?= e(isset($sit['solve_html']) ? $sit['solve_html'] : '') ?></textarea>
        <p class="hint" style="margin:6px 0 0">에디터가 뜨지 않으면 위 칸에 HTML 을 직접 입력할 수 있습니다. 콘티는 저장만 되고 사용자에게는 보이지 않습니다.</p>
        <div class="conti-bar"><button type="submit" class="btn">문제해결 조언 저장</button></div>
    </form>
</div>

<?php
$cl_num = '4'; $cl_title = '나의 마음 알아보기'; $cl_add_popup = 'situation_selfcheck_add'; $cl_del_act = 'del_selfitem';
$cl_sid = $sid; $cl_items = $selfItems; $cl_scores = $selfScores;
include __DIR__ . '/_checklist_list.php';
?>

<h3 class="vsec"><span class="vnum">5.</span> 나의 다짐</h3>
<p class="hint" style="margin-top:-6px">사용자가 학습 중 직접 작성하는 항목이라 관리자 화면이 없습니다.</p>

<div class="bar mt">
    <h3 class="vsec"><span class="vnum">6.</span> Action Card</h3>
    <a class="btn add" href="popup.php?f=situation_action_card_add&sid=<?= $sid ?>" onclick="return pop(this)">Action Card 추가</a>
</div>
<div class="vbody">
    <div class="vlabel">안내글 (텍스트 &middot; 카드 위에 사용자 화면에 노출)</div>
    <form method="post" id="actionHtmlForm">
        <?= csrf_field() ?>
        <input type="hidden" name="act" value="save_action_html">
        <div id="actionEditor" class="quill-host"></div>
        <textarea name="expert_html" id="actionHtml" rows="6" style="width:100%"><?= e(isset($sit['expert_html']) ? $sit['expert_html'] : '') ?></textarea>
        <div class="conti-bar"><button type="submit" class="btn">안내글 저장</button></div>
    </form>
</div>
<div class="vbody" style="margin-top:12px">
<div class="tablewrap">
<table class="grid">
    <thead><tr>
        <th class="w-no">No</th><th class="w-fmt">형태</th><th>제목</th><th>내용</th><th class="w-use">사용여부</th><th class="w-vops">관리</th>
    </tr></thead>
    <tbody>
    <?php if (!$action_cards): ?>
        <tr><td colspan="6" class="empty">등록된 카드가 없습니다.</td></tr>
    <?php endif; ?>
    <?php $no = count($action_cards); foreach ($action_cards as $c): ?>
        <tr>
            <td class="num"><?= $no-- ?></td>
            <td class="num"><?= $c['card_type'] === 'image' ? '이미지' : '텍스트' ?></td>
            <td class="ql"><?= e($c['title']) ?></td>
            <td class="ql"><?= e(mb_substr((string) $c['content'], 0, 40)) ?></td>
            <td class="num">
                <form method="post" class="usef">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="toggle_card">
                    <input type="hidden" name="card_id" value="<?= (int) $c['id'] ?>">
                    <input type="checkbox" name="in_use" value="1" <?= $c['is_active'] ? 'checked' : '' ?> onchange="this.form.submit()">
                </form>
            </td>
            <td class="ops">
                <a class="mini" href="popup.php?f=situation_action_card_add&id=<?= (int) $c['id'] ?>" onclick="return pop(this)">수정</a>
                <form method="post" onsubmit="return confirm('삭제할까요?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="del_card">
                    <input type="hidden" name="card_id" value="<?= (int) $c['id'] ?>">
                    <button class="mini danger" type="submit">삭제</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
</div>

<link rel="stylesheet" href="assets/quill/quill.snow.css?v=1">
<style>
/* Quill(밝은 테마)를 관리자 다크 톤에 맞춤 */
#solveEditor .ql-editor, #actionEditor .ql-editor { min-height: 180px; background: #12243a; color: var(--text, #e7ecf7); font-size: 14px; }
#solveEditor .ql-editor.ql-blank::before, #actionEditor .ql-editor.ql-blank::before { color: var(--text-mut, #74859f); font-style: normal; }
.ql-toolbar.ql-snow { border-color: var(--line, #27394f); border-radius: 10px 10px 0 0; background: #17293f; }
.ql-container.ql-snow { border-color: var(--line, #27394f); border-radius: 0 0 10px 10px; }
.ql-snow .ql-stroke { stroke: #bcc7e4; }
.ql-snow .ql-fill { fill: #bcc7e4; }
.ql-snow .ql-picker { color: #bcc7e4; }
.ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow .ql-toolbar button:hover .ql-stroke { stroke: #fff; }
.ql-snow .ql-picker-options { background: #17293f; border-color: var(--line, #27394f); }
</style>
<script src="assets/quill/quill.min.js?v=1"></script>
<script>
(function () {
    if (typeof Quill === 'undefined') { return; }   /* 로드 실패 시 textarea 그대로 사용 */
    function initQuill(taId, hostId, formId, placeholder) {
        var ta = document.getElementById(taId);
        var host = document.getElementById(hostId);
        var form = document.getElementById(formId);
        if (!ta || !host || !form) { return; }
        ta.style.display = 'none';
        var q = new Quill(host, {
            theme: 'snow',
            placeholder: placeholder,
            modules: { toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link'],
                ['clean']
            ] }
        });
        q.root.innerHTML = ta.value;
        form.addEventListener('submit', function () {
            var html = q.root.innerHTML;
            if (html === '<p><br></p>') { html = ''; }
            ta.value = html;
        });
    }
    initQuill('solveHtml', 'solveEditor', 'solveHtmlForm', '문제해결 조언 내용을 입력하세요');
    initQuill('actionHtml', 'actionEditor', 'actionHtmlForm', 'Action Card 위에 표시할 안내글을 입력하세요');
})();
</script>

<p class="mt"><a class="mini" href="index.php?p=situations&lc=<?= (int) $sit['lc_id'] ?>">&larr; 상황목록으로</a></p>
<?php render_footer(); ?>
