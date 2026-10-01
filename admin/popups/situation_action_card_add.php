<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$UPLOAD_DIR  = __DIR__ . '/../uploads';
$ALLOWED_IMG = array('jpg', 'jpeg', 'png', 'gif', 'webp');
$MAX_IMG     = 3 * 1024 * 1024;

$id      = int_or_zero(get('id'));
$editing = $id ? one('SELECT * FROM situation_action_card WHERE id = ?', array($id)) : null;

$sid = $editing ? (int) $editing['situation_id'] : int_or_zero(get('sid'));
$sit = one('SELECT s.title, sc.name AS cat, lc.name AS lc
            FROM situation s
            JOIN situation_category sc ON sc.id = s.situation_category_id
            JOIN life_cycle lc ON lc.id = sc.life_cycle_id
            WHERE s.id = ?', array($sid));
if (!$sit) {
    popup_header('Action Card');
    echo '<div class="flash flash-err">대상 상황을 찾을 수 없습니다.</div>';
    popup_footer();
    return;
}

$cardType = $editing ? $editing['card_type'] : 'text';
$title    = $editing ? $editing['title'] : '';
$content  = $editing ? $editing['content'] : '';
$curImage = $editing ? (string) $editing['image'] : '';
$err = '';

if (is_post()) {
    csrf_check();
    $cardType = (post('card_type') === 'image') ? 'image' : 'text';
    $title    = trim(post('title'));
    $content  = trim(post('content'));
    $delImg   = post('del_image') === '1';

    $newImage = null;
    $f = isset($_FILES['image']) ? $_FILES['image'] : null;
    if ($f && $f['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $err = '이미지 업로드에 실패했습니다. (오류코드 ' . (int) $f['error'] . ')';
        } else {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $ALLOWED_IMG, true)) {
                $err = '이미지는 ' . implode(', ', $ALLOWED_IMG) . ' 형식만 허용됩니다.';
            } elseif ($f['size'] > $MAX_IMG) {
                $err = '이미지가 너무 큽니다. (최대 3MB)';
            } else {
                $fname = 'card_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                if (is_dir($UPLOAD_DIR) && is_writable($UPLOAD_DIR)
                    && move_uploaded_file($f['tmp_name'], $UPLOAD_DIR . '/' . $fname)) {
                    $newImage = 'uploads/' . $fname;
                } else {
                    $err = '이미지 저장에 실패했습니다.';
                }
            }
        }
    }

    if ($err === '' && $title === '') { $err = '제목을 입력하세요.'; }
    $finalImage = $newImage !== null ? $newImage : ($delImg ? null : ($curImage !== '' ? $curImage : null));
    if ($err === '' && $cardType === 'image' && !$finalImage) {
        $err = '이미지형 카드는 이미지를 등록해야 합니다.';
    }

    if ($err === '') {
        $oldImageToUnlink = ($curImage !== '' && $curImage !== $finalImage) ? $curImage : null;
        if ($editing) {
            q('UPDATE situation_action_card SET card_type = ?, title = ?, content = ?, image = ? WHERE id = ?',
              array($cardType, $title, nz($content), $finalImage, $id));
        } else {
            $ord = (int) col('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM situation_action_card WHERE situation_id = ?',
                             array($sid));
            q('INSERT INTO situation_action_card (situation_id, card_type, title, content, image, sort_order)
               VALUES (?, ?, ?, ?, ?, ?)',
              array($sid, $cardType, $title, nz($content), $finalImage, $ord));
        }
        if ($oldImageToUnlink && strpos($oldImageToUnlink, 'uploads/') === 0) {
            $fp = $UPLOAD_DIR . '/' . basename($oldImageToUnlink);
            if (is_file($fp)) { @unlink($fp); }
        }
        popup_done();
    }
}

popup_header('Action Card' . ($editing ? ' 수정' : ' 추가'));
echo '<p class="muted small">' . e($sit['lc']) . ' &gt; ' . e($sit['cat']) . '</p>';
echo '<p class="sit-title">' . e($sit['title']) . '</p>';
if ($err !== '') { echo '<div class="flash flash-err">' . e($err) . '</div>'; }
?>
<form method="post" class="pform" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label>카드 형태</label>
    <div class="atype-tabs">
        <label class="atype-opt"><input type="radio" name="card_type" value="text" <?= $cardType === 'text' ? 'checked' : '' ?>> 텍스트</label>
        <label class="atype-opt"><input type="radio" name="card_type" value="image" <?= $cardType === 'image' ? 'checked' : '' ?>> 이미지</label>
    </div>

    <label>제목</label>
    <input type="text" name="title" value="<?= e($title) ?>" required autofocus>

    <label>내용</label>
    <textarea name="content" rows="4" placeholder="카드에 표시할 설명"><?= e($content) ?></textarea>

    <label>이미지 <span class="hint" style="margin:0">(이미지형 카드는 필수, jpg/png/gif/webp · 최대 3MB)</span></label>
    <?php if ($curImage !== ''): ?>
        <div class="opt-img-cur"><img src="<?= e($curImage) ?>" alt="" style="max-width:160px;display:block;margin-bottom:6px"></div>
        <label class="opt-delimg-lb"><input type="checkbox" name="del_image" value="1"> 이미지 삭제</label>
    <?php endif; ?>
    <input type="file" name="image" accept="image/*">

    <div class="pbtns">
        <button type="button" onclick="window.close()">닫기</button>
        <button type="submit" class="primary">저장</button>
    </div>
</form>
<?php popup_footer(); ?>
