<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

$mid = current_member_id();
$m = one('SELECT * FROM member WHERE id = ?', array($mid));

if (is_post()) {
    csrf_check();
    $nickname = trim(post('nickname'));
    $pg = post('parent_gender') === 'F' ? 'F' : (post('parent_gender') === 'M' ? 'M' : null);
    $phone = preg_replace('/[^0-9]/', '', post('phone'));
    if ($nickname === '') {
        flash_set('별칭을 입력하세요.', 'err');
    } else {
        q('UPDATE member SET nickname = ?, parent_gender = ?, phone = ?,
             region_zip = ?, region_addr = ?, region_detail = ?, updated_at = NOW()
           WHERE id = ?',
          array($nickname, $pg, nz($phone),
                nz(post('region_zip')), nz(post('region_addr')), nz(post('region_detail')), $mid));
        flash_set('기본정보를 저장했습니다.');
    }
    redirect('index.php?p=mypage');
}

render_header('내정보 · 기본정보', '');
?>
<div class="wrap mypage">
<h2 class="page-title">내정보</h2>
<div class="bar">
  <a class="btn" href="index.php?p=mypage">기본정보</a>
  <a class="btn gray" href="index.php?p=mypage_children">자녀정보</a>
</div>

<form method="post" class="panel" >
  <?= csrf_field() ?>
  <div class="form-row"><label>별칭</label>
    <input type="text" name="nickname" value="<?= e($m['nickname']) ?>" required></div>

  <div class="form-row"><label>성별</label>
    <input type="hidden" name="parent_gender" value="<?= e($m['parent_gender']) ?>">
    <div class="toggle" data-name="parent_gender">
      <button type="button" data-val="M" class="<?= $m['parent_gender'] === 'M' ? 'on' : '' ?>">아빠</button>
      <button type="button" data-val="F" class="<?= $m['parent_gender'] === 'F' ? 'on' : '' ?>">엄마</button>
    </div>
  </div>

  <div class="form-row"><label>휴대폰</label>
    <input type="tel" name="phone" value="<?= e($m['phone']) ?>" placeholder="01012345678"></div>

  <div class="form-row"><label>거주지역</label>
    <div class="inline">
      <input type="text" name="region_zip" value="<?= e($m['region_zip']) ?>" placeholder="우편번호" style="max-width:120px" readonly>
      <button type="button" class="btn gray" data-postcode>주소검색</button>
    </div>
    <input type="text" name="region_addr" value="<?= e($m['region_addr']) ?>" placeholder="주소" class="mt" readonly>
    <input type="text" name="region_detail" value="<?= e($m['region_detail']) ?>" placeholder="상세주소" class="mt">
  </div>

  <button type="submit" class="btn block">저장</button>
</form>
</div>
<?php
render_footer();
