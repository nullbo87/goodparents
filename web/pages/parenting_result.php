<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/../inc/scoring.php';

$mid = current_member_id();
$sid = int_or_zero(get('s'));
$sub = $sid
    ? one("SELECT * FROM checklist_submission WHERE id = ? AND member_id = ? AND scope = 'parenting'", array($sid, $mid))
    : one("SELECT * FROM checklist_submission WHERE member_id = ? AND scope = 'parenting' ORDER BY id DESC LIMIT 1", array($mid));

if (!$sub) { redirect('index.php?p=parenting'); }

$rows = all('SELECT r.score, a.name
             FROM checklist_result r
             JOIN parenting_attitude a ON a.id = r.parenting_attitude_id
             WHERE r.submission_id = ?
             ORDER BY r.score DESC', array((int) $sub['id']));

$max = 0.0001;
foreach ($rows as $r) { $max = max($max, (float) $r['score']); }
$top = array_slice($rows, 0, 2);

render_header('양육태도 체크 결과', '');
?>
<div class="wrap">
<h2 class="section-title">양육 태도 체크 결과</h2>

<div class="result-box">
  <p class="muted">당신의 양육태도는 다음과 같습니다.</p>
  <p class="stitle">
    <?php if ($top): ?>
      <?= e($top[0]['name']) ?><?php if (isset($top[1])): ?> · <?= e($top[1]['name']) ?><?php endif; ?>
    <?php else: ?>—<?php endif; ?>
  </p>
  <p class="hint" style="margin-top:0">상위 2개 유형입니다. 상세 설명 문구는 추후 제공됩니다.</p>

  <?php foreach ($rows as $i => $r): ?>
    <div class="att-row <?= $i < 2 ? 'top' : '' ?>">
      <span class="nm"><?= e($r['name']) ?></span>
      <span class="track"><i style="width:<?= (int) round((float) $r['score'] / $max * 100) ?>%"></i></span>
      <span class="vl"><?= number_format((float) $r['score'], 3) ?></span>
    </div>
  <?php endforeach; ?>
</div>

<div class="learn-nav">
  <a class="btn gray" href="index.php?p=parenting">다시하기</a>
  <a class="btn" href="index.php">저장</a>
</div>
<p class="hint">저장은 이미 완료되었습니다. 이제 학습하기를 이용할 수 있습니다.</p>
</div>
<?php
render_footer();
