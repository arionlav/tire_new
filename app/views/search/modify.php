<?php
use config\App;
?>
<h2>Редактируем запись №<span><?=$id?></span></h2>
<form action="<?=App::url(['search/modify-success']) ?>" method="post">

    <input type="hidden" name="id" value="<?= $id ?>"/>
    <input type="hidden" name="page" value="<?= $page ?>"/>
    <input type="hidden" name="pageDatatables" value="<?= $pageDatatables ?>"/>
    <input type="hidden" name="idList" value="<?= $tire['idList'] ?>"/>

    <div class='modifyCloseButton'></div>
    <div class='modifyCloseButtonHover'></div>

    <label id="str">Исходная строка</label>
    <input style="width: 835px" type="text" name="str" value="<?= $tire['str'] ?>"/>

    <div id="reductorDivLeft">
        <label>Имя прайса</label>
        <input type="text" name="nameList" value="<?= $tire['nameList'] ?>" disabled/>

        <label for="idRadius">Радиус</label>
        <select name="idRadius" id="idRadius">
            <? foreach ($items['radius'] as $arrKey => $arrVal) {
                ($tire['radius'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>
        <label for="idHeight">Высота</label>
        <select name="idHeight" id="idHeight">
            <? foreach ($items['height'] as $arrKey => $arrVal) {
                ($tire['nameHeight'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="idWidth">Ширина</label>
        <select name="idWidth" id="idWidth">
            <? foreach ($items['width'] as $arrKey => $arrVal) {
                ($tire['nameWidth'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="idIndexSpeed">Индекс скорости</label>
        <select name="idIndexSpeed" id="idIndexSpeed">
            <? foreach ($items['indexSpeed'] as $arrKey => $arrVal) {
                ($tire['nameIndexSpeed'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="idIndexPower">Индекс нагрузки</label>
        <select name="idIndexPower" id="idIndexPower">
            <? foreach ($items['indexPower'] as $arrKey => $arrVal) {
                ($tire['nameIndexPower'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="idBrand">Бренд</label>
        <select name="idBrand" id="idBrand">
            <? foreach ($items['brand'] as $arrKey => $arrVal) {
                ($tire['nameBrand'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="model">Модель</label>
        <input type="text" name="model" id="model" value="<?= $tire['model'] ?>"/>
    </div>

    <div  id="reductorDivRight">
        <label id="camera" for="idCamera">Камерность</label>
        <select name="idCamera" id="idCamera">
            <?foreach ($items['camera'] as $arrKey => $arrVal) {
                ($tire['nameCamera'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="idSeason">Сезон</label>
        <select name="idSeason" id="idSeason">
            <? foreach ($items['season'] as $arrKey => $arrVal) {
                ($tire['nameSeason'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="isIt">Наличие</label>
        <input type="text" name="isIt" id="isIt" value="<?= $tire['isIt'] ?>"/>

        <label for="price">Цена</label>
        <input type="text" name="price" id="price" value="<?= $tire['price'] ?>"/>

        <label for="idMoney">Валюта</label>
        <select name="idMoney" id="idMoney">
            <?foreach ($items['money'] as $arrKey => $arrVal) {
                ($tire['nameMoney'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="idGroup">Группа</label>
        <select name="idGroup" id="idGroup">
            <? foreach ($items['group'] as $arrKey => $arrVal) {
                ($tire['nameGroup'] == $arrVal) ? $selected = ' selected' : $selected = '';
                echo "<option value='" . $arrKey . "'$selected>" . $arrVal;
                echo "</option>";
            } ?>
        </select>

        <label for="dateTime">Дата изменения</label>
        <input type="text" name="dateTime" id="dateTime" value="<?= $tire['dateTime'] ?>" disabled/>

        <label for="other">Примечания</label>
        <input type="text" name="other" id="other" value="<?= $tire['other'] ?>"/>
    </div>
    <input type="submit" value="Применить изменения">
    <div class="dropRowDiv">
        <a href="<?=App::url(['search/delete-row', 'id' => $id, 'pageDatatables' => $pageDatatables, 'page' => $page]) ?>">
            Удалить позицию
        </a>
    </div>
</form>
