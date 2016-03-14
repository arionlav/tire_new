$(document).ready(function(){
    $("input#action1").on('click', function() {
        $("a.cameraId").hide(100);
        $("a.seasonId").hide(100);
        $("a.groupId").hide(100);
        $("a.listId").hide(100);
        $("div.addDeleteInputs p").hide(200);
    });
    $("input#action2").on('click', function() {
        $("a.cameraId").show(100);
        $("a.seasonId").show(100);
        $("a.groupId").show(100);
        $("a.listId").show(100);
        $("div.addDeleteInputs p").show(200);
    });
    var arrOptionSelect = [3,4,5,6,11,12,10002,10003,10004,10005,10007,10006];
    for (var i in arrOptionSelect) {
        $('select[name="idListArr[]"] option[value="'+arrOptionSelect[i]+'"]').css({
            'color': 'crimson'
        });
    }

    var speed = 500;

    var rowInsertParam = {
        1: {1: 'widthId', 2: 'widthInsertParam'},
        2: {1: 'heightId', 2: 'heightInsertParam'},
        3: {1: 'radiusId', 2: 'radiusInsertParam'},
        4: {1: 'indexPowerId', 2: 'indexPowerInsertParam'},
        5: {1: 'indexSpeedId', 2: 'indexSpeedInsertParam'},
        6: {1: 'brandId', 2: 'brandInsertParam'},
        7: {1: 'cameraId', 2: 'cameraInsertParam'},
        8: {1: 'seasonId', 2: 'seasonInsertParam'},
        9: {1: 'groupId', 2: 'groupInsertParam'},
        10: {1: 'listId', 2: 'listInsertParam'}
    };

    function clickToParam (id, divId) {
        if ($('input[name="action"]:checked').val() == 1) {
            $('div#rowInsertParam').children().hide();
            $('div#rowInsertParam div#' + divId).fadeToggle(speed);
            $('div#idd').children().removeClass('clickLinkSearch');
            $('div.idd a.' + id).addClass('clickLinkSearch');
        }
        if ($('input[name="action"]:checked').val() == 2) {
            $('div#rowInsertParam').children().hide();
            $('div#rowInsertParam div#' + id).fadeToggle(speed);
            $('div#idd').children().removeClass('clickLinkSearch');
            $('div.idd a.' + id).addClass('clickLinkSearch');
        }
    }

    for (var i = 1; i <= 10; i++) {
        $('.' + rowInsertParam[i][1]).click(
            generate_handler(i)
        )
    }

    function generate_handler(i) {
        return function() {
            clickToParam (rowInsertParam[i][1], rowInsertParam[i][2]);
        }
    }


    var arrKey = [
        'width',
        'height',
        'radius',
        'indexPower',
        'indexSpeed',
        'brand',
        'camera',
        'season',
        'group',
        'isIt',
        'list',
        'str',
        'model',
        'other',
        'price'
    ];

    for(i in arrKey){
        if (arrKey[i] == 'str'
            || arrKey[i] == 'model'
            || arrKey[i] == 'other'
        ) {
            $('div#' + arrKey[i] + 'Id input').on('keyup mousemove',
                handlerQueryStrShort(arrKey[i])
            );
            continue;
        }

        if (arrKey[i] == 'price') {
            $('div#' + arrKey[i] + 'FromId input').on('keyup mousemove',
                handlerQueryStrShort(arrKey[i] + 'From')
            );
            $('div#' + arrKey[i] + 'ToId input').on('keyup mousemove',
                handlerQueryStrShort(arrKey[i] + 'To')
            );
            continue;
        }

        $('div#' + arrKey[i] + 'Id select').click(
            handlerQueryStr(arrKey[i])
        );
    }

    function handlerQueryStr(key) {
        return function() {
            queryStr (key);
        };
    }

    function handlerQueryStrShort(key) {
        return function() {
            queryStrShort (key);
        };
    }

    function queryStrShort (key) {
        var bigKey      = key.ucfirst(),
            selector    = $('div#' + key + 'Id input');

        if (selector.val() == '')
            $('div#content' + bigKey).text('-');
        else
            $('div#content' + bigKey).text(selector.val());
    }

    function queryStr (key) {
        var bigKey      = key.ucfirst(),
            writeHere   = 'div#content' + bigKey,
            div         = 'div#' + key + 'Id', // откуда событие отлавливать
            arrayName   = arr[key], // массив со значениями из БД
            requestKey  = 'request' + bigKey;

        getStr (div, arrayName, writeHere);

        var request = $('div#content' + bigKey).text();
        $("input[name=" + requestKey + "]").val(request);
        return true;
    }

    function getStr (div, arrayName, writeHere) {
        var valueIndexFirst = $(div + ' select').val(),
            valueIndex      = valueIndexFirst.toString();

        if (valueIndex.indexOf(',') == -1) {
            if (valueIndex == -1)
                $(writeHere).text('-');

            if (div == 'div#isItId')
                if (valueIndex == -2)
                    $(writeHere).text('Есть в наличии');
            for (i in arrayName) {
                if (i == valueIndex)
                    $(writeHere).text(arrayName[i]);
            }

            if (div == 'div#isItId')
                if (!valueIndex)
                    $(writeHere).text('Нет в наличии');

        } else {
            var str = '',
                valueIndexSplit = valueIndex.split(',');

            for (var i in valueIndexSplit) {
                if (valueIndexSplit[i] == -1) {
                    str = 'Любое значение';
                    break;
                }

                if (div == 'div#isItId')
                    if (valueIndexSplit[i] == -2) {
                        str = 'Есть в наличии';
                        break;
                    }
                for (var j in arrayName) {
                    if (j == valueIndexSplit[i])
                        str = str + arrayName[j] + ', ';
                }
                if (div == 'div#isItId')
                    if (!valueIndexSplit[i]) {
                        str = 'Нет в наличии';
                        break;
                    }
            }
            $(writeHere).text(str);
        }
    }

    // Скрыть все параетры при нажатии на радио
    $('input[type="radio"]').on('click', function () {
        $('div#rowInsertParam').children().hide();
    });
});