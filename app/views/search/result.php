<?php
use config\App;

$title = 'Результаты поиска';
?>
<script type="text/javascript" src="../js/data_tables/media/js/jquery.dataTables.js"></script>
<link rel="stylesheet" href="../js/data_tables/media/css/jquery.dataTables.css"/>

<script type="text/javascript" language="javascript" class="init">
    var link = "<?=App::url(['search/modify']) ?>";
    var page;
    <?
    (!is_null($page))
        ? $pageVal = "page = " . $page . ';'
        : $pageVal = "page = -1;";

    echo $pageVal;
    ?>

    $(document).ready(function() {
        var selectorTable = $('#searchTires');
        selectorTable.DataTable({
            "columns": [
                null,
                null,
                null,
                {"width": "250px"},
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                {"width": "10px", "orderable": false}
            ],

            "order": [[8, 'asc']],
            "lengthMenu": [[10, 15, 25, 50, 100, -1], [10, 15, 25, 50, 100, "Все"]],
            "pageLength": 15
        });

        var table = selectorTable.dataTable();

        table.fnPageChange(<?=$pageDatatables ?>);

        $(table).fadeIn(500).removeClass('hidden');
    });
</script>

<script type="text/javascript" src="../js/leanModal.js"></script>
<script type="text/javascript" src="../js/searchResult.js"></script>

<?
echo '<h2>Параметры поиска</h2>';
echo '<div id="searchParam">';
if (
    $post['requestWidth'] != ''
    and $post['requestWidth'] != '-'
    and $post['requestWidth'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Ширина:</span> <em>{$post['requestWidth']}</em></div>";
}

if (
    $post['requestHeight'] != ''
    and $post['requestHeight'] != '-'
    and $post['requestHeight'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Высота:</span> <em>{$post['requestHeight']}</em></div>";
}

if (
    $post['requestRadius'] != ''
    and $post['requestRadius'] != '-'
    and $post['requestRadius'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Радиус:</span> <em>{$post['requestRadius']}</em></div>";
}

if (
    $post['requestIndexPower'] != ''
    and $post['requestIndexPower'] != '-'
    and $post['requestIndexPower'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Индекс нагрузки:</span> <em>{$post['requestIndexPower']}</em></div>";
}

if (
    $post['requestIndexSpeed'] != ''
    and $post['requestIndexSpeed'] != '-'
    and $post['requestIndexSpeed'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Индекс скорости:</span> <em>{$post['requestIndexSpeed']}</em></div>";
}

if (
    $post['requestBrand'] != ''
    and $post['requestBrand'] != '-'
    and $post['requestBrand'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Бренд:</span> <em>{$post['requestBrand']}</em></div>";
}

if (
    $post['requestCamera'] != ''
    and $post['requestCamera'] != '-'
    and $post['requestCamera'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Камерность:</span> <em>{$post['requestCamera']}</em></div>";
}

if (
    $post['requestSeason'] != ''
    and $post['requestSeason'] != '-'
    and $post['requestSeason'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Сезон:</span> <em>{$post['requestSeason']}</em></div>";
}

if (
    $post['requestGroup'] != ''
    and $post['requestGroup'] != '-'
    and $post['requestGroup'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Группа:</span> <em>{$post['requestGroup']}</em></div>";
}

if (
    $post['requestList'] != ''
    and $post['requestList'] != '-'
    and $post['requestList'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Прайс:</span> <em>{$post['requestList']}</em></div>";
}

if (
    $post['requestIsIt'] != ''
    and $post['requestIsIt'] != '-'
    and $post['requestIsIt'] != 'Любое значение'
) {
    echo "<div id='searchParamDiv'><span>Наличие:</span> <em>{$post['requestIsIt']}</em></div>";
}

if ($post['str'] != '') {
    echo "<div id='searchParamDiv'><span>В исходной строке есть:</span> <em>{$post['str']}</em></div>";
}

if ($post['model'] != '') {
    echo "<div id='searchParamDiv'><span>Модель:</span> <em>{$post['model']}</em></div>";
}

if ($post['other'] != '') {
    echo "<div id='searchParamDiv'><span>Примечание:</span> <em>{$post['other']}</em></div>";
}

if (
    $post['priceFrom'] != '0'
    and $post['priceFrom'] != ''
) {
    echo "<div id='searchParamDiv'><span>Цена ОТ:</span> <em>{$post['priceFrom']}</em></div>";
}

if (
    $post['priceTo'] != '100000'
    and $post['priceTo'] != ''
) {
    echo "<div id='searchParamDiv'><span>Цена ДО:</span> <em>{$post['priceTo']}</em></div>";
}

if ($post['listSettingCash']) {
    $flagCash = 1;
    echo "<div id='searchParamDiv'><span>Наличный расчет:</span> <em>Да</em></div>";
} else {
    echo "<div id='searchParamDiv'><span>Наличный расчет:</span> <em>Нет</em></div>";
}

if ($post['listSettingBank']) {
    $flagBank = 1;
    echo "<div id='searchParamDiv'><span>Безналичный расчет:</span> <em>Да</em></div>";
} else {
    echo "<div id='searchParamDiv'><span>Безналичный расчет:</span> <em>Нет</em></div>";
}

if ($post['listSettingBigg']) {
    $flagBigg = 1;
    echo "<div id='searchParamDiv'><span>Учитывать прайсы BIGG!:</span> <em>Да</em></div>";
} else {
    echo "<div id='searchParamDiv'><span>Учитывать прайсы BIGG!:</span> <em>Не учитывать</em></div>";
}

echo "<div style='color: crimson'>Остальные параметры - любые</div>";
echo '</div>';
?>

<a href="javascript:close_window();" class="closeSearchResult">Закрыть эти результаты поиска</a>
<div class="tableContent">

    <h3>Статистика:</h3>
    <div id="searchParam">
        <div id='searchParamDiv'>
            <div id="showItems" class="showLink">
                Найдено позиций: <em><?=$countTires ?></em>
                <strong>(показать/Скрыть по городам и прайсам)</strong>
            </div>
        </div>
        <div id='searchParamDiv'>
            <div id="itemsInPrice" class="displayNone marginLeft">
                <?
                if (!empty($arrayData['countValues'])) {
                    foreach ($arrayData['countValues'] as $k => $a) {
                        echo "<em>$a</em> поз. $k<br/>";
                    }
                }
                ?>
            </div>
        </div>
        <div id='searchParamDiv'>
            Цена от <em><?=$arrayData['price'][0] . '</em> до <em>' . $arrayData['price'][count($arrayData['price'])-1]; ?></em>
        </div>
        <div id='searchParamDiv'>Ширина
            <?
            ($arrayData['width'][0] == $arrayData['width'][count($arrayData['width'])-1])
                ? $str =  '<em>' . $arrayData['width'][0] . '</em>'
                : $str =  'от <em>' . $arrayData['width'][0] . '</em> до <em>'
                    . $arrayData['width'][count($arrayData['width'])-1] . '</em>';
            echo $str;
            ?>
        </div>
        <div id='searchParamDiv'>
            Высота
            <? if ($arrayData['height'][count($arrayData['height'])-1] == 'Не указано') {
                $arrayData['height'][count($arrayData['height']) - 1] = $arrayData['height'][count($arrayData['height']) - 2];
            }

            ($arrayData['height'][0] == $arrayData['height'][count($arrayData['height'])-1])
                ? $str =  '<em>' . $arrayData['height'][0] . '</em>'
                : $str =  'от <em>' . $arrayData['height'][0] . '</em> до <em>'
                    . $arrayData['height'][count($arrayData['height'])-1] . '</em>';
            echo $str; ?>
        </div>
        <div id='searchParamDiv'>Радиус
            <? if ($arrayData['radius'][count($arrayData['radius'])-1] == 'Не указан') {
                $arrayData['radius'][count($arrayData['radius']) - 1] = $arrayData['radius'][count($arrayData['radius']) - 2];
            }

            ($arrayData['radius'][0] == $arrayData['radius'][count($arrayData['radius'])-1])
                ? $str =  '<em>' . $arrayData['radius'][0] . '</em>'
                : $str =  'от <em>' . $arrayData['radius'][0] . '</em> до <em>'
                    . $arrayData['radius'][count($arrayData['radius'])-1] . '</em>';
            echo $str; ?>
        </div>
        <div id='searchParamDiv'>
            Сезон: <em>
                <?
                if (!empty($arrayData['seasonUnique'])) {
                    foreach ($arrayData['seasonUnique'] as $a) {
                        if ($a != 'Не указан') echo "$a, ";
                    }
                }
                ?>
            </em>
        </div>
        <div id='searchParamDiv'>
            Группа: <em>
                <?
                if (!empty($arrayData['groupUnique'])) {
                    foreach ($arrayData['groupUnique'] as $a) {
                        if ($a != 'Не указано') echo "$a, ";
                    }
                }
                ?>
            </em>
        </div>
        <div id='searchParamDiv'>
            <div id="showBrands" class="showLink">Бренды <strong>(показать/скрыть бренды):</strong></div>
            <div id="brands" class="displayNone marginLeft">
                <em>
                    <?
                    if (!empty($arrayData['brandUnique'])) {
                        foreach ($arrayData['brandUnique'] as $a) {
                            echo "$a<br/>";
                        }
                    }
                    ?>
                </em>
            </div>
        </div>
        <div id='searchParamDiv'>
            <div id="showCountries" class="showLink">
                Страны производства (те, которые указаны) <strong>(показать/скрыть страны производства):</strong>
            </div>
            <div id="countries" class="displayNone marginLeft">
                <em>
                    <?
                    if (!empty($arrayData['countryUnique'])) {
                        foreach ($arrayData['countryUnique'] as $a) {
                            if (preg_match('|Страна.+?: (.+)|u', $a, $matches)) {
                                echo $matches[1] . '<br/>';
                            } elseif (preg_match('|.+роизводит.+?: (.+)|u', $a, $matches)) {
                                echo $matches[1] . '<br/>';
                            }
                        }
                    }
                    ?>
                </em>
            </div>
        </div>
    </div>

    <div style="position: relative; width: 330px; margin: 0 auto;">
        <h1 id="resultH1">Результат поиска</h1>
        <a href="<?=App::url(['search/load-excel']) ?>" id="resultImg"></a>
    </div>

    <table class="tableOrders hover order-column stripe hidden" id="searchTires">
        <thead>
            <tr>
                <th>ID</th>
                <th>Прайс</th>
                <th>Город</th>
                <th>Исходная строка</th>
                <th>W</th>
                <th>H</th>
                <th>R</th>
                <th>Бренд</th>
                <th>Модель</th>
                <th>Нал</th>
                <th>Цена</th>
                <th>+/-</th>
                <th>Группа</th>
                <th>Сезон</th>
                <th>Камера</th>
                <th>IPower</th>
                <th>Kg</th>
                <th>ISpeed</th>
                <th>Км/ч</th>
                <th>$</th>
                <th>Год</th>
                <th>Примечание</th>
                <th>Дата</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?
        if (empty($tires)) {
            echo "<div class='errorLinksHead'>
                <p>Ничего не найдено с заданными параметрами, попробуйте что-то изменить.<p>
            </div>";
            exit;
        }

        foreach ($tiresChunk[$page] as $tire) {

            $time = date ('d.m.y', strtotime($tire['dateTime']));

            if ($tire['priceMove'] > 0) {
                $priceMoveEcho = "<td class='priceUp'>+{$tire['priceMove']}</td>";
            } elseif ($tire['priceMove'] < 0) {
                $priceMoveEcho = "<td class='priceDown'>{$tire['priceMove']}</td>";
            } else {
                $priceMoveEcho = "<td class='priceStay'></td>";
            }

            echo "<tr id='{$tire['id']}'>
                <td>{$tire['id']}</td>
                <td>{$tire['nameList']}</td>
                <td>{$tire['city']}</td>
                <td>{$tire['str']}</td>
                <td>{$tire['nameWidth']}</td>
                <td>{$tire['nameHeight']}</td>
                <td>{$tire['radius']}</td>
                <td>{$tire['nameBrand']}</td>
                <td>{$tire['model']}</td>
                <td>{$tire['isIt']}</td>
                <td>{$tire['price']}</td>
                $priceMoveEcho
                <td>{$tire['nameGroup']}</td>
                <td>{$tire['nameSeason']}</td>
                <td>{$tire['nameCamera']}</td>
                <td>{$tire['nameIndexPower']}</td>
                <td>{$tire['Kg']}</td>
                <td>{$tire['nameIndexSpeed']}</td>
                <td>{$tire['speed']}</td>
                <td>{$tire['nameMoney']}</td>
                <td>{$tire['year']}</td>
                <td>{$tire['other']}</td>
                <td>{$time}</td>
                <td><a class='link' rel='leanModal' id='{$tire['id']}'></a></td>
            </tr>"; ?>
        <? } ?>

        <div class="overlay signUp" id="overlay">
            <div class="overlayContent">
                <div id="orderModifyContent"></div>
                <div class="loading"></div>
            </div>
        </div>
        </tbody>
    </table>
    <hr/>

    <?
    if ($tiresChunk[1]) {
        echo "<div id='prevNextDiv'>";

        $prevPage = $page - 1;

        if ($prevPage == -1) {
            $prevPage = count($tiresChunk) - 1;
        }

        $nextPage = $page + 1;

        if ($nextPage > count($tiresChunk) - 1) {
            $nextPage = 0;
        }

        echo "<a class='nextPrev' href='" . App::url(['search/result', 'mode' => $mode, 'page' => $prevPage]) . "'>Предыдущая</a>";

        $linkStr = '';
        for ($i = 0; $i < count($tiresChunk); $i++) {
            $j = $i + 1;
            ($page == $i)
                ? $currentPageCss = "; font-size: 32px; color: gray; margin: 0px 0 0 10px;"
                : $currentPageCss = '';

            $linkStr .= "<a style=\"" . $currentPageCss . "\" href='"
                . App::url(['search/result', 'mode' => $mode, 'page' => $i]) . "'>$j</a>";
        }

        echo $linkStr;
        echo "<a class='nextPrev' style='' href='"
            . App::url(['search/result', 'mode' => $mode, 'page' => $nextPage]) . "'>Следующая</a>";
        echo "</div>";
    }
    ?>
</div>
