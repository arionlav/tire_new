<?php

use config\App;

$listArrIndex = [];
foreach ($lists as $arr) {
    $listArrIndex[$arr['idList']] = $arr['nameList'];
}
?>
<script type="text/javascript">
    var arrIndex = []; // array from php to js
    <? $js_array = json_encode($listArrIndex); echo "arrIndex = ". $js_array . ";\n"; ?>
</script>

<script type="text/javascript" src="../js/createHandler.js"></script>

<script type="text/javascript" src="../js/universalScript.js"></script>

<h2 style="margin: 50px 0 10px 0">Задайте параметры для нового обработчика</h2>
<div class="universalFullContainer">
<form action="<?= App::url(['universal/create-handler-success']) ?>" enctype="multipart/form-data" method="post">
    <div class="hr"></div>

    <!-- Начальные параметры -->
    <div id="startParams">
        <h4 style="margin-bottom: 10px;">Начальные параметры</h4>
        <label for="listName">Введите название прайса
            <span style="color: red;" class="displayNone" id="listNameError">
                Такой прайс уже есть! Зайдайте другое имя, иначе обработчик не будет создан.
            </span>
        </label>

        <input type="text" name="listName" id="listName" class="formInputText"/>

        <label for="listCity">Он находится в городе...</label>
        <input type="text" name="listCity" id="listCity" class="formInputText"/>

        <label for="pageName">Имя листа в файле:<br/>
            <span>Если в файле несколько листов, укажите какой следует обработать.<br/>
            Если лист один, оставьте поле пустым</span></label>
        <input type="text" name="pageName" id="pageName" class="formInputText"/>

        <label for="startValue">С какой строки начинаются значения?<br/>
            <span>Укажите номер строки, где начинаются параметры шин<br/>
            Это действие удалит ненужную шапку документа</span></label>
        <input type="text" name="startValue" id="startValue" class="formInputText"/>
    </div>
    <div class="hr"></div>

    <!-- Исходная строка -->
    <h4 style="margin-bottom: 10px;">Исходная строка</h4>
    <label for="str">Исходная строка (столбец)<br/>
        <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит исходную строку<br/>
        Если исходной строки нет, оставьте поле пустым, она будет собрана из найденых параметров</span></label>
    <input type="text" name="str" id="str" class="formInputText"/>

    <label for="firstWordInStartStr">Первое слово в исходной строке, если есть<br/>
        <span>Если в исходной строке есть первое слово у ВСЕХ шин, <br/>
        например "Шина" или "Автошина", укажите его</span></label>
    <input type="text" name="firstWordInStartStr" id="firstWordInStartStr" class="formInputText"/>

    <div class="hr"></div>

    <!-- Ширина -->
    <h4>Ширина</h4>
    <div class="inputRadio">
        <input type="radio" name="widthIs" id="widthIsRow" value="widthIsRow" checked/>
        <label for="widthIsRow" class="labelRadio">Ширина в столбце</label>
        <input type="radio" name="widthIs" id="widthIsStr" value="widthIsStr"/>
        <label for="widthIsStr">Ширина в исходной строке</label>
    </div>
    <div class="hrSecond"></div>
    <div id="widthIsRowDiv">
        <label for="widthRow">Номер или буква столбца с шинирой<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит ширину</span></label>
        <input type="text" name="widthRow" id="widthRow" class="formInputText"/>
        <input type="checkbox" name="widthSearchStrToo" id="widthSearchStrToo"  style="float: left;"/>
        <label for="widthSearchStrToo" style="margin: 11px 0 0 10px;">
            Если не найдем в столбце, попробовать поискать в исходной строке
        </label>
    </div>
    <div id="widthIsStrDiv" class="displayNone">
        <div class="inputRadio">
            <input type="radio" name="widthIsStrIn" id="widthAfterWord" value="widthAfterWord"/>
            <label for="widthAfterWord" class="labelRadio">
                У Вас строка вида: <em>"Шина<strong> 215</strong>/65R16 ..."</em><br/>
                Ширина - это ПЕРВОЕ число в строке и не важно что идет до, а что после
            </label>
        </div>
    </div>

    <div class="hr"></div>

    <!-- Высота -->
    <h4>Высота</h4>
    <div class="inputRadio">
        <input type="radio" name="heightIs" id="heightIsRow" value="heightIsRow" checked/>
        <label for="heightIsRow" class="labelRadio">Высота в столбце</label>
        <input type="radio" name="heightIs" id="heightIsStr" value="heightIsStr"/>
        <label for="heightIsStr">Высота в исходной строке</label>
    </div>
    <div class="hrSecond"></div>
    <div id="heightIsRowDiv">
        <label for="heightRow">Номер или буква столбца с высотой<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит высоту</span></label>
        <input type="text" name="heightRow" id="heightRow" class="formInputText"/>
        <input type="checkbox" name="heightSearchStrToo" id="heightSearchStrToo"  style="float: left;"/>
        <label for="heightSearchStrToo" style="margin: 11px 0 0 10px;">
            Если не найдем в столбце, попробовать поискать в исходной строке
        </label>
    </div>
    <div id="heightIsStrDiv" class="displayNone">
        <div class="inputRadio">
            <input type="radio" name="heightIsStrIn" id="heightAfterWidth" value="heightAfterWidth"/>
            <label for="heightAfterWidth" class="labelRadio">
                У Вас строка вида: <em>"Автошина 215<strong>/65</strong>R16 ..."</em><br/>
                Высота - это число после "/", который идет после ширины. Если "/" нет, то высота не задана
            </label>
        </div>
    </div>

    <div class="hr"></div>

    <!-- Радиус -->
    <h4>Радиус</h4>
    <div class="inputRadio">
        <input type="radio" name="radiusIs" id="radiusIsRow" value="radiusIsRow" checked/>
        <label for="radiusIsRow" class="labelRadio">Радиус в столбце</label>
        <input type="radio" name="radiusIs" id="radiusIsStr" value="radiusIsStr"/>
        <label for="radiusIsStr">Радиус в исходной строке</label>
    </div>
    <div class="hrSecond"></div>
    <div id="radiusIsRowDiv">
        <label for="radiusRow">Номер или буква столбца с радиусом<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит радиус</span></label>
        <input type="text" name="radiusRow" id="radiusRow" class="formInputText"/>
        <input type="checkbox" name="radiusSearchStrToo" id="radiusSearchStrToo"  style="float: left;"/>
        <label for="radiusSearchStrToo" style="margin: 11px 0 0 10px;">
            Если не найдем в столбце, попробовать поискать в исходной строке
        </label>
    </div>
    <div id="radiusIsStrDiv" class="displayNone">
        <div class="inputRadio">
            <input type="radio" name="radiusIsStrIn" id="radiusAfterSize" value="radiusAfterSize"/>
            <label for="radiusAfterSize" class="labelRadio">
                У Вас строка вида: <em>"Автошина 215/65<strong>R16</strong> ..."</em>
                или <em>"Автошина 215-<strong>16</strong> ..."</em><br/>
                Радиус - это число после одного из символов: "R", "х", "-"
            </label>
        </div>
    </div>

    <div class="hr"></div>

    <!-- Индекс нагрузки -->
    <h4>Индексы</h4>
    <div class="inputRadio">
        <input type="radio" name="indexPowerIs" id="indexPowerIsRow" value="indexPowerIsRow" checked/>
        <label for="indexPowerIsRow" class="labelRadio">Индексы в столбцах</label>
        <input type="radio" name="indexPowerIs" id="indexPowerIsStr" value="indexPowerIsStr"/>
        <label for="indexPowerIsStr">Индексы в исходной строке</label>
    </div>
    <div class="hrSecond"></div>
    <div id="indexPowerIsRowDiv">
        <label for="indexPowerRow">Номер или буква столбца с индексом нагрузки<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит индекс нагрузки</span></label>
        <input type="text" name="indexPowerRow" id="indexPowerRow" class="formInputText"/>
        <label for="indexSpeedRow">Номер или буква столбца с индексом скорости<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит индекс скорости</span></label>
        <input type="text" name="indexSpeedRow" id="indexSpeedRow" class="formInputText"/>
    </div>
    <div id="indexPowerIsStrDiv" class="displayNone">
        <div class="inputRadio">
            <input type="radio" name="indexPowerIsStrIn" id="indexPowerAfterSize" value="indexPowerAfterSize"/>
            <label for="indexPowerAfterSize" class="labelRadio">У Вас строка вида: <em>"Автошина 215/65R15
                    <strong>145/140Q</strong> ..."</em> или <em>"Автошина 215R16
                    <strong>145/140Q</strong> ..."</em><br/>
                Индекс - это число после ВТОРОГО пробела в строке (сначала слово - пробел - размеры - пробел -
                ИНДЕКС НАГРУЗКИ - ...)
            </label>
        </div>
    </div>

    <div class="hr"></div>

    <!-- Модель -->
    <h4>Модель</h4>
    <div class="inputRadio">
        <input type="radio" name="modelIs" id="modelIsRow" value="modelIsRow" checked/>
        <label for="modelIsRow" class="labelRadio">Модель в столбце</label>
        <input type="radio" name="modelIs" id="modelIsStr" value="modelIsStr"/>
        <label for="modelIsStr">Модель в исходной строке</label>
    </div>
    <div class="hrSecond"></div>
    <div id="modelIsRowDiv">
        <label for="modelRow">Номер или буква столбца с моделью<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит модель</span></label>
        <input type="text" name="modelRow" id="modelRow" class="formInputText"/>
    </div>
    <div id="modelIsStrDiv" class="displayNone">
        <div class="inputRadio">
            <input type="radio" name="modelIsStrIn" id="modelAfterSize" value="modelAfterSize"/>
            <label for="modelAfterSize" class="labelRadio">У Вас строка вида:
                <em>"Шина 215/75R17, 126/124M <strong>X MULTIWAY XZE2</strong> (Michelin)"</em>
                или <em>"Шина 340/65R18 <strong>DT818 RAD</strong> 122A8/B <strong>TL OPTITRAC</strong>
                (Goodyear)"</em><br/> Модель - это все, что идет после ВТОРОГО ПРОБЕЛА,
                за вычетом индексов и любого контента в скобках
            </label>

            <label for="modelWithoutWords" class="labelRadio">Минус слова. Это слова, которые нужно удалить из модели.
                Указывайте через запятую.<br/>
                <span>Внимание! В модели ПОСЛЕ указанных минус слов будет удален весь контент!</span></label>
            <input type="text" name="modelWithoutWords" id="modelWithoutWords" class="formInputText"
                   style="float: none"/>
            <input type="checkbox" name="braceYesNo" id="braceYesNo""/>
            <label for="braceYesNo" style="margin-top: 21px;">Удалить любой контент в скобках, который может попасть в Модель</label>
            <input type="checkbox" name="braceYesNoSame" id="braceYesNoSame"/>
            <label for="braceYesNoSame" style="margin-top: 11px;">
                ...и если не удалять все, что в скобках идет,
                то может хотя бы удалить сами скобки?
            </label>
        </div>
    </div>

    <div class="hr"></div>

    <!-- Бренд -->
    <h4>Бренд</h4>
    <div class="inputRadio">
        <input type="radio" name="brandIs" id="brandIsRow" value="brandIsRow" checked/>
        <label for="brandIsRow" class="labelRadio">Бренд в столбце</label>
        <input type="radio" name="brandIs" id="brandIsStr" value="brandIsStr"/>
        <label for="brandIsStr">Бренд в исходной строке</label>
        <input type="radio" name="brandIs" id="brandIsLonely" value="brandIsLonely"/>
        <label for="brandIsLonely">Бренд указывается в виде подраздела</label>
        <input type="radio" name="brandIs" id="brandOne" value="brandOne"/>
        <label for="brandOne">Бренд один на весь прайс</label>
        <input type="radio" name="brandIs" id="brandIsOneForAllBottom" value="brandIsOneForAllBottom"/>
        <label for="brandIsOneForAllBottom">Бренд указывается один раз на все шины ниже до переназначения бренда</label>
        <input type="radio" name="brandIs" id="brandIsRowAnywere" value="brandIsRowAnywere"/>
        <label for="brandIsRowAnywere">Бренд в столбце в любом месте</label>
    </div>
    <div class="hrSecond"></div>
    <div id="brandIsRowDiv">
        <label for="brandRow">Номер или буква столбца с брендом<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит бренд</span></label>
        <input type="text" name="brandRow" id="brandRow" class="formInputText"/>
    </div>
    <div id="brandIsStrDiv" class="displayNone">
        <label>Выберите точный вариант указания бренда в исходной строке</label>
        <div class="inputRadio">
            <input type="radio" name="brandIsStrIn" id="brandAfterSize" value="brandAfterSize"/>
            <label for="brandAfterSize" class="labelRadio">У Вас строка вида:
                <em>"Шина 215/75R17, 126/124M X MULTIWAY XZE2 <strong>(Michelin)</strong>"</em>
                или <em>"Шина 340/65R18 DT818 RAD 122A8/B TL OPTITRAC <strong>(Goodyear)</strong>"</em><br/>
                Бренд указан в конце каждой исходной строки В СКОБКАХ.
            </label>

            <input type="radio" name="brandIsStrIn" id="brandAnyware" value="brandAnyware"/>
            <label for="brandAnyware" class="labelRadio">
                Бренд указан в исходной строке где угодно<br/>
                и если найдем слово, совпадающее с каким-либо брендом, назначим его
            </label>
        </div>
    </div>
    <div id="brandIsLonelyDiv" class="displayNone">
        <label for="brandRowLonely">Номер или буква столбца в котором указывается бренд, как подраздел,
            редко, но на все шины ниже<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит бренд<br/>
            Если бренд в указанном столбце будет найден, то он будет присвоен всем последующим шинам,
                пока не найдет другой бренд.</span>
        </label>
        <input type="text" name="brandRowLonely" id="brandRowLonely" class="formInputText"/>
    </div>
    <div id="brandOneDiv" class="displayNone">
        <div class="universalSelects">
            <label for="idBrand">Бренд</label>
            <select name="idBrand" id="idBrand">
                <? foreach ($items['brand'] as $aKey  =>  $aVal) {
                    echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                }; ?>
            </select>
        </div>
    </div>
    <div id="brandIsOneForAllBottom" class="displayNone">
        <label for="brandRowOneForAllBottom">
            Бренд указывается один на несколько позиций ниже, возможно объединив ячейки<br/>
            <span>Бренд указывается не как подраздел, но и не в отдельной ячейке для КАЖДОЙ строки<br/>
            а в отдельной ячейке для первой и последующих ниже строк. <br/>
            Укажите номер или букву латиницей (A-Z) столбца, который содержит бренд<br/>
            Если бренд в указанном столбце будет найден, то он будет присвоен всем последующим шинам,
            пока не найдет другой бренд.</span>
        </label>
        <input type="text" name="brandRowOneForAllBottom" id="brandRowOneForAllBottom" class="formInputText"/>
    </div>
    <div id="brandIsRowAnywereDiv" class="displayNone">
        <label for="brandIsRowAnywere">Номер или буква столбца с брендом<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит бренд</span></label>
        <input type="text" name="brandIsRowAnywere" id="brandIsRowAnywere" class="formInputText"/>
    </div>

    <div class="hr"></div>

    <!-- Группа -->
    <h4>Группа</h4>
    <div class="inputRadio">
        <input type="radio" name="groupIs" id="groupIsRow" value="groupIsRow" checked/>
        <label for="groupIsRow" class="labelRadio">Группа в столбце</label>
        <input type="radio" name="groupIs" id="groupIsStr" value="groupIsStr"/>
        <label for="groupIsStr">Группа одна на весь прайс</label>
        <input type="radio" name="groupIs" id="groupIsLonely" value="groupIsLonely"/>
        <label for="groupIsLonely">Группа указывается в виде подраздела</label>
    </div>
    <div class="hrSecond"></div>
    <div id="groupIsRowDiv">
        <label for="groupRow">Номер или буква столбца с группой<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит группу</span></label>
        <input type="text" name="groupRow" id="groupRow" class="formInputText"/>
    </div>
    <div id="groupIsStrDiv" class="displayNone">
        <div class="universalSelects">
            <label for="idGroup">Группа</label>
            <select name="idGroup" id="idGroup">
                <? foreach ($items['group'] as $aKey  =>  $aVal) {
                    echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                }; ?>
            </select>
        </div>
    </div>
    <div id="groupIsLonelyDiv" class="displayNone">
        <label for="groupRowIsLonely">Номер или буква столбца в котором указывается группа,
            как подраздел, редко, но на все шины ниже<br/>
                <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит группу<br/>
                Если группа в указанном столбце будет найдена, то она будет присвоена всем последующим шинам,
                    пока не найдет другую группу.</span>
        </label>
        <input type="text" name="groupRowIsLonely" id="groupRowIsLonely" class="formInputText"/>
    </div>

    <div class="hr"></div>

    <!-- Сезон -->
    <h4>Сезон</h4>
    <div class="inputRadio">
        <input type="radio" name="seasonIs" id="seasonIsRow" value="seasonIsRow" checked/>
        <label for="seasonIsRow" class="labelRadio">Сезон в столбце</label>
        <input type="radio" name="seasonIs" id="seasonIsStr" value="seasonIsStr"/>
        <label for="seasonIsStr">Сезон один на весь прайс</label>
        <input type="radio" name="seasonIs" id="seasonIsLonely" value="seasonIsLonely"/>
        <label for="seasonIsLonely">Сезон указывается в виде подраздела</label>
    </div>
    <div class="hrSecond"></div>
    <div id="seasonIsRowDiv">
        <label for="seasonRow">Номер или буква столбца с сезоном<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит сезон</span></label>
        <input type="text" name="seasonRow" id="seasonRow" class="formInputText"/>
    </div>
    <div id="seasonIsStrDiv" class="displayNone">
        <div class="universalSelects">
            <label for="idSeason">Сезон</label>
            <select name="idSeason" id="idSeason">
                <? foreach ($items['season'] as $aKey  =>  $aVal) {
                    echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                }; ?>
            </select>
        </div>
    </div>
    <div id="seasonIsLonelyDiv" class="displayNone">
        <label for="seasonRowLonely">
            Номер или буква столбца в котором указывается сезон, как подраздел, редко, но на все шины ниже<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит сезон<br/>
            Если сезон в указанном столбце будет найден, то он будет присвоен всем последующим шинам,
            пока не найдет другой сезон.</span>
        </label>
        <input type="text" name="seasonRowLonely" id="seasonRowLonely" class="formInputText"/>
    </div>

    <div class="hr"></div>

    <!-- Наличие -->
    <h4 style="margin-bottom: 10px;">Наличие</h4>
    <label for="isItRow">Номер столбца, где указано Наличие</label>
    <input type="text" name="isItRow" id="isItRow" class="formInputText"/>

    <div class="hr"></div>

    <!-- Цена -->
    <h4 style="margin-bottom: 10px;">Цена</h4>
    <label for="priceRow">Номер столбца, где указана Цена</label>
    <input type="text" name="priceRow" id="priceRow" class="formInputText"/>

    <label>Коэффициент цены:</label>
    <div class="inputRadio">
        <input type="radio" name="priceChangeMethod" id="oneForAll" value="oneForAll" checked/>
        <label for="oneForAll" class="labelRadio">Один на весь прайс</label>

        <input type="radio" name="priceChangeMethod" id="custom" value="custom"/>
        <label for="custom" class="labelRadio" style="margin-top: 11px">Плавающий</label>
    </div>

    <div id="oneForAllDiv">
        <div class="hrSecond"></div>
        <label for="oneForAllText">Укажите коэффициент для всего прайса</label>
        <input type="text" name="oneForAllText" id="oneForAllText" class="formInputTextPriceChange" value="1"/>
    </div>
    <!-- 1 -->
    <div id="firstIf" style="display: none">
        <div class="hrSecond" style="margin-bottom: 0;"></div>
        <div class="inputRadio">
            <label>Индекс для...</label>
            <input type="radio" name="forWho1" id="forBrand1" value="forBrand1"/>
            <label for="forBrand1" class="labelRadio">Бренда</label>

            <input type="radio" name="forWho1" id="forGroup1" value="forGroup1"/>
            <label for="forGroup1" class="labelRadio" style="margin-top: 11px">Группы</label>
        </div>
        <?
        echo '<select name="idBrand1" class="idList" style="display: none">';
            foreach ($items['brand'] as $aKey  =>  $aVal) {
                echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
            };
        echo "</select>";

        echo '<select name="idGroup1" class="idList" style="display: none">';
            foreach ($items['group'] as $aKey  =>  $aVal) {
                echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
            };
        echo "</select>";
        ?>

        <label for="for1Text" style="margin: 0 0 13px 30px;">...равен:</label>
        <input type="text" name="for1Text" id="for1Text" class="formInputTextPriceChange" value="1"/>

        <div class="hrSecond"></div>
        <a id="addParam2" class="addParam" style="margin-left: 70px;">Добавить/скрыть еще условие</a>
    </div>
    <!-- 1 -->
    <!-- 2 -->
    <div id="secondIf" style="display: none">
        <div class="inputRadio">
            <label><span>Приоритет у первого условия</span></label>
            <label>Индекс для...</label>
            <input type="radio" name="forWho2" id="forBrand2" value="forBrand2"/>
            <label for="forBrand2" class="labelRadio">Бренда</label>

            <input type="radio" name="forWho2" id="forGroup2" value="forGroup2"/>
            <label for="forGroup2" class="labelRadio" style="margin-top: 11px">Группы</label>
        </div>

        <?
        echo '<select name="idBrand2" class="idList" style="display: none">';
        foreach ($items['brand'] as $aKey  =>  $aVal) {
            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
        };
        echo "</select>";

        echo '<select name="idGroup2" class="idList" style="display: none">';
        foreach ($items['group'] as $aKey  =>  $aVal) {
            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
        };
        echo "</select>";
        ?>
        <label for="for2Text" style="margin: 0 0 13px 30px;">...равен:</label>
        <input type="text" name="for2Text" id="for2Text" class="formInputTextPriceChange" value="1"/>

        <div class="hrSecond"></div>
        <a id="addParam3" class="addParam" style="margin-left: 70px;">Добавить/скрыть еще условие</a>
    </div>
    <!-- 2 -->
    <!-- 3 -->
    <div id="thirdIf" style="display: none">
        <div class="inputRadio">
            <label><span>Приоритет у первых двух условий</span></label>
            <label>Индекс для...</label>
            <input type="radio" name="forWho3" id="forBrand3" value="forBrand3"/>
            <label for="forBrand3" class="labelRadio">Бренда</label>

            <input type="radio" name="forWho3" id="forGroup3" value="forGroup3"/>
            <label for="forGroup3" class="labelRadio"  style="margin-top: 11px">Группы</label>
        </div>

        <?
        echo '<select name="idBrand3" class="idList" style="display: none">';
        foreach ($items['brand'] as $aKey  =>  $aVal) {
            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
        };
        echo "</select>";

        echo '<select name="idGroup3" class="idList" style="display: none">';
        foreach ($items['group'] as $aKey  =>  $aVal) {
            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
        };
        echo "</select>";
        ?>
        <label for="for3Text" style="margin: 0 0 13px 30px;">...равен:</label>
        <input type="text" name="for3Text" id="for3Text" class="formInputTextPriceChange" value="1"/>

        <div class="hrSecond"></div>
        <!-- 3 -->
    </div>

    <div id="priceChangeForOther" class="displayNone" style="margin: 20px 0 20px 0">
        <label for="allOtherText">Для остальных</label>
        <input type="text" name="allOtherText" id="allOtherText" class="formInputTextPriceChange" value="1"/>
    </div>
    <div class="hr" style="margin-bottom: 35px;"></div>

    <!-- Камерность -->
    <h4>Камерность</h4>
    <div class="inputRadio">
        <input type="radio" name="cameraIs" id="cameraIsRow" value="cameraIsRow" checked/>
        <label for="cameraIsRow" class="labelRadio">Камера в столбце</label>
        <input type="radio" name="cameraIs" id="cameraIsStr" value="cameraIsStr"/>
        <label for="cameraIsStr">Камера в исходной строке</label>
    </div>
    <div class="hrSecond"></div>
    <div id="cameraIsRowDiv">
        <label for="cameraRow">Номер или буква столбца с камерностью<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит камерность</span></label>
        <input type="text" name="cameraRow" id="cameraRow" class="formInputText"/>
    </div>
    <div id="cameraIsStrDiv" class="displayNone">
        <label>Камерность будет проверяться в исходной строке<br/>
                <span>Если в исходной строке будут найдены слова "камерная", "бескамерная", "б/к", "с камерой"<br/>
                Камерность будет назначана в соответствии с ними.</span></label>
    </div>

    <div class="hr"></div>

    <!-- Валюта -->
    <h4>Валюта</h4>
    <div class="inputRadio">
        <input type="radio" name="moneyIs" id="moneyIsOne" value="moneyIsOne" checked/>
        <label for="moneyIsOne">Валюта одна на весь прайс</label>
        <input type="radio" name="moneyIs" id="moneyIsRow" value="moneyIsRow"/>
        <label for="moneyIsRow" class="labelRadio">Мультивалютный прайс</label>
    </div>
    <div class="hrSecond"></div>
    <div id="moneyIsOneDiv">
        <div class="universalSelects">
            <label for="idMoney">Валюта</label>
            <select name="idMoney" id="idMoney">
                <? foreach ($items['money'] as $aKey  =>  $aVal) {
                    echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                }; ?>
            </select>
        </div>
    </div>
    <div id="moneyIsRowDiv" class="displayNone">
        <label for="moneyRow">Номер или буква столбца, в котором может быть указана валюта<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит валюту <br/>
            Она может быть указана, как "грн, UAH, usd, $". Если найдем такие символы,
            назначим валюту соответственно</span>
        </label>
        <input type="text" name="moneyRow" id="moneyRow" class="formInputText"/>
        <label>Валюта без опознавательных знаков это:</label>
        <div class="universalSelects">
            <label for="idMoneyUnknow">Валюта</label><select name="idMoneyUnknow" id="idMoneyUnknow">
                <? foreach ($items['money'] as $aKey  =>  $aVal) {
                    echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                }; ?>
            </select>
        </div>
    </div>

    <div class="hr"></div>

    <!-- Примечание -->
    <h4>Примечание</h4>
    <div class="inputRadio">
        <input type="radio" name="otherIs" id="otherIsRow" value="otherIsRow" checked/>
        <label for="otherIsRow" class="labelRadio">Добавить текст из столбца в примечание</label>
        <input type="radio" name="otherIs" id="otherIsLonely" value="otherIsLonely"/>
        <label for="otherIsLonely">Взять примечание из подраздела</label>
    </div>
    <div class="hrSecond"></div>
    <div id="otherIsRowDiv">
        <label for="otherRow">Номер или буква столбца с примечанием<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит примечание</span></label>
        <input type="text" name="otherRow" id="otherRow" class="formInputText"/>
        <label for="otherRowPlusText">Можно добавить текст в начало к любому примечанию<br/>
            <span>Например если в примечании написано Япония, Греция, то можно к каждому значению дописать<br/>
            Страна производитель: Япония и т.д.
            В этом случае напишите в поле ниже "Страна производитель: "</span>
        </label>
        <input type="text" name="otherRowPlusText" id="otherRowPlusText" class="formInputText"/>

        <label for="otherRowTwo">Можно еще один столбец в примечание поместить.
            Укажите номер или букву столбца с примечанием<br/>
            <span>Если не нужно, просто ничего не пишите</span></label>
        <input type="text" name="otherRowTwo" id="otherRowTwo" class="formInputText"/>
        <label for="otherRowPlusTextTwo">Можно добавить текст в начало к дополнительному примечанию<br/>
            <span>Если не нужно, просто ничего не пишите</span>
        </label>
        <input type="text" name="otherRowPlusTextTwo" id="otherRowPlusTextTwo" class="formInputText"/>
    </div>
    <div id="otherIsLonelyDiv" class="displayNone">
        <label for="otherRowLonely">Номер или буква столбца в котором указывается примечание,
            как подраздел, редко, но на все шины ниже<br/>
            <span>Укажите номер или букву латиницей (A-Z) столбца, который содержит примечание<br/>
            Указанное примечание будет присвоено всем последующим шинам,
            пока не будт назначено другое примечание.</span>
        </label>
        <input type="text" name="otherRowLonely" id="otherRowLonely" class="formInputText"/>
        <label for="otherRowPlusTextLonely">Можно добавить текст в начало к любому примечанию<br/>
            <span>Например если в примечании написано Япония, Греция, то можно к каждому значению дописать<br/>
            Страна производитель: Япония и т.д.
            В этом случае напишите в поле ниже "Страна производитель: "</span>
        </label>
        <input type="text" name="otherRowPlusTextLonely" id="otherRowPlusTextLonely" class="formInputText"/>
    </div>

    <div class="hr"></div>

    <!-- Год производства -->
    <h4 style="margin-bottom: 20px;">Год производства</h4>
    <label for="yearRow">Номер или буква (A-Z) столбца, в котором указан год:</label>
    <input type="text" name="yearRow" id="yearRow" class="formInputText"/>

    <div class="hr"></div>

    <!-- Доп. условие -->
    <h4 style="margin-bottom: 20px;">Удалить лишние позиции</h4>
    <label for="delRow">Пропустить позицию и не добавлять ее в базу, если в столбце с номером:</label>
    <input type="text" name="delRow" id="delRow" class="formInputText"/>
    <label for="delText">...есть текст или значение:<br/>
        <span>Это нужно если, например, в прайсе смешаны и диски, и шины <br/>
        Введите номер столбца, в котором встречается слово "Диск"
        и во втором поле напишине "диск"</span></label>
    <input type="text" name="delText" id="delText" class="formInputText"/>

    <div class="hr"></div>

    <!-- Доп. условие -->
    <h4 style="margin-bottom: 20px;">Удалить лишние позиции</h4>
    <label for="delRowEmpty">Пропустить позицию и не добавлять ее в базу, если в столбце с номером пустая ячейка:<br/>
        <span>Это нужно если подраздел очень похож на исходную строку</label>
    <input type="text" name="delRowEmpty" id="delRowEmpty" class="formInputText"/>

    <div class="hr"></div>

    <input type="file" name="userfile" id="file"
           accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>

    <input type="submit" value="Поехали!" id="submit" style="margin-bottom: 50px;"/>
</form>
</div>
<div id="load" style="display: none;">
    <div class="loadImg"><img src="../img/02.gif"/><p>Идет обработка данных...</p></div>
    <div class="loadBg"></div>
</div>