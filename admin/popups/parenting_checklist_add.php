<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$cf_title       = '부모양육태도 체크리스트';
$cf_mode        = 'parenting';
$cf_item_table  = 'parenting_checklist_item';
$cf_score_table = 'parenting_checklist_score';
$cf_item_fk     = 'parenting_checklist_item_id';

require __DIR__ . '/_checklist_form.php';
