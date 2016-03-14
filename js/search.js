$(document).ready(function(){
    var speed = 500;

    String.prototype.ucfirst = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };

    /*
     JavaScript doesn't have block scope, just function scope. So each function you create in the loop is being created in the same variable environment, and as such they're all referencing the same i variable.

     To scope a variable in a new variable environment, you need to invoke a function that has a variable (or function parameter) that references the value you want to retain.

     Here we invoked the generate_handler() function, passed in arrKey[i], and had generate_handler() return a function that references the local variable (named key in the function, though you could name it arrKey[i] as well).

     The variable environment of the returned function will exist as long as the function exists, so it will continue to have reference to any variables that existed in the environment when/where it was created.
     */

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

    // Show / Hide selects on click link
    for(var i in arrKey){
        $('.' + arrKey[i] + 'Id').click(
            generate_handler(arrKey[i])
        )
    }

    function generate_handler(key) {
        return function() {
            showHideSelects (key);
        }
    }

    function showHideSelects (key) {
        $('div#selectsContent').children().hide();

        if (key == 'price') {
            $('#' + key + 'FromId').fadeToggle(speed);
            $('#' + key + 'ToId').fadeToggle(speed);
        } else
            $('#' + key + 'Id').fadeToggle(speed);

        $('div#idd').children().removeClass('clickLinkSearch');
        $(this).addClass('clickLinkSearch');
    }


    // Строку поиска соберем
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

        if (key == 'brand'
            || key == 'group'
            || key == 'camera'
            || key == 'season'
            || key == 'isIt'
            || key == 'list'
        ) {
            var request = $('div#content' + bigKey).text();
            $("input[name=" + requestKey + "]").val(request);
            return true;
        }

        var keyText = key + 'Text',
            s = $("input[name=" + keyText + "]"),
            request = $('div#content' + bigKey).text();

        if (request.indexOf(',') == -1)
            s.val(request);
        else
            s.val('...несколько значений...');

        $("input[name=" + requestKey + "]").val(request);

        $(s).css({color: ''});

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


    // Сброс параметров
    $('input[type="reset"]').on('click', function() {
        $('div.refresh').text('-');
        $("input[type='hidden']").val('');
    });


    // Инпуты ввода вручную
    var arrKeyHandInputs = [
        'width',
        'height',
        'radius',
        'indexPower',
        'indexSpeed'
    ];

    for(i in arrKeyHandInputs){
        $('input[name="' + arrKeyHandInputs[i] + 'Text"]').on('keyup',
            generateHandInput(arrKeyHandInputs[i])
        )
    }

    function generateHandInput(key) {
        return function() {
            handInput (key);
        }
    }

    function handInput (key) {
        for (var i in arr[key]) {
            var selector    = $('input[name="' + key + 'Text"]'),
                currValue   = arr[key][i];

            if (selector.val() == currValue) {
                inputForCustomWrite(key, arr[key], i);
                selector.css({color: ''});
                break;
            } else
                selector.css({color: 'red'});
        }
    }

    function inputForCustomWrite (key, arrIndex, i) {
        var keyUp   = key.ucfirst(),
            id      = 'id' + keyUp + 'Arr[]';

        $("select[name='" + id + "'] option").prop('selected', false);
        $("select[name='" + id + "'] option[value='" + i + "']").prop('selected', true);

        // scroll to
        var selector    = $("select[name='" + id + "']"),
            optionTop   = selector.find("[value='" + i + "']").offset().top,
            selectTop   = selector.offset().top;

        selector.scrollTop(selector.scrollTop() + (optionTop - selectTop - 75));

        // write result
        var writeHere   = 'div#content' + keyUp,
            div         = 'div#' + key + 'Id', // откуда событие отлавливать
            request     = $('div#content' + keyUp).text();

        getStr(div, arrIndex, writeHere);

        $("input[name='request" + keyUp + "']").val(request);
    }
});