/**
 * Created by Don on 1/31/2015.
 */

/// <reference path="../scripts/libs/jquery.js">

var body = $(document.body);
var highlight = $(".highlight");
var nav = $("header > nav");
nav.find("li").mouseenter((function () {
    var timeoutId;

    return function () {
        var $this = $(this);

        if (timeoutId)
            clearTimeout(timeoutId);

        highlight.css("left", $this.position().left).width($this.width());

        if (body.hasClass("touch_screen")) {
            timeoutId = setTimeout(function () {
                highlight.css("left", 1000);
            }, 500);
        }
    }
})());
nav.mouseleave(function () {
    if (!body.hasClass("touch_screen"))
        highlight.css("left", 1000);
});

$('#close').on('click', function () {
    $('#highlight').slideUp();
})

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
        });

    $('#common').append(hintElement);
});