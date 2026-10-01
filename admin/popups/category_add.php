<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$id  = int_or_zero(get('id'));
$row = $id ? one('SELECT * FROM situation_category WHERE id = ?', array($id)) : null;
$lcs = all('SELECT id, name FROM life_cycle ORDER BY sort_order, id');

$lc_id = $row ? (int) $row['life_cycle_id'] : int_or_zero(get('lc'));
$name  = $row ? $row['name'] : '';
$desc  = $row ? (string) $row['description'] : '';
$sort  = $row ? (int) $row['sort_order'] : 0;
$err   = '';

if (is_post()) {
    csrf_check();
    $lc_id = int_or_zero(post('life_cycle_id'));
    $name  = trim(post('name'));
    $desc  = trim(post('description'));
    $sort  = int_or_zero(post('sort_order'));

    if ($lc_id <= 0) {
        $err = '생애주기를 선택하세요.';
    } elseif ($name === '') {
        $err = '상황분류 명칭을 입력하세요.';
    } elseif (one('SELECT id FROM situation_category WHERE life_cycle_id = ? AND name = ? AND id <> ?',
                  array($lc_id, $name, $id))) {
        $err = '같은 생애주기에 동일한 상황분류가 이미 있습니다.';
    }
    if ($err === '') {
        if ($id) {
            q('UPDATE situation_category SET life_cycle_id = ?, name = ?, description = ?, sort_order = ? WHERE id = ?',
              array($lc_id, $name, nz($desc), $sort, $id));
        } else {
            q('INSERT INTO situation_category (life_cycle_id, name, description, sort_order) VALUES (?, ?, ?, ?)',
              array($lc_id, $name, nz($desc), $sort));
        }
        popup_done();
    }
}

popup_header($id ? '상황분류 수정' : '상황분류 추가');
if ($err !== '') { echo '<div class="flash flash-err">' . e($err) . '</div>'; }
?>
<form method="post" class="pform">
    <?= csrf_field() ?>
    <label>생애주기</label>
    <select name="life_cycle_id" required>
        <option value="">선택</option>
        <?php foreach ($lcs as $x): ?>
            <option value="<?= (int) $x['id'] ?>" <?= $lc_id === (int) $x['id'] ? 'selected' : '' ?>><?= e($x['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>상황분류 명칭</label>
    <input type="text" name="name" value="<?= e($name) ?>" required>

    <label>순서</label>
    <input type="number" name="sort_order" value="<?= e($sort) ?>">

    <label>설명</label>
    <textarea name="description" rows="5"><?= e($desc) ?></textarea>

    <div class="pbtns">
        <button type="button" onclick="window.close()">닫기</button>
        <button type="submit" class="primary">저장</button>
    </div>
</form>
<?php popup_footer(); ?>
