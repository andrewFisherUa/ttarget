<? $this->layout = false; ?>
<!DOCTYPE html>
<html>
<head>
    <title>tTarget</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&subset=latin,cyrillic' rel='stylesheet'
          type='text/css'>
    <link href="/css/loginpage.css" rel="stylesheet" media="screen">

    <script src="/s/wm/jquery-1.4.3.min.js"></script>
    <script src="/s/wm/jquery.data.js"></script>
    <script src="/s/wm/jquery.watermark.min.js"></script>
</head>
<body>
<div class="page-header">
    <div class="page-header-inner">
        <div id="site-login">
            <form method="POST" action="/login">
                <input type="text" value="Логин" id="login" name="LoginForm[email]" placeholder="Логин">
                <input type="password" id="password" value="Пароль" name="LoginForm[password]" placeholder="Пароль">
                <button type="submit" value="" name="yt0">Войти</button>
            </form>
        </div>
        <h1 id="site-title">
            tTarget
        </h1>

        <div id="site-contact">
            <div>+7 (495) 783-4363, +7 (495) 783-4362</div>
            <div id="site-contact-micro">105082, г. Москва, ул. Бакунинская, д.69, стр.1</div>
        </div>
    </div>
</div>
<div class="container">

    <div class="page-content">
        <div class="promo clearfix">
            <div class="promo-block">
                <h3 class="promo-title">Селективность</h3>
                Заголовки статей размещаемые на более чем 300 популярных интернет ресурсах написаны так, что интересны и
                привлекают только целевую аудиторию с запросами совпадающими с тематикой сайта.
            </div>
            <div class="promo-block">
                <h3 class="promo-title">Эффективность</h3>
                Специально созданный для сайта контент вовлекает потребителя, формирует у него осознанную необходимость
                купить продукт представленный на сайте.
            </div>
            <div class="promo-block">
                <h3 class="promo-title">Прозрачность</h3>
                Во время проведения кампании на сайте устанавливаются счетчики статистики: глубины просмотра,
                длительности, и.т.д, что позволяет постоянно контролировать ход кампании.
            </div>
        </div>

        <p>Мозг человека устроен так, чтобы постоянно фильтровать поступающую информацию по принципу, нужно, не нужно. В
            современном обществе с переизбытком информации эти фильтры доведены до совершенства. Но в тоже время
            актуальные потребности человека требуют поиска необходимой информации. Взгляд человека мгновенно цепляется
            за ключевые слова, внимание сосредотачивается и мозг дает осознанную команду, перейти по ссылке к получению
            информации. Осознаний переход подтверждает, что данная информация нужна сейчас. Дальше происходит анализ
            полученной информации на предмет достоверности. Самая главная задача, которую решает потребитель – задача
            выбора, кому продукту отдать предпочтение. Традиционные агрессивные рекламные тексты сразу опускают индекс
            доверия, так как жизненный опыт подсказывает, что в действительности все будет не так как обещают. Более
            сдержанные информативные тексты, где реклама продукта «обернута» условно независимыми мнениями экспертов
            дают больше убеждения и лучше запоминаются</p>
    </div>

    <div class="page-footer">
        Copyright © 2013 by (c) tTarget.<br/>
        All Rights Reserved.
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $('#password').watermark('Пароль', {useNative: false});
        $('#login').watermark('Логин', {useNative: false});
    });
</script>
</body>
</html>
