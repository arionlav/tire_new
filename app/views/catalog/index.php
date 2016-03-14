<?php
use config\App;

$title = 'Редактирование справочников';
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

    if(!empty($arrKeyWords)){
        foreach ($arrKeyWords as $a) {
            $js_array = json_encode($items[$a]); echo "arr['" . $a . "'] = ". $js_array . ";\n";
        }
    }
    ?>
</script>

<script type="text/javascript" src="../js/catalog.js"></script>

<h3 class="courseH3">Выберите праметры для добавления/удаления</h3>
<div class="searchContainer">
    <div class="formCatalog searchRowAll">
        <form action="<?=App::url(['catalog/accept']) ?>" enctype="multipart/form-data" method="post">
            <div class="addDeleteInputs">
                <input type="radio" name="action" id="action1" value="1" checked/>
                <label for="action1">Добавить данные</label>
                <input type="radio" name="action" id="action2" value="2"/>
                <label for="action2">Удалить данные</label>
                <p class="catalogAttention">
                    <span>ВНИМАНИЕ!</span> <br/>
                    <em>Удаленяя любое значение,<br/> Вы удалите ВСЕ позиции в Базе данных с указанным значением!</em>
                </p>
            </div>

            <div class="searchRowAll rowTwoCatalog">
                <div id="idd" class="idd">
                    <a class="widthId iddA">Ширина</a><br/>
                    <a class="heightId iddA">Высота</a><br/>
                    <a class="radiusId iddA">Радиус</a><br/>
                    <a class="indexPowerId iddA">Индекс нагрузки</a><br/>
                    <a class="indexSpeedId iddA">Индекс скорости</a><br/>
                    <a class="brandId iddA">Бренд</a><br/>
                    <a class="cameraId iddA" style="display: none;">Камерность</a><br/>
                    <a class="seasonId iddA" style="display: none;">Сезон</a><br/>
                    <a class="groupId iddA" style="display: none;">Группа</a><br/>
                    <a class="listId iddA" style="display: none;">Прайс</a>
                </div>
            </div>

            <div id="rowInsertParam">
                <?
                $rowInsertParam = [
                    1 => [
                        'id'    => 'widthInsertParam',
                        'label' => 'Укажите новую ширину:'
                    ],
                    2 => [
                        'id'    => 'heightInsertParam',
                        'label' => 'Укажите новую высоту:'
                    ],
                    3 => [
                        'id'    => 'radiusInsertParam',
                        'label' => 'Укажите новый радиус:'
                    ],
                    4 => [
                        'id'        => 'indexPowerInsertParam',
                        'label'     => 'Укажите новый Индекс нагрузки:',
                        'idTwo'     => 'indexPowerInsertParamTwo',
                        'labelTwo'  => 'При этом, нагрузка в кг:'
                    ],
                    5 => [
                        'id'        => 'indexSpeedInsertParam',
                        'label'     => 'Укажите новый Индекс скорости:',
                        'idTwo'     => 'indexSpeedInsertParamTwo',
                        'labelTwo'  => 'При этом, скорость в км/ч:'
                    ],
                    6 => [
                        'id'    => 'brandInsertParam',
                        'label' => 'Укажите новый бренд:'
                    ],
                    7 => [
                        'id'    => 'cameraInsertParam',
                        'label' => 'Укажите новый вид камерности:'
                    ],
                    8 => [
                        'id'    => 'seasonInsertParam',
                        'label' => 'Придумайте новый сезон:'
                    ],
                    9 => [
                        'id'    => 'groupInsertParam',
                        'label' => 'Добавьте новую группу:'
                    ],
                    10 => [
                        'id'    => 'listInsertParam',
                        'label' => 'Добавьте новый прайс:'
                    ]
                ];
                if (!empty($rowInsertParam)) {
                    foreach ($rowInsertParam as $rOne) {
                        if (!$rOne['idTwo']) {
                            echo "<div id='{$rOne['id']}' class='displayNone'>
                                <label>{$rOne['label']}</label>
                                <input type='text' name='{$rOne['id']}'/>
                            </div>";
                        } else {
                            echo "<div id='{$rOne['id']}' class='displayNone'>
                                <label>{$rOne['label']}</label>
                                <input type='text' name='{$rOne['id']}'/>
                                <label class='labelTwo'>{$rOne['labelTwo']}</label>
                                <input class='inputTwo' type='text' name='{$rOne['idTwo']}'/>
                            </div>";
                        }
                    }
                }
                ?>
                <div class="rowInSelect" id="widthId">
                    <select name='idWidthArr[]' multiple=''>
                        <? foreach ($items['width'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="heightId">
                    <select name='idHeightArr[]' multiple=''>
                        <? foreach ($items['height'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="radiusId">
                    <select name='idRadiusArr[]' multiple=''>
                        <? foreach ($items['radius'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="indexPowerId">
                    <select name='idIndexPowerArr[]' multiple=''>
                        <? foreach ($items['indexPower'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="indexSpeedId">
                    <select name='idIndexSpeedArr[]' multiple=''>
                        <? foreach ($items['indexSpeed'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="brandId">
                    <select name='idBrandArr[]' multiple=''>
                        <? foreach ($items['brand'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="cameraId">
                    <select name='idCameraArr[]' multiple=''>
                        <? foreach ($items['camera'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="seasonId">
                    <select name='idSeasonArr[]' multiple=''>
                        <? foreach ($items['season'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="groupId">
                    <select name='idGroupArr[]' multiple=''>
                        <? foreach ($items['group'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
                <div class="rowInSelect" id="listId">
                    <select name='idListArr[]' multiple=''>
                        <? foreach ($items['list'] as $aKey  =>  $aVal) {
                            echo "<option value='" . $aKey . "'>" . $aVal . "</option>";
                        } ?>
                    </select>
                </div>
            </div>
            <input id="submit" type="submit" value="Подтверждаю" style="margin-left: 250px;"/>
        </form>
    </div>
</div>
