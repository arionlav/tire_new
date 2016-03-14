<?php
use config\App;

$title = 'Добавление пользователя';
?>
<h1>Добавление пользователя</h1>
<hr/>
<div class="form">
    <form action="<?=App::url(['admin/add-user'])?>" method="post">
        <div>
            <label for="txtLogin">Логин: </label>
            <input id="txtLogin" class="formInputTextPriceChange" type="text" name="login"/>
        </div>
        <div>
            <label for="txtPassword">Пароль: </label>
            <input id="txtPassword" class="formInputTextPriceChange" type="password" name="password" />
        </div>
        <div>
            <?
            echo '<label>Статус: </label><select name="idRole" id="idList">';
            foreach ($roleArray as $arr) {
                echo "<option value='" . $arr["idRole"] . "'>" . $arr["nameRole"];
                echo "</option>";
            };
            echo "</select>";
            ?>
        </div>
        <div id="buttonDiv">
            <input id="submit" type="submit" value="Добавить">
        </div>
    </form>
</div>

<div class="returnMessage"><?=$returnMsg ?></div>
