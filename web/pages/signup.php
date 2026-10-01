<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

/* SNS 연동 전: 회원가입은 임시 member(#1) 를 채우는 마법사.
   1) SNS 안내 → 다음   2) 프로필   3) 자녀정보 → 완료 시 부모양육태도 체크리스트로 이동 */

$step = clamp_int(get('step', 1), 1, 3);
if (!isset($_SESSION['signup'])) { $_SESSION['signup'] = array(); }
$S = &$_SESSION['signup'];

if (is_post()) {
    csrf_check();
    $step = clamp_int(post('step', 1), 1, 3);

    if ($step === 1) {
        redirect('index.php?p=signup&step=2');

    } elseif ($step === 2) {
        $S['nickname']      = trim(post('nickname'));
        $S['parent_gender'] = post('parent_gender') === 'F' ? 'F' : (post('parent_gender') === 'M' ? 'M' : null);
        $S['phone']         = preg_replace('/[^0-9]/', '', post('phone_1') . post('phone_2') . post('phone_3'));
        $S['region_zip']    = trim(post('region_zip'));
        $S['region_addr']   = trim(post('region_addr'));
        $S['region_detail'] = trim(post('region_detail'));
        $err = array();
        if ($S['nickname'] === '') { $err[] = '별칭을 입력하세요.'; }
        if (!$S['parent_gender']) { $err[] = '성별을 선택하세요.'; }
        if (!$err) { redirect('index.php?p=signup&step=3'); }
        flash_set(implode(' ', $err), 'err');
        redirect('index.php?p=signup&step=2');

    } else { /* step 3 */
        $cnt = clamp_int(post('child_count', 1), 1, 3);
        $children = array();
        for ($i = 1; $i <= $cnt; $i++) {
            $children[] = array(
                'name' => trim(post("child_name_$i")),
                'lc'   => int_or_zero(post("child_lc_$i")),
                'grade' => clamp_int(post("child_grade_$i"), 0, 6),
                'gender' => post("child_gender_$i") === 'F' ? 'F' : 'M',
            );
        }
        $err = array();
        foreach ($children as $k => $c) {
            if ($c['name'] === '' || $c['lc'] <= 0 || $c['grade'] < 1) {
                $err[] = ($k + 1) . '번째 자녀 정보를 모두 입력하세요.';
            }
        }
        if ($err) {
            flash_set(implode(' ', $err), 'err');
            redirect('index.php?p=signup&step=3');
        }

        /* 저장: 임시 member #1 */
        $mid = ensure_temp_member();
        q('UPDATE member SET nickname = ?, parent_gender = ?, phone = ?, phone_verified = 1,
             region_zip = ?, region_addr = ?, region_detail = ?, updated_at = NOW() WHERE id = ?',
          array(nz($S['nickname']), $S['parent_gender'], nz($S['phone']),
                nz($S['region_zip']), nz($S['region_addr']), nz($S['region_detail']), $mid));
        q('DELETE FROM member_child WHERE member_id = ?', array($mid));
        $o = 1;
        foreach ($children as $c) {
            q('INSERT INTO member_child (member_id, name, life_cycle_id, grade, gender, sort_order)
               VALUES (?, ?, ?, ?, ?, ?)',
              array($mid, $c['name'], $c['lc'], $c['grade'] ?: null, $c['gender'], $o++));
        }
        unset($_SESSION['signup']);
        $_SESSION['member_id'] = $mid;
        flash_set('회원가입이 완료되었습니다. 부모양육태도 체크리스트를 진행해 주세요.');
        redirect('index.php?p=parenting');
    }
}

$lcs = life_cycles();

render_header('회원가입', '');
?>
<div class="authbox">
  <h2 class="section-title">좋은 부모 배움터</h2>
  <div class="wizbar">
    <i class="<?= $step >= 1 ? 'on' : '' ?>"></i>
    <i class="<?= $step >= 2 ? 'on' : '' ?>"></i>
    <i class="<?= $step >= 3 ? 'on' : '' ?>"></i>
  </div>

<div class="loginbox join">
<?php if ($step === 1): ?>

  <?php render_sns_buttons(); ?>
  <p class="tit">SNS 연동은 준비 중입니다. 아래 [다음]으로 임시 가입을 진행하세요.</p>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="step" value="1">
    <button type="submit" class="btn block">다음</button>
  </form>

<?php elseif ($step === 2): ?>
  <form method="post" >
    <?= csrf_field() ?><input type="hidden" name="step" value="2">
    <div class="form-row"><label>별칭</label>
      <input type="text" name="nickname" value="<?= e(isset($S['nickname']) ? $S['nickname'] : '') ?>" required></div>

    <div class="form-row"><label>성별</label>
      <input type="hidden" name="parent_gender" value="<?= e(isset($S['parent_gender']) ? $S['parent_gender'] : '') ?>">
      <div class="toggle" data-name="parent_gender">
        <button type="button" data-val="M" class="<?= (isset($S['parent_gender']) && $S['parent_gender'] === 'M') ? 'on' : '' ?>">아빠</button>
        <button type="button" data-val="F" class="<?= (isset($S['parent_gender']) && $S['parent_gender'] === 'F') ? 'on' : '' ?>">엄마</button>
      </div>
    </div>

    <div class="form-row"><label>휴대폰 <span class="muted small">(인증 준비 중 · 형식만 입력)</span></label>
      <div class="inline">
        <input type="tel" name="phone_1" value="010" style="width:70px">
        <input type="tel" name="phone_2" maxlength="4" style="width:90px">
        <input type="tel" name="phone_3" maxlength="4" style="width:90px">
        <button type="button" class="btn gray" data-phone-send>인증번호</button>
      </div>
      <div class="inline mt">
        <input type="text" name="phone_code" placeholder="인증번호 입력" style="max-width:160px">
        <button type="button" class="btn gray" data-phone-send>인증</button>
      </div>
    </div>

    <div class="form-row"><label>거주지역</label>
      <div class="inline">
        <input type="text" name="region_zip" placeholder="우편번호" style="max-width:120px" readonly>
        <button type="button" class="btn gray" data-postcode>주소검색</button>
      </div>
      <input type="text" name="region_addr" placeholder="주소" class="mt" readonly>
      <input type="text" name="region_detail" placeholder="상세주소" class="mt">
    </div>

    <button type="submit" class="btn block">다음</button>
  </form>

<?php else: /* step 3 */ ?>
  <form method="post" >
    <?= csrf_field() ?><input type="hidden" name="step" value="3">
    <div class="form-row"><label>자녀수</label>
      <input type="hidden" name="child_count" value="1">
      <div class="toggle" data-name="child_count">
        <button type="button" data-val="1" class="on">1명</button>
        <button type="button" data-val="2">2명</button>
        <button type="button" data-val="3">3명</button>
      </div>
    </div>

    <?php for ($i = 1; $i <= 3; $i++): ?>
    <div class="child-block"<?= $i > 1 ? ' hidden' : '' ?>>
      <h4>자녀 <?= $i ?></h4>
      <div class="form-row"><label>이름</label>
        <input type="text" name="child_name_<?= $i ?>" data-req="1" <?= $i === 1 ? 'required' : '' ?>></div>
      <div class="form-row"><label>학교 (생애주기)</label>
        <select name="child_lc_<?= $i ?>" data-req="1" <?= $i === 1 ? 'required' : '' ?>>
          <option value="">선택</option>
          <?php foreach ($lcs as $lc): ?>
            <option value="<?= (int) $lc['id'] ?>"><?= e($lc['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row"><label>학년</label>
        <select name="child_grade_<?= $i ?>" data-req="1" <?= $i === 1 ? 'required' : '' ?>>
          <option value="">선택</option>
          <?php for ($g = 1; $g <= 6; $g++): ?>
            <option value="<?= $g ?>"><?= $g ?>학년</option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-row"><label>성별</label>
        <input type="hidden" name="child_gender_<?= $i ?>" value="M">
        <div class="toggle" data-name="child_gender_<?= $i ?>">
          <button type="button" data-val="M" class="on">남자</button>
          <button type="button" data-val="F">여자</button>
        </div>
      </div>
    </div>
    <?php endfor; ?>

    <button type="submit" class="btn block">회원가입</button>
  </form>
<?php endif; ?>
</div>
</div>
<?php
render_footer();
