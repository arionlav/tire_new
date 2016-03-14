<?php
use config\App;

$title = 'Удалить пользователя';
?>
<h1>Изменить статус пользователей</h1>
<hr/>
<div class="form">
    <form id="frmLogin" action="<?=App::url(['admin/change-role'])?>" method="post">
        <div>
            <label for="idList">Логин:</label>
            <select name="login" id="idList">
                <?
                foreach ($users as $user) {
                    echo "<option value='" . $user["login"] . "'>" . $user["login"] . "</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label for="idRole">Назначить статус: </label>
            <select name="idRole" id="idRole">
                <?
                foreach ($roleArray as $ra) {
                    echo "<option value='" . $ra["idRole"] . "'>" . $ra["nameRole"] . "</option>";
                }
                ?>
            </select>
        </div>
        <div id="buttonDiv">
            <input id="submit" type="submit" value="Изменить">
        </div>
    </form>
</div>

<div class="returnMessage"><?=$returnMsg ?></div>
