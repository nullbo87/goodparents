<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }
require_once __DIR__ . '/../inc/content.php';

render_header('메인화면', '');
?>
<script type="text/javascript" src="./assets/js/jquery/jquery-1.12.4.min.js?ver=20210909"></script>
<script type="text/javascript" src="./assets/js/jquery/jquery-ui.js?ver=20210909"></script>
<link rel="stylesheet" href="./assets/css/swiper.min.css">
<style>

	.swiper-container.mctopban { margin:0px;   width: 100%; height:100%; display:flex;align-items: center; 	justify-content: center;	}
	.mctopban .swiper-wrapper{height:100%;    width: 100%; }
	.mctopban .swiper-slide{ 	position:relative; width:100%; height:100%; margin:0px; background: transparent; 			/* Center slide text vertically */ 		 	display: flex; 		 		justify-content: flex-start; 	 	align-items: center;  overflow:hidden;}
	.mctopban .swiper-slide img { display: block;  width:auto; height:auto;   object-fit: none;}
		

</style>
<section class="hero main">
		<div class="swiper-container mctopban">
			<div class="swiper-wrapper">
				<div class='swiper-slide'>
				  <div class="cont">
					<h2><?= nl2br(e($GP_HERO['title'])) ?><span><?= nl2br(e($GP_HERO['subtitle'])) ?></span></h2>	
					<a class="btn" href="<?= e($GP_HERO['cta_href']) ?>"><?= e($GP_HERO['cta']) ?></a>
				  </div>
				  <div class="photo"><img src= "./assets/img/hero-timg2.png" title=""></div>
				</div>
				<div class='swiper-slide'  >
				  <div class="cont">
					<h2><?= nl2br(e($GP_HERO['title2'])) ?><span><?= nl2br(e($GP_HERO['subtitle2'])) ?></span></h2>
					<div class="desc"><?= nl2br(e($GP_HERO['descrtion2'])) ?></div>
					<a class="btn" href="<?= e($GP_HERO['cta_href']) ?>"><?= e($GP_HERO['cta2']) ?></a>
				  </div>
				  <div class="photo"><img src= "./assets/img/hero-timg.png" title=""></div>
				</div>
 	
				</div>
				<div class="swiper-button-prev"><span class="ti-angle-left"></span></div>					
				<div class="swiper-button-next"><span class="ti-angle-right"></span></div>
			
			</div>

		</div>
</section>

<script type="text/javascript"  src="./assets/js/swiper.min.js"></script>
		<script>
			var swiper = new Swiper(".mctopban", {
				loop:true,
				spaceBetween: 0,
				effect: "fade",
				  centeredSlides: true,
				  autoplay: {
					delay: 5000,
					disableOnInteraction: false,
				  },					
				navigation: {
					nextEl: ".swiper-button-next",
					prevEl: ".swiper-button-prev",
				},
			});
 
		</script>




<div class="wrap">
	<article class="article about-body">
	  <h3><?= e($GP_ABOUT['title']) ?></h3>
	  <?= $GP_ABOUT['html'] ?>
	</article>
</div>
<?php
render_footer();
