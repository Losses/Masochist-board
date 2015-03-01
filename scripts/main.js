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

$(document).ready(function () {
    var hintElement = $('<span>')
        .attr('class', 's-tooltip')
        .html('STooltip by Losses Don');
    $(document.body)/*nxt line*/
        .delegate('[title],[data-title]', 'mouseenter', function () {
            if (typeof( $(this).attr('title') ) != 'undefined') {
                $(this).attr('data-title', $(this).attr('title'));
                $(this).removeAttr('title');
            }

            hintElement.html($(this).attr('data-title'))
                .css({
                    'top': $(this).offset().top + $(this).height() + 3,
                    'left': $(this).offset().left + $(this).width() * 0.5 - $(hintElement).width() * 0.5 - 10
                })
                .addClass('show');

        })/*nxt line*/
        .delegate('[title], [data-title]', 'mouseleave', function () {
            hintElement.removeClass('show');
        })/*nxt line*/
        .append(hintElement);
});