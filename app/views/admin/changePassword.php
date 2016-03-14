<?php
use config\App;

$title = 'Изменение пароля';
?>
<h1>Изменение пароля</h1>
<hr/>
<div class="form">
    <form id="frmLogin" action="<?=App::url(['admin/change-password']) ?>" method="post">
        <div>
            <label for="oldPass">Введите старый пароль: </label>
            <input id="oldPass" class="formInputTextPriceChange" type="password" name="oldPass"/>
        </div>
        <div>
            <label for="newPass">Введите новый пароль: </label>
            <input id="newPass" class="formInputTextPriceChange" type="password" name="newPass" />
        </div>
        <div>
            <label for="confirmNewPass">Повторите новый пароль: </label>
            <input id="confirmNewPass" class="formInputTextPriceChange" type="password" name="confirmNewPass" />
        </div>
        <div id="buttonDiv">
            <input id="submit" type="submit" value="Изменить">
        </div>
    </form>
</div>

<div class="returnMessage"><?=$returnMsg ?></div>
