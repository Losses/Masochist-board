/**
 * Created by Don on 3/7/2015.
 */

$(document).ready(function () {
    var imgArr = [
            {
                'name': '影舞出云',
                'index': 'http://oekaki.so/?p=douga&spec=m_profile&id=64683',
                'img': 'images/custom/theme_2.png'
            },
            {
                'name': 'かぐや',
                'index': 'http://oekaki.so/?p=douga&spec=m_profile&id=7015',
                'img': 'images/custom/theme_1.jpg'
            }
        ]
        , randNum = Math.floor(Math.random() * (imgArr.length))
        , paintInfo = $('<a>')
            .addClass('theme_author')
            .attr('href', imgArr[randNum].index)
            .text(imgArr[randNum].name);

    $('header').append(paintInfo)
        .addClass('theme_' + randNum);
});

