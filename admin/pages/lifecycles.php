<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

if (is_post()) {
    csrf_check();
    $act = post('act');
    $id  = int_or_zero(post('id'));
    if ($act === 'delete') {
        $cnt = (int) col('SELECT COUNT(*) FROM situation_category WHERE life_cycle_id = ?', array($id));
        if ($cnt > 0) {
            flash_set('하위 상황분류가 ' . $cnt . '건 있어 삭제할 수 없습니다. 대신 비활성 처리하세요.', 'err');
        } else {
            q('DELETE FROM life_cycle WHERE id = ?', array($id));
            flash_set('생애주기를 삭제했습니다.');
        }
    } elseif ($act === 'toggle') {
        q('UPDATE life_cycle SET is_active = 1 - is_active WHERE id = ?', array($id));
        flash_set('활성 상태를 변경했습니다.');
    }
    redirect('index.php?p=lifecycles');
}

$rows = all('SELECT lc.*,
             (SELECT COUNT(*) FROM situation_category c WHERE c.life_cycle_id = lc.id) AS cat_cnt
             FROM life_cycle lc ORDER BY sort_order, id');

render_header('생애주기 관리', 'lifecycles');
?>
<div class="bar">
    <h2 class="page-title">생애주기 관리</h2>
    <a class="btn add" href="popup.php?f=lifecycle_add" onclick="return pop(this)">생애주기 추가</a>
</div>
<div class="tablewrap">
<table class="grid">
    <thead><tr>
        <th class="w-no">No</th><th class="w-lc">생애주기 명칭</th><th class="w-cnt">순서</th>
        <th class="w-cnt">활성</th><th class="w-cnt">상황분류 수</th><th>설명</th><th class="w-ops">관리</th>
    </tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="7" class="empty">데이터가 없습니다. install.php 로 초기 데이터를 넣으세요.</td></tr>
    <?php endif; ?>
    <?php $no = count($rows); foreach ($rows as $r): ?>
        <tr>
            <td class="num"><?= $no-- ?></td>
            <td><b><?= e($r['name']) ?></b></td>
            <td class="num"><?= (int) $r['sort_order'] ?></td>
            <td class="num"><?= $r['is_active'] ? 'Y' : '<span class="off">N</span>' ?></td>
            <td class="num"><?= (int) $r['cat_cnt'] ?></td>
            <td class="ql"><?= e($r['description']) ?></td>
            <td class="ops">
                <a class="mini" href="popup.php?f=lifecycle_add&id=<?= (int) $r['id'] ?>" onclick="return pop(this)">수정</a>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="toggle">
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="mini" type="submit"><?= $r['is_active'] ? '비활성' : '활성' ?></button>
                </form>
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
<p class="hint">여기서 추가·비활성·순서 변경한 내용은 메인화면·상황목록·각 추가 팝업의 생애주기 목록에 즉시 반영됩니다.</p>
<?php render_footer(); ?>
