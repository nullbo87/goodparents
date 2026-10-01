<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

if (is_post()) {
    csrf_check();
    if (post('act') === 'delete') {
        $id  = int_or_zero(post('id'));
        $cnt = (int) col('SELECT COUNT(*) FROM situation WHERE situation_category_id = ?', array($id));
        if ($cnt > 0) {
            flash_set('이 분류에 속한 상황이 ' . $cnt . '건 있어 삭제할 수 없습니다.', 'err');
        } else {
            q('DELETE FROM situation_category WHERE id = ?', array($id));
            flash_set('상황분류를 삭제했습니다.');
        }
    }
    redirect('index.php?p=categories');
}

$rows = all('SELECT c.*, lc.name AS lc_name,
             (SELECT COUNT(*) FROM situation s WHERE s.situation_category_id = c.id) AS s_cnt
             FROM situation_category c
             JOIN life_cycle lc ON lc.id = c.life_cycle_id
             ORDER BY lc.sort_order, c.sort_order, c.id');

render_header('생애주기별 상황분류', 'categories');
?>
<div class="bar">
    <h2 class="page-title">생애주기별 상황분류 관리</h2>
    <a class="btn add" href="popup.php?f=category_add" onclick="return pop(this)">상황 분류 추가</a>
</div>
<div class="tablewrap">
<table class="grid">
    <thead><tr>
        <th class="w-no">No</th><th class="w-lc">생애주기</th><th class="w-cat">상황분류</th>
        <th class="w-cnt">상황 수</th><th>설명</th><th class="w-ops">관리</th>
    </tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="6" class="empty">등록된 상황분류가 없습니다.</td></tr>
    <?php endif; ?>
    <?php $no = count($rows); foreach ($rows as $r): ?>
        <tr>
            <td class="num"><?= $no-- ?></td>
            <td><?= e($r['lc_name']) ?></td>
            <td><b><?= e($r['name']) ?></b></td>
            <td class="num"><?= (int) $r['s_cnt'] ?></td>
            <td class="ql"><?= e($r['description']) ?></td>
            <td class="ops">
                <a class="mini" href="popup.php?f=category_add&id=<?= (int) $r['id'] ?>" onclick="return pop(this)">수정</a>
                <form method="post" onsubmit="return confirm('삭제할까요?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="mini danger" type="submit">삭제</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php render_footer(); ?>
