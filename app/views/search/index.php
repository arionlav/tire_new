<?php
use config\App;

$title = 'Поиск в Базе данных';
?>

<script type="text/javascript">
    var arr = [];
    <?
    $arrKeyWords = [
        'brand',
        'height',
        'radius',
        'indexPower',
        'indexSpeed',
        'camera',
        'season',
        'group',
        'list',
        'isIt',
        'width'
    ];

    foreach ($arrKeyWords as $a) {
        $js_array = json_encode($items[$a]); echo "arr['" . $a . "'] = ". $js_array . ";\n";
    }
    ?>
</script>

<script type="text/javascript" src="../js/search.js"></script>

<h3 class="courseH3">Выберите праметры для поиска</h3>
<div class="searchContainer">
    <div class="searchRowAll rowOne">
        <div id="idd" class="idd">
            <a class="strId iddA">Исходная строка</a><br/>
            <div class="borderLine"></div>
            <a class="widthId iddA">Ширина</a><br/>
            <a class="heightId iddA">Высота</a><br/>
            <a class="radiusId iddA">Радиус</a><br/>
            <div class="borderLine"></div>
            <a class="indexPowerId iddA">Индекс нагрузки</a><br/>
            <a class="indexSpeedId iddA">Индекс скорости</a><br/>
            <div class="borderLine"></div>
            <a class="brandId iddA">Бренд</a><br/>
            <a class="modelId iddA">Модель</a><br/>
            <div class="borderLine"></div>
            <a class="cameraId iddA">Камерность</a><br/>
            <a class="seasonId iddA">Сезон</a><br/>
            <a class="groupId iddA">Группа</a><br/>
            <a class="listId iddA">Прайс</a><br/>
            <a class="otherId iddA">Текст в примечании</a><br/>
            <div class="borderLine"></div>
            <a class="isItId iddA">Наличие</a><br/>
            <a class="priceId iddA">Цена</a>
        </div>
    </div>

    <div class="formSelect searchRowAll rowTwo">
        <form action="<?= App::url(['search/result', 'mode' => 1]) ?>" enctype="multipart/form-data" method="post" target="_blank">
            <div id="selectsContent">
                <div class="rowInSelect fewHeight" id="widthId">
                    <div class="selectCustomWrite">
                        <p>Можно ввести здесь...</p>
                        <input type="text" name="widthText" class="inputText"/>
                        <p>...или выбрать ниже</p>
                    </div>
                    <select name='idWidthArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['width'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect fewHeight" id="heightId">
                    <div class="selectCustomWrite">
                        <p>Можно ввести здесь...</p>
                        <input type="text" name="heightText" class="inputText"/>
                        <p>...или выбрать ниже</p>
                    </div>
                    <select name='idHeightArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['height'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect fewHeight" id="radiusId">
                    <div class="selectCustomWrite">
                        <p>Можно ввести здесь...</p>
                        <input type="text" name="radiusText" class="inputText"/>
                        <p>...или выбрать ниже</p>
                    </div>
                    <select name='idRadiusArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['radius'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect fewHeight" id="indexPowerId">
                    <div class="selectCustomWrite">
                        <p>Можно ввести здесь...</p>
                        <input type="text" name="indexPowerText" class="inputText"/>
                        <p>...или выбрать ниже</p>
                    </div>
                    <select name='idIndexPowerArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['indexPower'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect fewHeight" id="indexSpeedId">
                    <div class="selectCustomWrite">
                        <p>Можно ввести здесь...</p>
                        <input type="text" name="indexSpeedText" class="inputText"/>
                        <p>...или выбрать ниже</p>
                    </div>
                    <select name='idIndexSpeedArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['indexSpeed'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="brandId">
                    <select name='idBrandArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['brand'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="cameraId">
                    <select name='idCameraArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['camera'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="seasonId">
                    <select name='idSeasonArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['season'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="groupId">
                    <select name='idGroupArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['group'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="isItId">
                    <select name='isItArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <option value='-2'>Есть в наличии</option>
                        <? foreach ($items['isIt'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="listId">
                    <select name='idListArr[]' multiple=''>
                        <option value='-1' selected class="searchAnyValue">Любое значение</option>
                        <? foreach ($items['list'] as $aKey => $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>

                <div class="rowInText rowInSelect" id="strId" style="clear: left">
                    <label for="str">В исходной строке есть текст:</label>
                    <input type="text" name="str" id="str"/>
                </div>
                <div class="rowInText rowInSelect" id="modelId">
                    <label for="model">Модель:</label>
                    <input type="text" name="model" id="model"/>
                </div>
                <div class="rowInText rowInSelect" id="otherId">
                    <label for="other">Текст в примечании:</label>
                    <input type="text" name="other" id="other"/>
                </div>
                <div class="rowInText rowInSelect" id="priceFromId">
                    <label for="priceFrom">Цена ОТ:</label>
                    <input type="text" name="priceFrom" id="priceFrom" value="0"/>
                </div>
                <div class="rowInText rowInSelect" id="priceToId">
                    <label for="priceTo">Цена ДО:</label>
                    <input type="text" name="priceTo" id="priceTo" value="100000"/>
                </div>
            </div>
            <input id="submitSearch" type="submit" value="Поехали!"/>

            <input type="hidden" name="requestBrand" value=""/>
            <input type="hidden" name="requestWidth" value=""/>
            <input type="hidden" name="requestHeight" value=""/>
            <input type="hidden" name="requestRadius" value=""/>
            <input type="hidden" name="requestGroup" value=""/>
            <input type="hidden" name="requestIndexPower" value=""/>
            <input type="hidden" name="requestIndexSpeed" value=""/>
            <input type="hidden" name="requestCamera" value=""/>
            <input type="hidden" name="requestSeason" value=""/>
            <input type="hidden" name="requestIsIt" value=""/>
            <input type="hidden" name="requestList" value=""/>
        </div>

        <div class="searchRowAll rowThree">
            <div class="rowThreeField">
                <div class="queryParam">Исходная строка: </div>
                <div id="contentStr" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Ширина: </div>
                <div id="contentWidth" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Высота: </div>
                <div id="contentHeight" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Радиус: </div>
                <div id="contentRadius" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Индекс нагрузки: </div>
                <div id="contentIndexPower" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Индекс скорости: </div>
                <div id="contentIndexSpeed" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Бренд: </div>
                <div id="contentBrand" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Камерность: </div>
                <div id="contentCamera" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Сезон: </div>
                <div id="contentSeason" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Группа: </div>
                <div id="contentGroup" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Прайс: </div>
                <div id="contentList" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Наличие: </div>
                <div id="contentIsIt" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Модель: </div>
                <div id="contentModel" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Примечание: </div>
                <div id="contentOther" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Цена ОТ: </div>
                <div id="contentPriceFrom" class="refresh">-</div>
            </div>
            <div class="rowThreeField">
                <div class="queryParam">Цена ДО: </div>
                <div id="contentPriceTo" class="refresh">-</div>
            </div>
            <input type="reset" id="reset" name="reset" value="Очистить параметры" />
        </div>

        <div style="clear: both"></div>
        <div id="listOptions" style="margin-top: -65px; float: left; position: relative;">
            <div style="width: 80%; border-bottom: 1px dotted lightsteelblue"></div>
            <div class="inputRadio">
                <input type="checkbox" name="listSettingCash" id="listSettingCash" checked/>
                <label for="listSettingCash" class="labelRadio" style="margin-top: 11px;">Наличный расчет</label>
                <input type="checkbox" name="listSettingBank" id="listSettingBank" checked/>
                <label for="listSettingBank" style="margin-top: 12px;">Безналичный расчет</label>
                <input type="checkbox" name="listSettingBigg" id="listSettingBigg" checked/>
                <label for="listSettingBigg" style="margin-top: 13px;">Учитывать прайсы от "BIGG!"</label>
            </div>
        </div>
    </form>
</div>
