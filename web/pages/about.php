<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/../inc/content.php';

render_header('좋은 부모교육 이란?', 'about');
?>
<div class="hero about">
  <div class="cont"><h2><?= e($GP_ABOUT['title']) ?></h2></div>
  <div class="photo"><img src= "./assets/img/about-timg.png" title=""></div>
</div>
<div class="wrap">
	<article class="article about-body">
	  <?= $GP_ABOUT['html'] ?>
	</article>
</div>
<?php
render_footer();
