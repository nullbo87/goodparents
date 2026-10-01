<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$lcs = all('SELECT id, name FROM life_cycle ORDER BY sort_order, id');
$lc  = int_or_zero(get('lc'));
if (!is_post() && $lc === 0 && $lcs) {
    redirect('index.php?p=situations&lc=' . (int) $lcs[0]['id']);
}
if ($lc === 0 && $lcs) { $lc = (int) $lcs[0]['id']; }

if (is_post()) {
    csrf_check();
    if (post('act') === 'delete') {
        try {
            q('DELETE FROM situation WHERE id = ?', array(int_or_zero(post('id'))));
            flash_set('상황을 삭제했습니다.');
        } catch (Exception $ex) {
            flash_set('삭제하지 못했습니다: ' . $ex->getMessage(), 'err');
        }
    }
    redirect('index.php?p=situations&lc=' . $lc);
}

$lc_name = '';
foreach ($lcs as $x) { if ((int) $x['id'] === $lc) { $lc_name = $x['name']; } }

$rows = all('SELECT s.id, lc.name AS lc_name, sc.name AS cat_name, s.title,
             (SELECT COUNT(*) FROM situation_checklist_item i WHERE i.situation_id = s.id) AS item_cnt,
             (SELECT COUNT(*) FROM situation_media m WHERE m.situation_id = s.id) AS media_cnt
             FROM situation s
             JOIN situation_category sc ON sc.id = s.situation_category_id
             JOIN life_cycle lc ON lc.id = sc.life_cycle_id
             WHERE lc.id = ?
             ORDER BY sc.name DESC, s.id DESC', array($lc));

render_header('상황목록', 'situations');
?>
<div class="bar">
    <h2 class="page-title" style="margin:0"><?= e($lc_name) ?> 상황목록</h2>
    <?php if ($lc > 0): ?>
    <a class="btn add" href="popup.php?f=situation_add&lc=<?= $lc ?>" onclick="return pop(this)">상황 추가</a>
    <?php endif; ?>
</div>
<p class="hint">좌측 메뉴 <b>생애주기별 상황목록</b>에서 다른 생애주기를 선택할 수 있습니다.</p>
<div class="tablewrap">
<table class="grid">
            <thead><tr>
                <th class="w-no">No</th><th class="w-lc">생애주기</th><th class="w-cat">상황분류</th>
                <th>상황</th><th class="w-cnt">체크리스트 항목수</th><th class="w-cnt">상황이해 동영상</th><th class="w-ops">관리</th>
            </tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="empty">등록된 상황이 없습니다.</td></tr>
            <?php endif; ?>
            <?php $no = count($rows); foreach ($rows as $r): $u = 'index.php?p=situation_view&id=' . (int) $r['id']; ?>
                <tr>
                    <td class="num"><?= $no-- ?></td>
                    <td><?= e($r['lc_name']) ?></td>
                    <td><?= e($r['cat_name']) ?></td>
                    <td class="ql"><a href="<?= $u ?>"><?= e($r['title']) ?></a></td>
                    <td class="num"><?= (int) $r['item_cnt'] ?></td>
                    <td class="num"><?= ((int) $r['media_cnt']) > 0 ? (int) $r['media_cnt'] : '-' ?></td>
                    <td class="ops">
                        <a class="mini" href="popup.php?f=situation_add&id=<?= (int) $r['id'] ?>" onclick="return pop(this)">수정</a>
                        <form method="post" onsubmit="return confirm('이 상황과 하위 체크리스트·동영상이 모두 삭제됩니다. 계속할까요?');">
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
