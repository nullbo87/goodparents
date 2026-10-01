<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

if (is_post()) {
    csrf_check();
    $act = post('act');

    if ($act === 'del') {
        $tid = int_or_zero(post('tid'));
        $inUse = (int) col('SELECT
            (SELECT COUNT(*) FROM situation_checklist_item WHERE scale_template_id = ?) +
            (SELECT COUNT(*) FROM parenting_checklist_item WHERE scale_template_id = ?)',
            array($tid, $tid));
        if ($inUse > 0) {
            flash_set('이 템플릿을 사용 중인 문항이 있어 삭제할 수 없습니다. 먼저 해당 문항에서 템플릿 선택을 해제하세요.', 'err');
        } else {
            q('DELETE FROM scale_label_template WHERE id = ?', array($tid));
            flash_set('템플릿을 삭제했습니다.');
        }
    }
    redirect('index.php?p=scale_templates');
}

$templates = all('SELECT * FROM scale_label_template ORDER BY sort_order, id');

render_header('척도 라벨 템플릿 관리', 'scale_templates');
?>
<div class="bar">
    <h2 class="page-title">척도 라벨 템플릿 관리</h2>
    <a class="btn add" href="popup.php?f=scale_template_add" onclick="return pop(this)">템플릿 추가</a>
</div>
<p class="hint">척도(단계형) 문항에서 각 점 아래에 표시할 단계별 라벨 묶음입니다. 체크리스트 문항 등록 화면에서 선택해 사용합니다.</p>

<div class="tablewrap">
<table class="grid">
    <thead><tr><th class="w-no">No</th><th>이름</th><th class="w-cnt">단계수</th><th>라벨</th><th class="w-ops">관리</th></tr></thead>
    <tbody>
    <?php if (!$templates): ?>
        <tr><td colspan="5" class="empty">등록된 템플릿이 없습니다.</td></tr>
    <?php endif; ?>
    <?php $no = count($templates); foreach ($templates as $t): ?>
        <tr>
            <td class="num"><?= $no-- ?></td>
            <td class="ql"><?= e($t['name']) ?></td>
            <td class="num"><?= (int) $t['steps'] ?></td>
            <td><?= e(str_replace('|', ' · ', $t['labels'])) ?></td>
            <td class="ops">
                <a class="mini" href="popup.php?f=scale_template_add&id=<?= (int) $t['id'] ?>" onclick="return pop(this)">수정</a>
                <form method="post" onsubmit="return confirm('템플릿을 삭제할까요?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="del">
                    <input type="hidden" name="tid" value="<?= (int) $t['id'] ?>">
                    <button class="mini danger" type="submit">삭제</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php render_footer(); ?>
