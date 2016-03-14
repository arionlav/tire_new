<?php
use \config\App;

switch ($params['e']) {
    case 1: $errorMsg   = 'У Вас нет привилегий для просмотра данной страницы. <br/>Пожалуйста, авторизируйтесь'; break;
    case 2: $errorMsg   = 'Неправильный логин или пароль. Поробуйте еще раз'; break;
    case 3: $errorMsg   = 'Логин может состоять только из букв английского алфавита и цифр'; break;
    case 4: $errorMsg   = 'Логин должен быть не меньше 3-х символов и не больше 30'; break;
    case 5: $errorMsg   = 'Время сессии истекло, пожалуйста, войдите еще раз'; break;
    case 6: $errorMsg   = 'Произошла системная ошибка. Пожалуйста, сообщите в техподдержку: asqwas@i.ua'; break;
    default: $errorMsg  = '';
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Tire</title>
    <link rel="stylesheet" href="../css/style.css" type="text/css" />
    <!-- JS
    ================================================== -->
    <script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
</head>
<body>
<h1>Авторизация</h1>
<hr/>
<div class="form">
    <form id="frmLogin" action="<?=App::url(['security/login', 'e'=>0]) ?>" method="post">
        <div>
            <label for="txtLogin">Ваш логин</label>
            <input id="txtLogin" type="text" name="login" class="formInputTextPriceChange" value='<?=$login ?>'/>
        </div>
        <div>
            <label for="txtPassword">Ваш пароль</label>
            <input id="txtPassword" type="password" class="formInputTextPriceChange" name="password" />
        </div>
        <div id="buttonDiv">
            <input id="submit" type="submit" value="Войти">
        </div>
    </form>
</div>

<div class="returnMessage"><div id="errorMessage"><?=$errorMsg ?></div></div>
</body>
</html>
