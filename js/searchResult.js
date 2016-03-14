
function close_window() {
    close()
}

// Скроем/покажем статистику
$(function() {
    $('div#showItems').on('click', function () {
        $('div#itemsInPrice').toggle(150);
    });
    $('div#showBrands').on('click', function () {
        $('div#brands').toggle(150);
    });
    $('div#showCountries').on('click', function () {
        $('div#countries').toggle(150);
    });
});