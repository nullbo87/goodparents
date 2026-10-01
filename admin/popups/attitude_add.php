<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$id  = int_or_zero(get('id'));
$row = $id ? one('SELECT * FROM parenting_attitude WHERE id = ?', array($id)) : null;

$name   = $row ? $row['name'] : '';
$desc   = $row ? (string) $row['description'] : '';
$sort   = $row ? (int) $row['sort_order']
               : (int) col('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM parenting_attitude');
$active = $row ? (int) $row['is_active'] : 1;
$err    = '';

if (is_post()) {
    csrf_check();
    $name   = trim(post('name'));
    $desc   = trim(post('description'));
    $sort   = int_or_zero(post('sort_order'));
    $active = post('is_active') === '1' ? 1 : 0;

    if ($name === '') {
        $err = '양육태도 명칭을 입력하세요.';
    } elseif (one('SELECT id FROM parenting_attitude WHERE name = ? AND id <> ?', array($name, $id))) {
        $err = '이미 존재하는 양육태도 명칭입니다.';
    }
    if ($err === '') {
        if ($id) {
            q('UPDATE parenting_attitude SET name = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?',
              array($name, nz($desc), $sort, $active, $id));
        } else {
            q('INSERT INTO parenting_attitude (name, description, sort_order, is_active) VALUES (?, ?, ?, ?)',
              array($name, nz($desc), $sort, $active));
        }
        popup_done();
    }
}

popup_header($id ? '양육태도 수정' : '양육태도 추가');
if ($err !== '') { echo '<div class="flash flash-err">' . e($err) . '</div>'; }
?>
<form method="post" class="pform">
    <?= csrf_field() ?>
    <label>양육태도 명칭 <span class="muted">(예: 성장지원형)</span></label>
    <input type="text" name="name" value="<?= e($name) ?>" required autofocus>

    <label>순서</label>
    <input type="number" name="sort_order" value="<?= e($sort) ?>">

    <label>설명</label>
    <textarea name="description" rows="5"><?= e($desc) ?></textarea>

    <label class="chk"><input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?>> 활성</label>

    <div class="pbtns">
        <button type="button" onclick="window.close()">닫기</button>
        <button type="submit" class="primary">저장</button>
    </div>
</form>
<?php popup_footer(); ?>
