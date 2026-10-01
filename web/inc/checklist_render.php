<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

/* 체크리스트 문항 하나(척도/다지선다·OX/서술형)를 사용자 화면에 렌더링
 * $scope: 'situation' | 'parenting'   $prev: 이전 응답(checklist_response) 행 또는 null */
function checklist_load_options($scope, $item_id) {
    return all('SELECT * FROM checklist_item_option WHERE scope = ? AND item_id = ? ORDER BY sort_order, id',
               array($scope, (int) $item_id));
}

function checklist_option_image_src($url) {
    $url = (string) $url;
    if ($url === '') { return ''; }
    if (strpos($url, 'uploads/') === 0) { return '../admin/' . $url; }
    return $url;
}

/* 템플릿의 단계수가 문항의 scale_steps 와 다르면 사용하지 않음(양끝 라벨로 대체) */
function scale_template_labels($template_id, $steps) {
    $template_id = (int) $template_id;
    if (!$template_id) { return null; }
    $t = one('SELECT steps, labels FROM scale_label_template WHERE id = ?', array($template_id));
    if (!$t || (int) $t['steps'] !== (int) $steps) { return null; }
    return explode('|', $t['labels']);
}

function render_checklist_question($scope, $it, $n, $prev = null, $essayPlaceholder = '자유롭게 작성해 주세요') {
    $iid  = (int) $it['id'];
    $type = isset($it['answer_type']) ? $it['answer_type'] : 'scale';

    echo '<div class="q">';
    echo '<div class="qtt">' . (int) $n . '. ' . e($it['question']) . '</div>';

    if ($type === 'scale') {
        $steps = max(1, (int) (isset($it['scale_steps']) ? $it['scale_steps'] : 5));
        $tplLabels = scale_template_labels(isset($it['scale_template_id']) ? $it['scale_template_id'] : 0, $steps);
        echo '<div class="scale">';
        for ($k = 1; $k <= $steps; $k++) {
            $checked = ($prev && (int) $prev['scale_value'] === $k) ? 'checked' : '';
            echo '<label class="pt"><input type="radio" name="s_' . $iid . '" value="' . $k . '" ' . $checked . '>';
            if ($tplLabels) { echo '<span class="pt-lb">' . e($tplLabels[$k - 1]) . '</span>'; }
            echo '</label>';
        }
        echo '</div>';
        if (!$tplLabels) {
            echo '<div class="scale-ends"><span>잘못하고 있음</span><span>매우 잘하고 있음</span></div>';
        }

    } elseif ($type === 'choice') {
        $opts  = checklist_load_options($scope, $iid);
        $style = (isset($it['display_style']) && $it['display_style'] === 'ox') ? 'ox' : 'list';
        if (!$opts) {
            echo '<p class="hint">보기가 등록되지 않았습니다.</p>';
        } elseif ($style === 'ox') {
            echo '<div class="ox-row">';
            foreach ($opts as $op) {
                $checked = ($prev && (int) $prev['option_id'] === (int) $op['id']) ? 'checked' : '';
                $img = checklist_option_image_src($op['image_url']);
                echo '<label class="ox-opt"><input type="radio" name="o_' . $iid . '" value="' . (int) $op['id'] . '" ' . $checked . '>';
                if ($img !== '') { echo '<img src="' . e($img) . '" alt="">'; }
                echo '<span class="ox-tx">' . e($op['label']) . '</span></label>';
            }
            echo '</div>';
        } else {
            foreach ($opts as $op) {
                $checked = ($prev && (int) $prev['option_id'] === (int) $op['id']) ? 'checked' : '';
                $img = checklist_option_image_src($op['image_url']);
                echo '<label class="choice">';
                echo '<input type="radio" name="o_' . $iid . '" value="' . (int) $op['id'] . '" ' . $checked . '>';
                if ($img !== '') { echo '<img class="opt-thumb" src="' . e($img) . '" alt="">'; }
                echo '<span>' . e($op['label']) . '</span></label>';
            }
        }

    } else { /* essay */
        echo '<textarea name="e_' . $iid . '" rows="3" placeholder="' . e($essayPlaceholder) . '">'
           . e($prev ? $prev['essay_text'] : '') . '</textarea>';
    }

    echo '</div>';
}
