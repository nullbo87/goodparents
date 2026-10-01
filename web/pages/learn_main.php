<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/../inc/learn.php';
require_once __DIR__ . '/_rail.php';

$mid = current_member_id();
$lcs = life_cycles();

/* 생애주기 필터 = 다중선택(체크박스).
   - lc 파라미터 없음(신규 진입): 회원 자녀들의 생애주기를 자동 선택 (2자녀면 둘 다)
   - lc=0 또는 전부 해제: 아무것도 선택 안 됨 → 검색하지 않음
   - lc=2,3 : 해당 생애주기들 합집합
   결과 표시 조건: 생애주기가 하나 이상 선택되어 있고, 그 위에 칩을 눌렀거나(lc 파라미터 존재)
   또는 검색어가 2글자 이상일 때. 생애주기가 모두 해제되면 검색어가 있어도 결과를 보여주지 않음.
   그냥 진입(lc·q 없음)만으로는 결과 안 보여줌. 검색어 텍스트 매칭은 아직 안 하고 생애주기 필터만 적용. */
$q = trim(get('q', ''));

$lcRaw = get('lc', null);
if ($lcRaw === null) {
    $sel = array();
    foreach (all("SELECT DISTINCT lc.id, lc.sort_order
                  FROM member_child mc
                  JOIN life_cycle lc ON lc.id = mc.life_cycle_id
                  WHERE mc.member_id = ? AND lc.is_active = 1
                  ORDER BY lc.sort_order, lc.id", array($mid)) as $r) {
        $sel[] = (int) $r['id'];
    }
} else {
    $sel = array();
    foreach (explode(',', $lcRaw) as $p) {
        $n = (int) $p;
        if ($n > 0 && !in_array($n, $sel, true)) { $sel[] = $n; }
    }
}

$doSearch = !empty($sel) && ($lcRaw !== null || mb_strlen($q) >= 2);
$results  = array();
if ($doSearch) {
    $where = 's.is_active = 1 AND lc.is_active = 1';
    $args  = array();
    if ($sel) {
        $where .= ' AND lc.id IN (' . implode(',', array_fill(0, count($sel), '?')) . ')';
        $args   = array_merge($args, $sel);
    }
    $results = all("SELECT s.id, s.title, sc.name AS cat, lc.name AS lc,
                    (SELECT COUNT(*) FROM situation_like sl WHERE sl.situation_id = s.id) AS likes,
                    (SELECT COUNT(*) FROM learning_session ls WHERE ls.situation_id = s.id) AS learners
                    FROM situation s
                    JOIN situation_category sc ON sc.id = s.situation_category_id
                    JOIN life_cycle lc ON lc.id = sc.life_cycle_id
                    WHERE $where
                    ORDER BY lc.sort_order, s.sort_order, s.id", $args);
}

render_header('학습하기', 'learn');
?>
<div class="wrap learn-main-wrap">
<div class="learn-layout">
  <?php render_learn_rail(); ?>

  <div class="search-col">
    <h2 class="section-title">지금 어떤 상황인지 적어주세요</h2>

    <div class="chips">
      <?php foreach ($lcs as $x):
        $xid = (int) $x['id'];
        $on  = in_array($xid, $sel, true);
        $next = $on ? array_values(array_diff($sel, array($xid))) : array_merge($sel, array($xid));
        $href = 'index.php?p=learn&lc=' . ($next ? implode(',', $next) : '0')
              . ($q !== '' ? '&q=' . urlencode($q) : ''); ?>
        <a class="chip <?= $on ? 'on' : '' ?>" href="<?= e($href) ?>"><?= e($x['name']) ?></a>
      <?php endforeach; ?>
    </div>

    <form method="get" action="index.php" class="searchbox">
      <input type="hidden" name="p" value="learn">
      <input type="hidden" name="lc" value="<?= e($sel ? implode(',', $sel) : '0') ?>">
      <textarea name="q" rows="1" placeholder="예: 친구와 놀다가 싸우는 경우"><?= e($q) ?></textarea>
      <button type="submit" class="go" title="검색">+</button>
    </form>
    <?php if ($doSearch): ?>
      <div class="cards mt">
        <?php if (!$results): ?>
          <div class="empty" style="grid-column:1/-1">표시할 상황이 없습니다.</div>
        <?php endif; ?>
        <?php foreach ($results as $r): ?>
          <div class="card">
            <a class="thumb" href="index.php?p=learn_step&sid=<?= (int) $r['id'] ?>&step=1">
              <span class="play">학습하기</span>
            </a>
            <div class="body">
              <div class="cat"><?= e($r['lc']) ?>.<?= e($r['cat']) ?></div>
              <div class="tt"><?= e($r['title']) ?></div>
              <div class="meta">
                <span>&hearts; <?= (int) $r['likes'] ?></span>
                <span>학습 <?= (int) $r['learners'] ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
</div>
<?php
render_footer(array('bare' => true)); /* 학습검색 화면은 풀높이 레이아웃 — 푸터 제외 */
