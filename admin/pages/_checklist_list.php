<?php
/* 체크리스트 섹션 partial (아이의 마음 읽기 / 나의 마음 알아보기 공용)
 * 필요 변수: $cl_num, $cl_title, $cl_add_popup, $cl_del_act, $cl_sid, $cl_items, $cl_scores, $attitudes */
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }
$cl_span = 4 + count($attitudes);
?>
<div class="bar mt">
    <h3 class="vsec"><span class="vnum"><?= e($cl_num) ?>.</span> <?= e($cl_title) ?></h3>
    <a class="btn add" href="popup.php?f=<?= e($cl_add_popup) ?>&sid=<?= $cl_sid ?>" onclick="return pop(this)">체크리스트 추가</a>
</div>
<div class="vbody">
<div class="tablewrap">
<table class="grid">
    <thead><tr>
        <th class="w-no">No</th><th>문항</th><th class="w-cnt">유형</th>
        <?php foreach ($attitudes as $a): ?><th class="w-cnt"><?= e($a['name']) ?></th><?php endforeach; ?>
        <th class="w-ops">관리</th>
    </tr></thead>
    <tbody>
    <?php if (!$cl_items): ?>
        <tr><td colspan="<?= $cl_span ?>" class="empty">등록된 문항이 없습니다.</td></tr>
    <?php endif; ?>
    <?php $no = count($cl_items); foreach ($cl_items as $it): ?>
        <tr>
            <td class="num"><?= $no-- ?></td>
            <td class="ql"><?= e($it['question']) ?></td>
            <td class="num"><?= e(checklist_type_label($it)) ?></td>
            <?php $isScale = (isset($it['answer_type']) ? $it['answer_type'] : 'scale') === 'scale'; ?>
            <?php foreach ($attitudes as $a):
                $v = isset($cl_scores[(int) $it['id']][(int) $a['id']]) ? $cl_scores[(int) $it['id']][(int) $a['id']] : null; ?>
                <td class="num"><?= $isScale ? ($v ? e($v) . '%' : '-') : '&middot;' ?></td>
            <?php endforeach; ?>
            <td class="ops">
                <a class="mini" href="popup.php?f=<?= e($cl_add_popup) ?>&id=<?= (int) $it['id'] ?>" onclick="return pop(this)">수정</a>
                <form method="post" onsubmit="return confirm('문항을 삭제할까요?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="<?= e($cl_del_act) ?>">
                    <input type="hidden" name="item_id" value="<?= (int) $it['id'] ?>">
                    <button class="mini danger" type="submit">삭제</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
</div>
