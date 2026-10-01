<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

if (is_post()) {
    csrf_check();
    $act = post('act');
    $id  = int_or_zero(post('id'));
    if ($act === 'delete') {
        $ref = (int) col('SELECT
                 (SELECT COUNT(*) FROM situation_checklist_score WHERE parenting_attitude_id = ?) +
                 (SELECT COUNT(*) FROM parenting_checklist_score WHERE parenting_attitude_id = ?)',
                 array($id, $id));
        if ($ref > 0) {
            flash_set('체크리스트 배점에서 사용 중(' . $ref . '건)이라 삭제할 수 없습니다. 비활성 처리하세요.', 'err');
        } else {
            q('DELETE FROM parenting_attitude WHERE id = ?', array($id));
            flash_set('양육태도를 삭제했습니다.');
        }
    } elseif ($act === 'toggle') {
        q('UPDATE parenting_attitude SET is_active = 1 - is_active WHERE id = ?', array($id));
        flash_set('활성 상태를 변경했습니다.');
    }
    redirect('index.php?p=attitudes');
}

$rows = all('SELECT * FROM parenting_attitude ORDER BY sort_order, id');

render_header('양육태도 관리', 'attitudes');
?>
<div class="bar">
    <h2 class="page-title">양육태도 관리</h2>
    <a class="btn add" href="popup.php?f=attitude_add" onclick="return pop(this)">양육태도 추가</a>
</div>
<div class="note-inline">여기서 유형을 추가·삭제·비활성하면 모든 체크리스트 표와 입력 폼의 열 구성이 바뀝니다.</div>
<div class="tablewrap">
<table class="grid">
    <thead><tr>
        <th class="w-no">No</th><th class="w-cat">양육태도 명칭</th><th class="w-cnt">순서</th>
        <th class="w-cnt">활성</th><th>설명</th><th class="w-ops">관리</th>
    </tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="6" class="empty">데이터가 없습니다. install.php 로 초기 데이터를 넣으세요.</td></tr>
    <?php endif; ?>
    <?php $no = count($rows); foreach ($rows as $r): ?>
        <tr>
            <td class="num"><?= $no-- ?></td>
            <td><b><?= e($r['name']) ?></b></td>
            <td class="num"><?= (int) $r['sort_order'] ?></td>
            <td class="num"><?= $r['is_active'] ? 'Y' : '<span class="off">N</span>' ?></td>
            <td class="ql"><?= e($r['description']) ?></td>
            <td class="ops">
                <a class="mini" href="popup.php?f=attitude_add&id=<?= (int) $r['id'] ?>" onclick="return pop(this)">수정</a>
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
<?php render_footer(); ?>
