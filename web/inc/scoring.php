<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/db.php';

/* =========================================================
 *  체크리스트 채점 엔진 (상황별 · 부모양육태도 공용)
 *
 *  문항 유형별 한 문항의 기여:
 *   - scale  : (선택단계 / 단계수) x 양육태도별 가중치(%)  / 100
 *   - choice : 선택한 보기의 양육태도별 가중치(%)          / 100
 *   - essay  : 0 (추후 AI 분석)
 *
 *  가중치 합은 문항(scale) 또는 보기(choice) 단위로 100%.
 *  결과 = 양육태도별 누적합 -> 상위 N개.
 * ========================================================= */

/* scope: 'situation' | 'self' | 'parenting' */
function scoring_tables($scope) {
    if ($scope === 'parenting') {
        return array(
            'item'    => 'parenting_checklist_item',
            'score'   => 'parenting_checklist_score',
            'item_fk' => 'parenting_checklist_item_id',
        );
    }
    if ($scope === 'self') {
        return array(
            'item'    => 'situation_selfcheck_item',
            'score'   => 'situation_selfcheck_score',
            'item_fk' => 'situation_selfcheck_item_id',
        );
    }
    return array(
        'item'    => 'situation_checklist_item',
        'score'   => 'situation_checklist_score',
        'item_fk' => 'situation_checklist_item_id',
    );
}

/* 저장된 응답으로부터 양육태도별 점수 계산 -> array(attitude_id => float score) */
function score_submission($submission_id) {
    $submission_id = (int) $submission_id;
    $sub = one('SELECT * FROM checklist_submission WHERE id = ?', array($submission_id));
    if (!$sub) { return array(); }
    $t = scoring_tables($sub['scope']);

    $attitudes = all('SELECT id FROM parenting_attitude WHERE is_active = 1');
    $totals = array();
    foreach ($attitudes as $a) { $totals[(int) $a['id']] = 0.0; }

    $responses = all('SELECT * FROM checklist_response WHERE submission_id = ?', array($submission_id));

    foreach ($responses as $r) {
        $item = one("SELECT * FROM {$t['item']} WHERE id = ?", array((int) $r['item_id']));
        if (!$item) { continue; }
        $type = isset($item['answer_type']) ? $item['answer_type'] : 'scale';

        if ($type === 'essay') {
            continue;

        } elseif ($type === 'choice') {
            $oid = (int) $r['option_id'];
            if (!$oid) { continue; }
            foreach (all('SELECT parenting_attitude_id AS aid, percentage AS p
                          FROM checklist_option_score WHERE option_id = ?', array($oid)) as $s) {
                $aid = (int) $s['aid'];
                if (isset($totals[$aid])) { $totals[$aid] += ((float) $s['p']) / 100.0; }
            }

        } else { /* scale */
            $steps = max(1, (int) (isset($item['scale_steps']) ? $item['scale_steps'] : 5));
            $val   = (int) $r['scale_value'];
            if ($val < 1) { continue; }
            if ($val > $steps) { $val = $steps; }
            $factor = $val / $steps;                       /* 1/N .. N/N */
            foreach (all("SELECT parenting_attitude_id AS aid, percentage AS p
                          FROM {$t['score']} WHERE {$t['item_fk']} = ?", array((int) $item['id'])) as $s) {
                $aid = (int) $s['aid'];
                if (isset($totals[$aid])) { $totals[$aid] += $factor * (((float) $s['p']) / 100.0); }
            }
        }
    }
    return $totals;
}

/* 상위 N개 양육태도 -> array of array('id','name','score') 내림차순 */
function top_attitudes($totals, $n = 2) {
    arsort($totals);
    $out = array();
    foreach ($totals as $aid => $score) {
        $a = one('SELECT id, name FROM parenting_attitude WHERE id = ?', array((int) $aid));
        if (!$a) { continue; }
        $out[] = array('id' => (int) $aid, 'name' => $a['name'], 'score' => round($score, 4));
        if (count($out) >= $n) { break; }
    }
    return $out;
}

/* 채점 결과를 checklist_result 에 저장(재계산 시 덮어쓰기) */
function save_submission_result($submission_id) {
    $submission_id = (int) $submission_id;
    $totals = score_submission($submission_id);
    q('DELETE FROM checklist_result WHERE submission_id = ?', array($submission_id));
    foreach ($totals as $aid => $score) {
        q('INSERT INTO checklist_result (submission_id, parenting_attitude_id, score)
           VALUES (?, ?, ?)', array($submission_id, (int) $aid, round($score, 4)));
    }
    return $totals;
}
