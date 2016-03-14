<?php
use config\App;

$title = 'Панель администратора';
?>
<h1>Панель администратора</h1>
<hr/>
<h3>Сейчас Вы хотите...</h3>
<div id="selectItems">
    <ul>
        <li><a href="<?=App::url(['admin/add-user']) ?>">Добавить пользователя</a></li>
        <li><a href="<?=App::url(['admin/change-password']) ?>">Изменить свой пароль</a></li>
        <li><a href="<?=App::url(['admin/delete-user']) ?>">Удалить пользователя</a></li>
        <li><a href="<?=App::url(['admin/change-role']) ?>">Изменить статус пользователя</a></li>
    </ul>
</div>
