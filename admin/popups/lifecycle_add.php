<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$id  = int_or_zero(get('id'));
$row = $id ? one('SELECT * FROM life_cycle WHERE id = ?', array($id)) : null;

$name   = $row ? $row['name'] : '';
$sort   = $row ? (int) $row['sort_order']
               : (int) col('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM life_cycle');
$desc   = $row ? (string) $row['description'] : '';
$active = $row ? (int) $row['is_active'] : 1;
$err    = '';

if (is_post()) {
    csrf_check();
    $name   = trim(post('name'));
    $sort   = int_or_zero(post('sort_order'));
    $desc   = trim(post('description'));
    $active = post('is_active') === '1' ? 1 : 0;

    if ($name === '') {
        $err = '생애주기 명칭을 입력하세요.';
    } elseif (one('SELECT id FROM life_cycle WHERE name = ? AND id <> ?', array($name, $id))) {
        $err = '이미 존재하는 생애주기 명칭입니다.';
    }
    if ($err === '') {
        if ($id) {
            q('UPDATE life_cycle SET name = ?, sort_order = ?, description = ?, is_active = ? WHERE id = ?',
              array($name, $sort, nz($desc), $active, $id));
        } else {
            q('INSERT INTO life_cycle (name, sort_order, description, is_active) VALUES (?, ?, ?, ?)',
              array($name, $sort, nz($desc), $active));
        }
        popup_done();
    }
}

popup_header($id ? '생애주기 수정' : '생애주기 추가');
if ($err !== '') { echo '<div class="flash flash-err">' . e($err) . '</div>'; }
?>
<form method="post" class="pform">
    <?= csrf_field() ?>
    <label>생애주기 명칭</label>
    <input type="text" name="name" value="<?= e($name) ?>" required autofocus>

    <label>순서</label>
    <input type="number" name="sort_order" value="<?= e($sort) ?>">

    <label>설명</label>
    <textarea name="description" rows="4"><?= e($desc) ?></textarea>

    <label class="chk"><input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?>> 활성</label>

    <div class="pbtns">
        <button type="button" onclick="window.close()">닫기</button>
        <button type="submit" class="primary">저장</button>
    </div>
</form>
<?php popup_footer(); ?>
