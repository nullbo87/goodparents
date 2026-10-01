<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$cf_title          = '나의 마음 알아보기';
$cf_mode           = 'self';
$cf_situation_scoped = true;
$cf_item_table     = 'situation_selfcheck_item';
$cf_score_table    = 'situation_selfcheck_score';
$cf_item_fk        = 'situation_selfcheck_item_id';

require __DIR__ . '/_checklist_form.php';
