<?php
use config\App;

$title = 'Изменение курса';
?>
<h3 class="courseH3">Введите текущий курс гривны к доллару</h3>
<div class="courseContainer">
    <p><em>Сейчас курс в базе: <br/> <span class="courseNow"><?=$courseNow ?></span></em></p>
    <p class="courseAttention">
        <em>ВНИМАНИЕ!</em>
        <em>При изменении курса произойдет пересчет прайсов, которые добавлялись в USD</em>
    </p>
    <div class="formCatalog searchRowAll">
        <form action="<?= App::url(['catalog/course']) ?>" method="post">
            <input type="text" name="course" class="inputText"/>
            <input type="submit" class="courseButton" value="Изменить курс"/>
        </form>
    </div>
</div>
