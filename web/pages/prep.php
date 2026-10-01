<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/../inc/content.php';

$sub = route_key(get('sub', 'ready'), 'ready');
if (!isset($GP_PREP[$sub])) { $sub = 'ready'; }
$a = $GP_PREP[$sub];

render_header($a['title'], 'prep');
?>
<div class="hero">
  <div class="cont"><h2><?= e($a['title']) ?></h2></div>
  <div class="photo"><img src= "./assets/img/prep-timg.png" title=""></div>
</div>
<div class="wrap">
	<div class="split mt">
	  <?= prep_subnav($sub) ?>
	  <article class="article about-body" style="margin-top:0">
		<h3 class="title"><span><?= e($a['title']) ?></span></h3>
		<?= $a['html'] ?>
	  </article>
	</div>
</div>
<?php
render_footer();
