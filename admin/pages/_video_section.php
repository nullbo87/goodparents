<?php
/* 동영상 섹션 partial (상황이해 / 문제해결 공용)
 * 필요 변수: $sid, $vs_kind, $vs_num, $vs_title, $vs_conti, $vs_rows  */
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }
?>
<h3 class="vsec"><span class="vnum"><?= e($vs_num) ?>.</span> <?= e($vs_title) ?></h3>
<div class="vbody">
<div class="vgrid">

<form method="post" class="conti">
    <?= csrf_field() ?>
    <input type="hidden" name="act" value="save_conti">
    <input type="hidden" name="kind" value="<?= e($vs_kind) ?>">
    <label>콘티</label>
    <textarea name="conti" rows="4" placeholder="콘티 텍스트"><?= e($vs_conti) ?></textarea>
    <div class="conti-bar"><button type="submit" class="btn">저장</button></div>
</form>

<div class="vmedia">
<div class="vlabel">동영상</div>
<div class="tablewrap">
<table class="grid">
    <thead><tr>
        <th class="w-no">No</th><th class="w-use">사용여부</th><th class="w-fmt">형식</th>
        <th>URL</th><th class="w-vops">관리</th>
    </tr></thead>
    <tbody>
    <?php if (!$vs_rows): ?>
        <tr><td colspan="5" class="empty">등록된 동영상이 없습니다.</td></tr>
    <?php endif; ?>
    <?php $no = count($vs_rows); foreach ($vs_rows as $r):
        $isFile = ($r['media_type'] === 'file'); ?>
        <tr>
            <td class="num"><?= $no-- ?></td>
            <td class="num">
                <form method="post" class="usef">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="toggle_media">
                    <input type="hidden" name="media_id" value="<?= (int) $r['id'] ?>">
                    <input type="checkbox" name="in_use" value="1" <?= $r['in_use'] ? 'checked' : '' ?> onchange="this.form.submit()">
                </form>
            </td>
            <td class="num"><?= $isFile ? '파일' : 'URL' ?></td>
            <td class="ql vurl"><a href="<?= e($r['url']) ?>" target="_blank" rel="noopener"><?= e($r['url']) ?></a></td>
            <td class="ops">
                <form method="post" onsubmit="return confirm('삭제할까요?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="act" value="del_media">
                    <input type="hidden" name="media_id" value="<?= (int) $r['id'] ?>">
                    <button class="mini danger" type="submit">삭제</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<form method="post" class="vadd">
    <?= csrf_field() ?>
    <input type="hidden" name="act" value="add_media">
    <input type="hidden" name="kind" value="<?= e($vs_kind) ?>">
    <input type="hidden" name="media_type" value="url">
    <span class="vadd-label">URL추가</span>
    <input type="url" name="url" placeholder="https://..." required>
    <button type="submit" class="btn">동영상 추가</button>
</form>

<form method="post" class="vadd vfile" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="act" value="add_media">
    <input type="hidden" name="kind" value="<?= e($vs_kind) ?>">
    <input type="hidden" name="media_type" value="file">
    <span class="vadd-label">파일추가</span>
    <label class="filebtn" title="동영상 파일 선택 (최대 20MB)">
        <input type="file" name="mediafile" accept="video/*" onchange="if(this.files.length){this.form.submit();}">
        <span class="plus">+</span>
    </label>
    <span class="hint" style="margin:0">mp4·webm·mov 등 · 최대 20MB (큰 영상은 URL 사용)</span>
</form>
</div><!-- /.vmedia -->

</div><!-- /.vgrid -->
</div>
