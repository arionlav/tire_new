$(function(){
    $('form input#submit').on('click', function() {
        $('div#load').fadeIn(500);
    });

    // Коэффициент цены
    var speed = 200;
    $('input#oneForAll').on('click', function () {
        $('div#oneForAllDiv').show(speed);
        $('div#firstIf').hide(speed);
        $('div#secondIf').hide(speed);
        $('div#thirdIf').hide(speed);
        $('div#priceChangeForOther').hide(speed);
        $('input[name="forWho1"]').prop('checked', false);
        $('input[name="forWho2"]').prop('checked', false);
        $('input[name="forWho3"]').prop('checked', false);
        $("select[name='idBrand1']").hide(speed);
        $("select[name='idGroup1']").hide(speed);
        $("select[name='idBrand2']").hide(speed);
        $("select[name='idGroup3']").hide(speed);
        $("select[name='idBrand3']").hide(speed);
    });
    $('input#custom').on('click', function () {
        $('div#firstIf').show(speed);
        $('div#priceChangeForOther').show(speed);
        $('div#oneForAllDiv').hide(speed);
        $('input[name="forWho1"]').prop('checked', false);
        $('input[name="forWho2"]').prop('checked', false);
        $('input[name="forWho3"]').prop('checked', false);
    });

    $('a#addParam2').on('click', function () {
        $('div#secondIf').toggle(speed);
        $('div#thirdIf').hide(speed);
        $('input[name="forWho2"]').prop('checked', false);
        $('input[name="forWho3"]').prop('checked', false);
        $("select[name='idBrand2']").hide(speed);
        $("select[name='idGroup2']").hide(speed);
    });

    $('a#addParam3').on('click', function () {
        $('div#thirdIf').toggle(speed);
        $('input[name="forWho3"]').prop('checked', false);
        $("select[name='idBrand3']").hide(speed);
        $("select[name='idGroup3']").hide(speed);
    });

    $('input#forBrand1').on('click', function () {
        $("select[name='idBrand1']").show(speed);
        $("select[name='idGroup1']").hide(speed);
    });
    $('input#forGroup1').on('click', function () {
        $("select[name='idGroup1']").show(speed);
        $("select[name='idBrand1']").hide(speed);
    });

    $('input#forBrand2').on('click', function () {
        $("select[name='idBrand2']").show(speed);
        $("select[name='idGroup2']").hide(speed);
    });
    $('input#forGroup2').on('click', function () {
        $("select[name='idGroup2']").show(speed);
        $("select[name='idBrand2']").hide(speed);
    });

    $('input#forBrand3').on('click', function () {
        $("select[name='idBrand3']").show(speed);
        $("select[name='idGroup3']").hide(speed);
    });
    $('input#forGroup3').on('click', function () {
        $("select[name='idGroup3']").show(speed);
        $("select[name='idBrand3']").hide(speed);
    });
    // Show notice when choose Petr
    $('select#idList').on('click', function () {
        if ($('select#idList').val() == '4') {
            $("div#noticeForPetr").show(speed);
            $("div#swimFactorBlock").hide(speed);
            $("div#swimFactor").hide(speed);
        } else {
            $("div#noticeForPetr").hide(speed);
            $("div#swimFactorBlock").show(speed);
            $("div#swimFactor").show(speed);
        }
    });
})