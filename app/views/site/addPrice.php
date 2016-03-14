<?
use config\App;

$title = "Добавить прайс";
?>
<script type="text/javascript" src="../js/addPrice.js"></script>
<script type="text/javascript">
    $(function () {
        $('form input#submit').on('click', function() {
            $('div#load').fadeIn(500);
        });
    })
</script>

<h3 style="margin: 50px 0 -20px 0">Выберите прайс для изменения в базе данных</h3>

<div class="form">
    <form action='<?=App::url(['site/add-price-success']) ?>' enctype='multipart/form-data' method='post'>
        <label>Сейчас я хочу добавить прайс:</label>

        <?
        echo "<select name='idList' id='idList'>";
            foreach ($lists as $arr) {
                echo "<option value='{$arr["idList"]}'>{$arr['nameList']}</option>";
            };
        echo '</select>';
        ?>

        <div id="swimFactor">
            <div class="hr"></div>
            <label>Коэффициент цены:</label>
            <div class="inputRadio">
                <input type="radio" name="priceChangeMethod" id="oneForAll" value="oneForAll" checked/>
                <label for="oneForAll" class="labelRadio">Один на весь прайс</label>

                <input type="radio" name="priceChangeMethod" id="custom" value="custom"/>
                <label for="custom" class="labelRadio" style="margin-top: 16px;">Плавающий</label>
            </div>

            <div id="oneForAllDiv">
                <div class="hrSecond"></div>
                <label for="oneForAllText">Укажите коэффициент для всего прайса</label>
                <input type="text" name="oneForAllText" id="oneForAllText" class="formInputTextPriceChange" value="1"/>
            </div>
        </div>

        <div id="noticeForPetr" style="">
            <div class="hrSecond"></div>
            <p><em>ВНИМАНИЕ!</em> На цену действуют следующие коэффициенты (<ins>деление</ins>):</p>
            <p>- BRIDGESTONE, летние: <span>1.12</span><br/>
                - YOKOHAMA, летние: <span>1.13</span><br/>
                - Летние остальные: <span>1.1</span><br/>
                - Зимние: <span>1.15</span><br/>
                - Грузовые шины: <span>1.07</span></p>
        </div>
        <!-- 1 -->
        <div id="swimFactorBlock">
            <div id="firstIf" style="display: none">
                <div class="hrSecond" style="margin-bottom: 0;"></div>
                <div class="inputRadio">
                    <label>Индекс для...</label>
                    <input type="radio" name="forWho1" id="forBrand1" value="forBrand1"/>
                    <label for="forBrand1" class="labelRadio priceChangeLabel">Бренда</label>

                    <input type="radio" name="forWho1" id="forGroup1" value="forGroup1"/>
                    <label for="forGroup1" class="labelRadio priceChangeLabel">Группы</label>
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
                <a id="addParam2" class="addParam">Добавить/скрыть еще условие</a>
            </div>
            <!-- 1 -->
            <!-- 2 -->
            <div id="secondIf" style="display: none">
                <div class="inputRadio">
                    <label><span>Приоритет у первого условия</span></label>
                    <label>Индекс для...</label>
                    <input type="radio" name="forWho2" id="forBrand2" value="forBrand2"/>
                    <label for="forBrand2" class="labelRadio priceChangeLabel">Бренда</label>

                    <input type="radio" name="forWho2" id="forGroup2" value="forGroup2"/>
                    <label for="forGroup2" class="labelRadio priceChangeLabel">Группы</label>
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
                <label style="margin: 0 0 13px 30px;" for="for2Text">...равен:</label>
                <input type="text" name="for2Text" id="for2Text" class="formInputTextPriceChange" value="1"/>

                <div class="hrSecond"></div>
                <a id="addParam3" class="addParam">Добавить/скрыть еще условие</a>
            </div>
            <!-- 2 -->
            <!-- 3 -->
            <div id="thirdIf" style="display: none">
                <div class="inputRadio">
                    <label><span>Приоритет у первых двух условий</span></label>
                    <label>Индекс для...</label>
                    <input type="radio" name="forWho3" id="forBrand3" value="forBrand3"/>
                    <label for="forBrand3" class="labelRadio priceChangeLabel">Бренда</label>

                    <input type="radio" name="forWho3" id="forGroup3" value="forGroup3"/>
                    <label for="forGroup3" class="labelRadio priceChangeLabel">Группы</label>
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
                <label style="margin: 0 0 13px 30px;" for="for3Text">...равен:</label>
                <input type="text" name="for3Text" id="for3Text" class="formInputTextPriceChange" value="1"/>

                <div class="hrSecond"></div>
                <!-- 3 -->
            </div>

            <div id="priceChangeForOther" class="displayNone" style="margin: 20px 0 20px 0">
                <label for="allOtherText">Для остальных</label>
                <input type="text" name="allOtherText" id="allOtherText" class="formInputTextPriceChange" value="1"/>
            </div>
        </div>
        <div class="hr" style="margin-bottom: 35px;"></div>

        <input type="file" name="userfile" id="file" accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
        <input id="submit" type="submit" value="Поехали!"/>
    </form>
</div>
<div id="load" style="display: none;">
    <div class="loadImg"><img src="../img/02.gif"/><p>Идет обработка данных...</p></div>
    <div class="loadBg"></div>
</div>
