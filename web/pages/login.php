<?php
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

$err = '';
if (is_post()) {
    csrf_check();
    if (attempt_login(post('login_id'), post('login_pw'))) {
        $to = !empty($_SESSION['after_login']) ? $_SESSION['after_login'] : 'index.php';
        unset($_SESSION['after_login']);
        redirect($to);
    }
    $err = '아이디 또는 비밀번호가 올바르지 않습니다.';
}
if (is_logged_in()) { redirect('index.php'); }

render_header('로그인', '');
?>
<div class="wrap">
	<div class="authbox">
	  <h2 class="section-title">좋은 부모 배움터</h2>
		<div class="loginbox">
		  <form method="post" >
			<?= csrf_field() ?>
			<div class="form-row"><label>아이디</label>
			  <input type="text" name="login_id" value="goodparents" autocomplete="username"></div>
			<div class="form-row"><label>비밀번호</label>
			 <input type="password" name="login_pw" autocomplete="current-password"></div>
			<button type="submit" class="btn block">로그인</button>
		  </form>
		<div class="snswr">
			  <p class="tit"><span>SNS 계정으로 로그인</span></p>
			   <?php render_sns_buttons(); ?>  
			  <?php if ($err !== ''): ?><div class="flash flash-err"><?= e($err) ?></div><?php endif; ?>
		</div>
		 <div class="joinwr">
			<p class="tit">아직 회원이 아니신가요?</p>
			 <div class="btnwr"><a href="index.php?p=signup">회원가입</a></div>
		 </div>
	  </div>
	</div>
</div>
<?php
render_footer();
