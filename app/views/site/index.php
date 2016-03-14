<?php
use \config\App;

$role = 1;

$title = 'Главная';

$linkAddPrice = App::url(['site/add-price']);
$linkCreate   = App::url(['universal/create-handler']);
$linkSearch   = App::url(['search/index']);
$linkLogin    = App::url(['security/login', 'e'=>'0']);
$linkCatalog  = App::url(['catalog/index']);
$linkCourse   = App::url(['catalog/course']);
$linkAdmin    = App::url(['admin/index']);
?>
<h1>Добро пожаловать</h1>
<h2 style="margin-top: -30px">в редактор базы данных mnogoshin.com.ua</h2>
<hr/>
<h3>Сейчас Вы хотите...</h3>
<div id="selectItems">
    <ul>
        <? if ($role == 1) echo "<li><a href='{$linkAddPrice}'>Добавить прайс</a></li>"; ?>
        <? if ($role == 1) echo "<li><a href='{$linkCreate}'>Создать обработчик</a></li>"; ?>
        <li><a href="<?=$linkSearch ?>">Поиск в Базе данных</a></li>
        <? if ($role == 1) echo "<li><a href='{$linkCatalog}'>Редактировать справочники</a></li>"; ?>
        <? if ($role == 1) echo "<li><a href='{$linkCourse}'>Изменить курс</a></li>"; ?>
    </ul>
</div>
<div id="selectItems" style="border-top: none;">
    <ul>
        <? if ($role == 1) echo "<li><a href='{$linkAdmin}'>Админка</a></li>"; ?>
        <li><a href="<?=$linkLogin ?>" style="color: crimson;">ВЫХОД</a></li>
    </ul>
</div>