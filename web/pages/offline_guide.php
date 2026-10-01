<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/_meetings.php';
render_meeting_page('guide', '오프라인 교육안내', 'offline');
