<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$cf_title       = '아이의 마음읽기';
$cf_mode        = 'situation';
$cf_item_table  = 'situation_checklist_item';
$cf_score_table = 'situation_checklist_score';
$cf_item_fk     = 'situation_checklist_item_id';

require __DIR__ . '/_checklist_form.php';
