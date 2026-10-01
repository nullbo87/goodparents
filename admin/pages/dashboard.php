<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$lc = int_or_zero(get('lc'));
$at = int_or_zero(get('at'));

$lcs = all('SELECT id, name FROM life_cycle WHERE is_active = 1 ORDER BY sort_order, id');
$ats = all('SELECT id, name FROM parenting_attitude WHERE is_active = 1 ORDER BY sort_order, id');

$where  = array();
$params = array();
$sql = 'SELECT s.id, lc.name AS lc_name, sc.name AS cat_name, s.title,
        (SELECT COUNT(*) FROM situation_checklist_item i WHERE i.situation_id = s.id) AS item_cnt,
        (SELECT COUNT(*) FROM situation_media m WHERE m.situation_id = s.id) AS media_cnt
        FROM situation s
        JOIN situation_category sc ON sc.id = s.situation_category_id
        JOIN life_cycle lc ON lc.id = sc.life_cycle_id';
if ($lc > 0) { $where[] = 'lc.id = ?'; $params[] = $lc; }
if ($at > 0) {
    $where[] = 'EXISTS (SELECT 1 FROM situation_checklist_item i2
                 JOIN situation_checklist_score sco ON sco.situation_checklist_item_id = i2.id
                 WHERE i2.situation_id = s.id AND sco.parenting_attitude_id = ? AND sco.percentage > 0)';
    $params[] = $at;
}
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY s.id DESC';
$rows = all($sql, $params);

render_header('메인화면', 'main');
?>
<h2 class="section-title">생애주기</h2>
<div class="chips">
    <a class="chip <?= $lc === 0 ? 'on' : '' ?>" href="index.php<?= $at ? '?at=' . $at : '' ?>">전체</a>
    <?php foreach ($lcs as $x): ?>
        <a class="chip <?= $lc === (int) $x['id'] ? 'on' : '' ?>"
           href="index.php?lc=<?= (int) $x['id'] ?><?= $at ? '&at=' . $at : '' ?>"><?= e($x['name']) ?></a>
    <?php endforeach; ?>
</div>

<h2 class="section-title">양육태도</h2>
<div class="chips">
    <a class="chip <?= $at === 0 ? 'on' : '' ?>" href="index.php<?= $lc ? '?lc=' . $lc : '' ?>">전체</a>
    <?php foreach ($ats as $x): ?>
        <a class="chip <?= $at === (int) $x['id'] ? 'on' : '' ?>"
           href="index.php?<?= $lc ? 'lc=' . $lc . '&' : '' ?>at=<?= (int) $x['id'] ?>"><?= e($x['name']) ?></a>
    <?php endforeach; ?>
</div>
<p class="hint">양육태도 칩: 선택한 유형에 배점(%)이 매겨진 상황별 체크리스트 문항을 가진 상황만 표시합니다.</p>

<h2 class="section-title">상황목록</h2>
<div class="tablewrap">
<table class="grid">
    <thead><tr>
        <th class="w-no">No</th><th class="w-lc">생애주기</th><th class="w-cat">상황분류</th>
        <th>상황</th><th class="w-cnt">체크리스트 항목수</th><th class="w-cnt">상황이해 동영상</th>
    </tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="6" class="empty">등록된 상황이 없습니다.</td></tr>
    <?php endif; ?>
    <?php $no = count($rows); foreach ($rows as $r): $u = 'index.php?p=situation_view&id=' . (int) $r['id']; ?>
        <tr class="clickable" onclick="location.href='<?= $u ?>'">
            <td class="num"><?= $no-- ?></td>
            <td><?= e($r['lc_name']) ?></td>
            <td><?= e($r['cat_name']) ?></td>
            <td class="ql"><a href="<?= $u ?>"><?= e($r['title']) ?></a></td>
            <td class="num"><?= (int) $r['item_cnt'] ?></td>
            <td class="num"><?= ((int) $r['media_cnt']) > 0 ? (int) $r['media_cnt'] : '-' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php render_footer(); ?>
