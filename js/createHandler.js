$(function() {
    $('form input#submit').on('click', function() {
        $('div#load').fadeIn(500);
    });
    var spanError       = $('span#listNameError');
    var submit          = $("input[type='submit']");
    var inputListName   = $("input[name='listName']");

    function showError(s, currentValue) {
        for (var i in arrIndex) {
            if (currentValue == arrIndex[i]) {
                $(s).css({'color': 'red'});
                spanError.show();
                submit.prop("disabled", true).css({ 'background-color': '#eaeaea', 'border': 'none'});
                break;
            } else {
                $(s).css({'color': 'mediumseagreen'});
                spanError.hide();
                submit.prop("disabled", false).css({'background': '', 'border': ''});
            }
        }
    }
    inputListName.on('keyup', function () {
        var currentValue = $(this).val();
        showError($(this), currentValue);
    });
    $("div#startParams").on('mousemove', function () {
        var currentValue = inputListName.val();
        showError(inputListName, currentValue);
    });
});