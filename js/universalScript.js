$(function () {
    var speed = 200;
    // Width (показать/скрыть варианты)
    $('input#widthIsRow').on('click', function () {
        $('div#widthIsRowDiv').show(speed);
        $('div#widthIsStrDiv').hide(speed);
        $('div#widthIsStrDiv input[type="radio"]').prop('checked', false);
    });
    $('input#widthIsStr').on('click', function () {
        $('div#widthIsStrDiv').show(speed);
        $('div#widthIsRowDiv').hide(speed);
        $('div#widthIsStrDiv input[type="radio"]').prop('checked', true);
        $('div#widthIsRowDiv input[type="text"]').val('');
        $('div#widthIsRowDiv input[type="checkbox"]').prop('checked', false);
    });

    // Height (показать/скрыть варианты)
    $('input#heightIsRow').on('click', function () {
        $('div#heightIsRowDiv').show(speed);
        $('div#heightIsStrDiv').hide(speed);
        $('div#heightIsStrDiv input[type="radio"]').prop('checked', false);
    });
    $('input#heightIsStr').on('click', function () {
        $('div#heightIsStrDiv').show(speed);
        $('div#heightIsRowDiv').hide(speed);
        $('div#heightIsStrDiv input[type="radio"]').prop('checked', true);
        $('div#heightIsRowDiv input[type="text"]').val('');
    });

    // Radius (показать/скрыть варианты)
    $('input#radiusIsRow').on('click', function () {
        $('div#radiusIsRowDiv').show(speed);
        $('div#radiusIsStrDiv').hide(speed);
        $('div#radiusIsStrDiv input[type="radio"]').prop('checked', false);
    });
    $('input#radiusIsStr').on('click', function () {
        $('div#radiusIsStrDiv').show(speed);
        $('div#radiusIsRowDiv').hide(speed);
        $('div#radiusIsStrDiv input[type="radio"]').prop('checked', true);
        $('div#radiusIsRowDiv input[type="text"]').val('');
    });

    // Index power (показать/скрыть варианты)
    $('input#indexPowerIsRow').on('click', function () {
        $('div#indexPowerIsRowDiv').show(speed);
        $('div#indexPowerIsStrDiv').hide(speed);
        $('div#indexPowerIsStrDiv input[type="radio"]').prop('checked', false);
    });
    $('input#indexPowerIsStr').on('click', function () {
        $('div#indexPowerIsStrDiv').show(speed);
        $('div#indexPowerIsRowDiv').hide(speed);
        $('div#indexPowerIsStrDiv input[type="radio"]').prop('checked', true);
        $('div#indexPowerIsRowDiv input[type="text"]').val('');
    });

    // Index speed (показать/скрыть варианты)
    $('input#indexSpeedIsRow').on('click', function () {
        $('div#indexSpeedIsRowDiv').show(speed);
        $('div#indexSpeedIsStrDiv').hide(speed);
        $('div#indexSpeedIsStrDiv input[type="radio"]').prop('checked', false);
    });
    $('input#indexSpeedIsStr').on('click', function () {
        $('div#indexSpeedIsStrDiv').show(speed);
        $('div#indexSpeedIsRowDiv').hide(speed);
        $('div#indexSpeedIsStrDiv input[type="radio"]').prop('checked', true);
        $('div#indexSpeedIsRowDiv input[type="text"]').val('');
    });

    // Model (показать/скрыть варианты)
    $('input#modelIsRow').on('click', function () {
        $('div#modelIsRowDiv').show(speed);
        $('div#modelIsStrDiv').hide(speed);
        $('div#modelIsStrDiv input[type="radio"]').prop('checked', false);
        $('div#modelIsStrDiv input[type="checkbox"]').prop('checked', false);
        $('div#modelIsStrDiv input[type="text"]').val('');
    });
    $('input#modelIsStr').on('click', function () {
        $('div#modelIsStrDiv').show(speed);
        $('div#modelIsRowDiv').hide(speed);
        $('div#modelIsStrDiv input[type="radio"]').prop('checked', true);
        $('div#modelIsRowDiv input[type="text"]').val('');
    });

    // Brand (показать/скрыть варианты)
    $('input#brandIsRow').on('click', function () {
        $('div#brandIsRowDiv').show(speed);
        $('div#brandIsStrDiv').hide(speed);
        $('div#brandIsLonelyDiv').hide(speed);
        $('div#brandOneDiv').hide(speed);
        $('div#brandIsOneForAllBottom').hide(speed);
        $('div#brandIsRowAnywereDiv').hide(speed);
        $('div#brandIsStrDiv input[type="radio"]').prop('checked', false);
        $('div#brandIsLonelyDiv input[type="text"]').val('');
        $('div#brandRowOneForAllBottom input[type="text"]').val('');
        $('div#brandIsRowAnywere input[type="text"]').val('');
    });
    $('input#brandIsStr').on('click', function () {
        $('div#brandIsStrDiv').show(speed);
        $('div#brandIsRowDiv').hide(speed);
        $('div#brandIsLonelyDiv').hide(speed);
        $('div#brandOneDiv').hide(speed);
        $('div#brandIsOneForAllBottom').hide(speed);
        $('div#brandIsRowAnywereDiv').hide(speed);
        $('div#brandIsRowDiv input[type="text"]').val('');
        $('div#brandIsLonelyDiv input[type="text"]').val('');
        $('div#brandRowOneForAllBottom input[type="text"]').val('');
        $('div#brandIsRowAnywere input[type="text"]').val('');
    });
    $('input#brandIsLonely').on('click', function () {
        $('div#brandIsLonelyDiv').show(speed);
        $('div#brandIsStrDiv').hide(speed);
        $('div#brandIsRowDiv').hide(speed);
        $('div#brandOneDiv').hide(speed);
        $('div#brandIsOneForAllBottom').hide(speed);
        $('div#brandIsRowAnywereDiv').hide(speed);
        $('div#brandIsStrDiv input[type="radio"]').prop('checked', false);
        $('div#brandIsRowDiv input[type="text"]').val('');
        $('div#brandRowOneForAllBottom input[type="text"]').val('');
        $('div#brandIsRowAnywere input[type="text"]').val('');
    });
    $('input#brandOne').on('click', function () {
        $('div#brandOneDiv').show(speed);
        $('div#brandIsStrDiv').hide(speed);
        $('div#brandIsRowDiv').hide(speed);
        $('div#brandIsOneForAllBottom').hide(speed);
        $('div#brandIsLonelyDiv').hide(speed);
        $('div#brandIsRowAnywereDiv').hide(speed);
        $('div#brandIsStrDiv input[type="radio"]').prop('checked', false);
        $('div#brandIsRowDiv input[type="text"]').val('');
        $('div#brandIsLonelyDiv input[type="text"]').val('');
        $('div#brandRowOneForAllBottom input[type="text"]').val('');
        $('div#brandIsRowAnywere input[type="text"]').val('');
    });
    $('input#brandIsOneForAllBottom').on('click', function () {
        $('div#brandIsOneForAllBottom').show(speed);
        $('div#brandIsStrDiv').hide(speed);
        $('div#brandIsRowDiv').hide(speed);
        $('div#brandOneDiv').hide(speed);
        $('div#brandIsLonelyDiv').hide(speed);
        $('div#brandIsRowAnywereDiv').hide(speed);
        $('div#brandIsStrDiv input[type="radio"]').prop('checked', false);
        $('div#brandIsRowDiv input[type="text"]').val('');
        $('div#brandIsLonelyDiv input[type="text"]').val('');
        $('div#brandIsRowAnywere input[type="text"]').val('');
    });
    $('input#brandIsRowAnywere').on('click', function () {
        $('div#brandIsRowAnywereDiv').show(speed);
        $('div#brandIsOneForAllBottom').hide(speed);
        $('div#brandIsStrDiv').hide(speed);
        $('div#brandIsRowDiv').hide(speed);
        $('div#brandOneDiv').hide(speed);
        $('div#brandIsLonelyDiv').hide(speed);
        $('div#brandIsStrDiv input[type="radio"]').prop('checked', false);
        $('div#brandIsRowDiv input[type="text"]').val('');
        $('div#brandIsLonelyDiv input[type="text"]').val('');
        $('div#brandRowOneForAllBottom input[type="text"]').val('');
    });

    // Group (показать/скрыть варианты)
    $('input#groupIsRow').on('click', function () {
        $('div#groupIsRowDiv').show(speed);
        $('div#groupIsStrDiv').hide(speed);
        $('div#groupIsLonelyDiv').hide(speed);
        $('div#groupIsLonelyDiv input[type="text"]').val('');
    });
    $('input#groupIsStr').on('click', function () {
        $('div#groupIsStrDiv').show(speed);
        $('div#groupIsRowDiv').hide(speed);
        $('div#groupIsLonelyDiv').hide(speed);
        $('div#groupIsRowDiv input[type="text"]').val('');
        $('div#groupIsLonelyDiv input[type="text"]').val('');
    });
    $('input#groupIsLonely').on('click', function () {
        $('div#groupIsLonelyDiv').show(speed);
        $('div#groupIsStrDiv').hide(speed);
        $('div#groupIsRowDiv').hide(speed);
        $('div#groupIsRowDiv input[type="text"]').val('');
    });

    // Season (показать/скрыть варианты)
    $('input#seasonIsRow').on('click', function () {
        $('div#seasonIsRowDiv').show(speed);
        $('div#seasonIsStrDiv').hide(speed);
        $('div#seasonIsLonelyDiv').hide(speed);
        $('div#seasonIsLonelyDiv input[type="text"]').val('');
    });
    $('input#seasonIsStr').on('click', function () {
        $('div#seasonIsStrDiv').show(speed);
        $('div#seasonIsRowDiv').hide(speed);
        $('div#seasonIsLonelyDiv').hide(speed);
        $('div#seasonIsRowDiv input[type="text"]').val('');
        $('div#seasonIsLonelyDiv input[type="text"]').val('');
    });
    $('input#seasonIsLonely').on('click', function () {
        $('div#seasonIsLonelyDiv').show(speed);
        $('div#seasonIsStrDiv').hide(speed);
        $('div#seasonIsRowDiv').hide(speed);
        $('div#seasonIsRowDiv input[type="text"]').val('');
    });

    // Camera (показать/скрыть варианты)
    $('input#cameraIsRow').on('click', function () {
        $('div#cameraIsRowDiv').show(speed);
        $('div#cameraIsStrDiv').hide(speed);
    });
    $('input#cameraIsStr').on('click', function () {
        $('div#cameraIsStrDiv').show(speed);
        $('div#cameraIsRowDiv').hide(speed);
        $('div#cameraIsRowDiv input[type="text"]').val('');
    });

    // Model (показать/скрыть варианты)
    $('input#moneyIsOne').on('click', function () {
        $('div#moneyIsOneDiv').show(speed);
        $('div#moneyIsRowDiv').hide(speed);
        $('div#moneyIsRowDiv input[type="text"]').val('');
    });
    $('input#moneyIsRow').on('click', function () {
        $('div#moneyIsRowDiv').show(speed);
        $('div#moneyIsOneDiv').hide(speed);
    });

    // Note (other) (показать/скрыть варианты)
    $('input#otherIsRow').on('click', function () {
        $('div#otherIsRowDiv').show(speed);
        $('div#otherIsLonelyDiv').hide(speed);
        $('div#otherIsLonelyDiv input[type="text"]').val('');
    });
    $('input#otherIsLonely').on('click', function () {
        $('div#otherIsLonelyDiv').show(speed);
        $('div#otherIsRowDiv').hide(speed);
        $('div#otherIsRowDiv input[type="text"]').val('');
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
        $("select[name='idGroup3']").hide(speed);
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
});