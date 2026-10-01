<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

$mid = current_member_id();
$lcs = life_cycles();

if (is_post()) {
    csrf_check();
    $cnt = clamp_int(post('child_count', 1), 1, 3);
    $children = array();
    $bad = false;
    for ($i = 1; $i <= $cnt; $i++) {
        $name  = trim(post("child_name_$i"));
        $lc    = int_or_zero(post("child_lc_$i"));
        $grade = clamp_int(post("child_grade_$i"), 0, 6);
        $g     = post("child_gender_$i") === 'F' ? 'F' : 'M';
        if ($name === '' || $lc <= 0 || $grade < 1) { $bad = true; }
        $children[] = array($name, $lc, $grade, $g);
    }
    if ($bad) {
        flash_set('모든 자녀 정보를 입력하세요.', 'err');
    } else {
        q('DELETE FROM member_child WHERE member_id = ?', array($mid));
        $o = 1;
        foreach ($children as $c) {
            q('INSERT INTO member_child (member_id, name, life_cycle_id, grade, gender, sort_order)
               VALUES (?, ?, ?, ?, ?, ?)', array($mid, $c[0], $c[1], $c[2] ?: null, $c[3], $o++));
        }
        flash_set('자녀정보를 저장했습니다.');
    }
    redirect('index.php?p=mypage_children');
}

$kids = all('SELECT * FROM member_child WHERE member_id = ? ORDER BY sort_order, id', array($mid));
$cnt  = max(1, count($kids));

render_header('내정보 · 자녀정보', '');
?>
<div class="wrap mypage">
<h2 class="page-title">내정보</h2>
<div class="bar">
  <a class="btn gray" href="index.php?p=mypage">기본정보</a>
  <a class="btn" href="index.php?p=mypage_children">자녀정보</a>
</div>

<form method="post" class="panel" >
  <?= csrf_field() ?>
  <div class="form-row"><label>자녀수</label>
    <input type="hidden" name="child_count" value="<?= $cnt ?>">
    <div class="toggle" data-name="child_count">
      <?php for ($k = 1; $k <= 3; $k++): ?>
        <button type="button" data-val="<?= $k ?>" class="<?= $k === $cnt ? 'on' : '' ?>"><?= $k ?>명</button>
      <?php endfor; ?>
    </div>
  </div>

  <?php for ($i = 1; $i <= 3; $i++):
    $k = isset($kids[$i - 1]) ? $kids[$i - 1] : null; ?>
    <div class="child-block"<?= $i > $cnt ? ' hidden' : '' ?>>
      <h4>자녀 <?= $i ?></h4>
      <div class="form-row"><label>이름</label>
        <input type="text" name="child_name_<?= $i ?>" data-req="1"
               value="<?= e($k ? $k['name'] : '') ?>" <?= $i <= $cnt ? 'required' : '' ?>></div>
      <div class="form-row"><label>학교 (생애주기)</label>
        <select name="child_lc_<?= $i ?>" data-req="1" <?= $i <= $cnt ? 'required' : '' ?>>
          <option value="">선택</option>
          <?php foreach ($lcs as $lc): ?>
            <option value="<?= (int) $lc['id'] ?>" <?= ($k && (int) $k['life_cycle_id'] === (int) $lc['id']) ? 'selected' : '' ?>>
              <?= e($lc['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row"><label>학년</label>
        <select name="child_grade_<?= $i ?>" data-req="1" <?= $i <= $cnt ? 'required' : '' ?>>
          <option value="">선택</option>
          <?php for ($g = 1; $g <= 6; $g++): ?>
            <option value="<?= $g ?>" <?= ($k && (int) $k['grade'] === $g) ? 'selected' : '' ?>><?= $g ?>학년</option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-row"><label>성별</label>
        <input type="hidden" name="child_gender_<?= $i ?>" value="<?= e($k ? $k['gender'] : 'M') ?>">
        <div class="toggle" data-name="child_gender_<?= $i ?>">
          <button type="button" data-val="M" class="<?= (!$k || $k['gender'] === 'M') ? 'on' : '' ?>">남자</button>
          <button type="button" data-val="F" class="<?= ($k && $k['gender'] === 'F') ? 'on' : '' ?>">여자</button>
        </div>
      </div>
    </div>
  <?php endfor; ?>

  <button type="submit" class="btn block">저장</button>
</form>
</div>
<?php
render_footer();
