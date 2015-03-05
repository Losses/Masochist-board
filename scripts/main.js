/**
 * Created by Don on 1/31/2015.
 */

(function () {
    var bodyElement = $('body') /*nxt line*/
        , headerList = $('header>nav') /*nxt line*/
        , highlightItem = $('.highlight') /*nxt line*/
        , timeoutEvent = false;

    headerList.delegate('li', 'mouseenter', function () {
        var $this = $(this);

        if (timeoutEvent)
            clearTimeout(timeoutEvent);

        highlightItem.css('left', $this.position().left);
        $this.addClass('high');

        if (bodyElement.hasClass('touch_screen')) {
            timeoutEvent = setTimeout(function () {
                $this.removeClass('high');
                highlightItem.css('left', 1000);
            }, 1500);
        }
    }).delegate('li', 'mouseleave', function () {
        $(this).removeClass('high');
    });

    $('nav').on('mouseleave', function (event) {
        highlightItem.css('left', 1000);

        event.stopPropagation();
        event.preventDefault();
    });

    $('#close').on('click', function () {
        $('#highlight').slideUp();
    })
})();

$(function () {
    var hintElement = $('<span>').addClass('s-tooltip').text('STooltip by Losses Don');
    $(document.body)/*nxt line*/
        .delegate('[data-title]', 'mouseenter', function () {
            hintElement.text($(this).data('title'))
                .css({
                    'top': $(this).offset().top + $(this).height() + 3,
                    'left': $(this).offset().left + $(this).width() * 0.5 - $(hintElement).width() * 0.5 - 10
                })
                .addClass('show');
        })/*nxt line*/
        .delegate('[data-title]', 'mouseleave', function () {
            hintElement.removeClass('show');
        })/*nxt line*/
        .append(hintElement);
});