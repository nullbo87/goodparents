<?php
if (!defined('GP_ADMIN')) { http_response_code(403); exit('No direct access'); }

$err = '';
if (is_post()) {
    csrf_check();
    if (attempt_login(post('pw'))) { redirect('index.php'); }
    $err = '비밀번호가 올바르지 않습니다.';
}
if (is_logged_in()) { redirect('index.php'); }

header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>로그인 · <?= e(APP_TITLE) ?></title>
<link rel="stylesheet" href="assets/style.css?v=10">
</head>
<body class="login-body">
<form class="login-box" method="post" action="index.php?p=login">
    <div class="login-logo">로고</div>
    <h1><?= e(APP_TITLE) ?><br><small>관리자</small></h1>
    <?php if ($err !== ''): ?><div class="flash flash-err"><?= e($err) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label>비밀번호</label>
    <input type="password" name="pw" autofocus required autocomplete="current-password">
    <button type="submit" class="primary">로그인</button>
</form>
</body>
</html>
