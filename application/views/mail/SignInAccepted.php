<?php
/**
 * @var Users $user
 */
?>
<html>
<body>

<h1>Добро пожаловать!</h1>

<p>Запрос на регистрацию аккаунта подтвержден.</p>

Для <a href="http://tt.ttarget.ru/">входа</a> используйте вашу почту <?= CHtml::encode($user->email); ?> и пароль указанный при регистрации.
</body>
</html>