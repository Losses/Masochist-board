/**
 * Created by Don on 1/31/2015.
 */

(function () {
    var headerLis = document.querySelectorAll('.nav_item') /*nxt line*/
        , highlightItem = document.querySelector('.highlight');
    for (var x = 0; x < headerLis.length; x++) {
        $(headerLis[x]).on({
                'mouseenter': function (event) {
                    $(highlightItem).css('left', $(event.target).position().left);
                    $(this).addClass('high');

                    event.stopPropagation();
                },
                'mouseleave': function () {
                    $(this).removeClass('high');
                }
            }
        );
    }

    $('nav').on('mouseleave', function (event) {
        highlightItem.setAttribute('style', 'left:1000px');

        event.stopPropagation();
        event.preventDefault();
    });

    $('#close').on('click', function () {
        $('#highlight').slideUp();
    })
})();