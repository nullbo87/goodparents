<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$id  = int_or_zero(get('id'));
$row = $id ? one('SELECT * FROM scale_label_template WHERE id = ?', array($id)) : null;

$name   = $row ? $row['name'] : '';
$steps  = $row ? (int) $row['steps'] : 5;
$labels = $row ? explode('|', $row['labels']) : array();
$err    = '';

if (is_post()) {
    csrf_check();
    $name  = trim(post('name'));
    $steps = clamp_int(post('steps', 5), 2, 20);
    $labels = array();
    for ($i = 1; $i <= $steps; $i++) {
        $labels[] = trim(post('lb_' . $i));
    }
    $filled = array_filter($labels, function ($v) { return $v !== ''; });

    if ($name === '') {
        $err = '템플릿 이름을 입력하세요.';
    } elseif (count($filled) !== $steps) {
        $err = '모든 단계의 라벨을 입력하세요. (' . $steps . '개 필요, ' . count($filled) . '개 입력됨)';
    }
    if ($err === '') {
        if ($id) {
            q('UPDATE scale_label_template SET name = ?, steps = ?, labels = ? WHERE id = ?',
              array($name, $steps, implode('|', $labels), $id));
        } else {
            $ord = (int) col('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM scale_label_template');
            q('INSERT INTO scale_label_template (name, steps, labels, sort_order) VALUES (?, ?, ?, ?)',
              array($name, $steps, implode('|', $labels), $ord));
        }
        popup_done();
    }
}

popup_header($id ? '척도 라벨 템플릿 수정' : '척도 라벨 템플릿 추가');
if ($err !== '') { echo '<div class="flash flash-err">' . e($err) . '</div>'; }
?>
<form method="post" class="pform" id="tplForm">
    <?= csrf_field() ?>
    <label>템플릿 이름</label>
    <input type="text" name="name" value="<?= e($name) ?>" required autofocus placeholder="예: 5단계 기본">

    <label>단계수</label>
    <input type="number" name="steps" id="tplSteps" min="2" max="20" value="<?= e($steps) ?>" style="max-width:120px">

    <label>단계별 라벨 <span class="hint" style="margin:0">(1단계=가장 낮은 쪽부터)</span></label>
    <div id="tplLabels"></div>

    <div class="pbtns">
        <button type="button" onclick="window.close()">닫기</button>
        <button type="submit" class="primary">저장</button>
    </div>
</form>

<script>
(function () {
    var stepsIn = document.getElementById('tplSteps');
    var box = document.getElementById('tplLabels');
    var initial = <?= json_encode(array_values($labels)) ?>;
    function render() {
        var n = Math.max(2, Math.min(20, parseInt(stepsIn.value, 10) || 5));
        var cur = Array.prototype.map.call(box.querySelectorAll('input'), function (i) { return i.value; });
        if (!cur.length) { cur = initial; }
        box.innerHTML = '';
        for (var i = 1; i <= n; i++) {
            var lb = document.createElement('label');
            lb.textContent = i + '단계 라벨';
            var inp = document.createElement('input');
            inp.type = 'text'; inp.name = 'lb_' + i; inp.value = cur[i - 1] || '';
            box.appendChild(lb); box.appendChild(inp);
        }
    }
    stepsIn.addEventListener('input', render);
    render();
})();
</script>
<?php popup_footer(); ?>
