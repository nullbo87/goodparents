<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$id  = int_or_zero(get('id'));
$row = $id ? one('SELECT s.*, sc.life_cycle_id
                  FROM situation s JOIN situation_category sc ON sc.id = s.situation_category_id
                  WHERE s.id = ?', array($id)) : null;

$lcs  = all('SELECT id, name FROM life_cycle ORDER BY sort_order, id');
$cats = all('SELECT id, life_cycle_id, name, COALESCE(description, \'\') AS description
             FROM situation_category ORDER BY sort_order, name');

$lc_id  = $row ? (int) $row['life_cycle_id'] : int_or_zero(get('lc'));
$cat_id = $row ? (int) $row['situation_category_id'] : 0;
$title  = $row ? $row['title'] : '';
$desc   = $row ? (string) $row['description'] : '';
$err    = '';

if (is_post()) {
    csrf_check();
    $lc_id  = int_or_zero(post('life_cycle_id'));
    $cat_id = int_or_zero(post('situation_category_id'));
    $title  = trim(post('title'));
    $desc   = trim(post('description'));

    $cat_ok = $cat_id > 0 && one('SELECT id FROM situation_category WHERE id = ?', array($cat_id));
    if (!$cat_ok) {
        $err = '상황분류를 선택하세요.';
    } elseif ($title === '') {
        $err = '상황을 입력하세요.';
    }
    if ($err === '') {
        if ($id) {
            q('UPDATE situation SET situation_category_id = ?, title = ?, description = ? WHERE id = ?',
              array($cat_id, $title, nz($desc), $id));
        } else {
            q('INSERT INTO situation (situation_category_id, title, description, sort_order) VALUES (?, ?, ?, 0)',
              array($cat_id, $title, nz($desc)));
        }
        popup_done();
    }
}

if ($lc_id === 0 && $lcs) { $lc_id = (int) $lcs[0]['id']; }

popup_header($id ? '상황 수정' : '상황 추가');
if ($err !== '') { echo '<div class="flash flash-err">' . e($err) . '</div>'; }
?>
<form method="post" class="pform">
    <?= csrf_field() ?>
    <label>생애주기</label>
    <select name="life_cycle_id" id="lcsel">
        <?php foreach ($lcs as $x): ?>
            <option value="<?= (int) $x['id'] ?>" <?= $lc_id === (int) $x['id'] ? 'selected' : '' ?>><?= e($x['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>상황분류</label>
    <select name="situation_category_id" id="catsel" required></select>
    <p class="hint" id="catdesc"></p>

    <label>상황</label>
    <input type="text" name="title" value="<?= e($title) ?>" required placeholder="예: 친구와 놀다가 싸우는 경우">

    <label>설명 <span class="muted">(선택)</span></label>
    <textarea name="description" rows="3"><?= e($desc) ?></textarea>

    <div class="pbtns">
        <button type="button" onclick="window.close()">닫기</button>
        <button type="submit" class="primary">저장</button>
    </div>
</form>
<script>
var CATS = <?= json_encode($cats, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
var PRESET_CAT = "<?= (int) $cat_id ?>";
function showDesc() {
    var cs = document.getElementById('catsel');
    var o  = cs.options[cs.selectedIndex];
    var d  = o ? (o.getAttribute('data-desc') || '') : '';
    document.getElementById('catdesc').textContent = d ? ('설명 : ' + d) : '';
}
function refreshCats() {
    var lc  = document.getElementById('lcsel').value;
    var cs  = document.getElementById('catsel');
    var cur = cs.value || PRESET_CAT;
    cs.innerHTML = '';
    var list = CATS.filter(function (c) { return String(c.life_cycle_id) === String(lc); });
    if (!list.length) {
        var op = document.createElement('option');
        op.value = ''; op.textContent = '(등록된 상황분류 없음 - 먼저 상황분류를 추가하세요)';
        cs.appendChild(op);
    }
    list.forEach(function (c) {
        var op = document.createElement('option');
        op.value = c.id; op.textContent = c.name;
        op.setAttribute('data-desc', c.description || '');
        cs.appendChild(op);
    });
    if (cur) { cs.value = cur; }
    showDesc();
}
document.getElementById('lcsel').addEventListener('change', function () { PRESET_CAT = ''; refreshCats(); });
document.getElementById('catsel').addEventListener('change', showDesc);
refreshCats();
</script>
<?php popup_footer(); ?>
