<?php
use config\App;

$title = 'Удалить пользователя';
?>
<h1>Удалить пользователя</h1>
<hr/>
<div class="form">
    <form id="frmLogin" action="<?=App::url(['admin/delete-user']) ?>" method="post">
        <div>
            <label for="idList">Логин: </label>
            <select name="login" id="idList">
            <?
            foreach ($users as $user) {
                echo "<option value='" . $user["login"] . "'>" . $user["login"] . '</option>';
            };
            ?>
            </select>
        </div>
        <div id="buttonDiv">
            <input id="submit" type="submit" value="Удалить">
        </div>
    </form>
</div>

<div class="returnMessage"><?=$returnMsg ?></div>
