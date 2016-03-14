$(function() {
    $('a[rel*=leanModal]').leanModal({ top : 200, closeButton: ".modal_close" });

    $('table#reductor tbody tr:odd').css('background-color', '#fff');
    $('table#reductor tbody tr:even').css('background-color', '#fffbf5');

    $('table#reductor tbody tr').on('mouseover', function (e) {
        $(this).css({
            'background-color': '#ffeed2',
            'color': '#000',
            '-webkit-transition': 'all 0.15s ease-out',
            '-moz-transition': 'all 0.15s ease-out',
            'transition': 'all 0.15s ease-out'});
    })

    $('table#reductor tr').on('mouseout', function (e) {
        $(this).css('background-color', '').css('color', '');
        $('table#reductor tbody tr:odd').css('background-color', '#fff');
        $('table#reductor tbody tr:even').css('background-color', '#fffbf5');
    })

    // движение цены
    $('td.priceUp').css({
        'background': "url(\"../img/priceUp.png\") 50% 50% no-repeat",
        'text-align': 'center',
        'color': 'crimson'
    });
    $('td.priceDown').css({
        'background': "url(\"../img/priceDown.png\") 50% 50% no-repeat",
        'text-align': 'center',
        'color': '#37932e'
    });
    $('td.priceStay').css({
        'background': "url(\"../img/priceStay.png\") 50% 50% no-repeat",
        'text-align': 'center'
    });

});

$(document).ready(function(){
    $( document ).ajaxStart(function() {
        $( ".log" ).css("display", "block");
    });
    $(document).ajaxSuccess(function() {
        $( ".log" ).css("display", "none");
    });
});