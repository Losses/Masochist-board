/**
 * Created by Don on 1/31/2015.
 */

(function () {
    var headerList = $('header>nav') /*nxt line*/
        , highlightItem = document.querySelector('.highlight');

    headerList.delegate('li', 'mouseenter', function () {
        $(highlightItem).css('left', $(this).position().left);
        $(this).addClass('high');
    }).delegate('li', 'mouseleave', function () {
        $(this).removeClass('high');
    });

    $('nav').on('mouseleave', function (event) {
        highlightItem.setAttribute('style', 'left:1000px');

        event.stopPropagation();
        event.preventDefault();
    });

    $('#close').on('click', function () {
        $('#highlight').slideUp();
    })
})();