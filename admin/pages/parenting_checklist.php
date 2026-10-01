<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

if (is_post()) {
    csrf_check();
    if (post('act') === 'del_item') {
        $delId = int_or_zero(post('item_id'));
        q("DELETE FROM checklist_item_option WHERE scope = 'parenting' AND item_id = ?", array($delId));
        q('DELETE FROM parenting_checklist_item WHERE id = ?', array($delId));
        flash_set('문항을 삭제했습니다.');
    }
    redirect('index.php?p=parenting_checklist');
}

$attitudes = all('SELECT id, name FROM parenting_attitude WHERE is_active = 1 ORDER BY sort_order, id');
$items     = all('SELECT * FROM parenting_checklist_item ORDER BY id DESC');

$scores = array();
if ($items) {
    $ids = array();
    foreach ($items as $it) { $ids[] = (int) $it['id']; }
    $ph = implode(',', array_fill(0, count($ids), '?'));
    foreach (all("SELECT parenting_checklist_item_id AS iid, parenting_attitude_id AS aid, percentage AS p
                  FROM parenting_checklist_score WHERE parenting_checklist_item_id IN ($ph)", $ids) as $r) {
        $scores[(int) $r['iid']][(int) $r['aid']] = (int) $r['p'];
    }
}
$span = 4 + count($attitudes);

render_header('부모양육태도 체크리스트', 'parenting_checklist');
?>
<div class="bar">
    <h2 class="page-title">부모양육태도 체크리스트</h2>
    <a class="btn add" href="popup.php?f=parenting_checklist_add" onclick="return pop(this)">체크리스트 추가</a>
</div>
<p class="hint">전체적인 부모의 양육태도를 체크하기 위한 문항입니다. (특정 상황과 무관 · 상황별 체크리스트와 별도)</p>
<div class="tablewrap">
<table class="grid">
    <thead><tr>
        <th class="w-no">No</th><th>문항</th><th class="w-cnt">유형</th>
        <?php foreach ($attitudes as $a): ?><th class="w-cnt"><?= e($a['name']) ?></th><?php endforeach; ?>
        <th class="w-ops">관리</th>
    </tr></thead>
    <tbody>
    <?php if (!$items): ?>
        <tr><td colspan="<?= $span ?>" class="empty">등록된 문항이 없습니다.</td></tr>
    <?php endif; ?>
    <?php $no = count($items); foreach ($items as $it): ?>
        <tr>
            <td class="num"><?= $no-- ?></td>
            <td class="ql"><?= e($it['question']) ?></td>
            <td class="num"><?= e(checklist_type_label($it)) ?></td>
            <?php $isScale = (isset($it['answer_type']) ? $it['answer_type'] : 'scale') === 'scale'; ?>
            <?php foreach ($attitudes as $a):
                $v = isset($scores[(int) $it['id']][(int) $a['id']]) ? $scores[(int) $it['id']][(int) $a['id']] : null; ?>
                <td class="num"><?= $isScale ? ($v ? e($v) . '%' : '-') : '&middot;' ?></td>
            <?php endforeach; ?>
            <td class="ops">
                <a class="mini" href="popup.php?f=parenting_checklist_add&id=<?= (int) $it['id'] ?>" onclick="return pop(this)">수정</a>
                <form method="post" onsubmit="return confirm('문항을 삭제할까요?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="del_item">
                    <input type="hidden" name="item_id" value="<?= (int) $it['id'] ?>">
                    <button class="mini danger" type="submit">삭제</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php render_footer(); ?>
