/**
 * Created by Don on 1/31/2015.
 */

(function () {
    var bodyElement = $('body') /*nxt line*/
        , headerList = $('header>nav') /*nxt line*/
        , highlightItem = document.querySelector('.highlight') /*nxt line*/
        , timeoutEvent = false;

    headerList.delegate('li', 'mouseenter', function () {
        var that = this;
        if (timeoutEvent)
            clearTimeout(timeoutEvent);

        $(highlightItem).css('left', $(this).position().left);
        $(this).addClass('high');

        if (bodyElement.hasClass('touch_screen')) {
            timeoutEvent = setTimeout(function () {
                $(that).removeClass('high');
                highlightItem.setAttribute('style', 'left:1000px');
            }, 1500);
        }
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