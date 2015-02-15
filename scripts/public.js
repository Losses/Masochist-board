/**
 * Created by Don on 2/13/2015.
 */

var mKnowledge = angular.module('mKnowledge', []);

mKnowledge.controller('getPostCtrl', function ($http, $scope) {

    var page = 1
        , loading = false;

    $scope.posts = [];

    function pushContent() {
        if (!inPost) {
            if (!loading) {
                loading = true;
                $http.get("api/?list&page=" + page)
                    .success(function (response) {
                        for (var i = 0; i <= response.length - 1; i++) {
                            $scope.posts.push(response[i]);
                        }
                        loading = false;
                    });
            }
        } else {
            if (!loading) {
                loading = true;
                var hash = window.location.hash.split('#')[1];
                $http.get("api/?post&id=" + hash + "&page=" + page)
                    .success(function (response) {
                        for (var i = 0; i <= response.length - 1; i++) {
                            $scope.posts.push(response[i]);
                        }
                        loading = false;
                    });
            }
        }

        page++;
    }

    $(window).on('scroll', function () {
        if ($(document).scrollTop() + $(window).height() >= $(document).height()) {
            pushContent();
        }
    });

    pushContent();
});

mKnowledge.controller('emojiCtrl', function ($http, $scope) {
    $scope.groups = [];

    $http.get('dbs/emotions.json')
        .success(function (response) {
            for (var i in response) {            /*i是分组名*/
                var emojiCollection = [];
                for (var j in response[i]) {     /*j是替代文字*/
                    emojiCollection.push({
                        'name': response[i][j],
                        'value': 'sprite-' + j
                    })
                }
                $scope.groups.push({
                    'name': i,
                    'emoji': emojiCollection
                });
            }
        });
});

mKnowledge.filter('trustHtml', function ($sce) {
    return function (input) {
        return $sce.trustAsHtml(input);
    }
});

$(document).ready(function () {
    $('#new_post').click(function () {
        $('#post_dialog').addClass('flow_up')
            .removeClass('flow_down')
            .on('click.activeForm', function (event) {
                if (!$(event.target).hasClass('submit'))
                    return false;
            });

        $('#new_post').addClass('hide')
            .removeClass('show');

        setTimeout(function () {
            $('body').on('click.activeBody', function (event) {
                if (!$(event.target).hasClass('submit')) {
                    $('#post_dialog').addClass('flow_down')
                        .removeClass('flow_up')
                        .off('click.activeForm');

                    $(this).off('click.activeBody');

                    $('#new_post').removeClass('hide')
                        .addClass('show');

                }
            });
        }, 100);

    });

    var losses = {
        elements: {
            submitable: false,
            titleElement: !inPost ? $('input[name="title"]') : null,
            contentElement: $('textarea[name="content"]'),
            submitIcon: $('button[type="submit"]')
        }
    };

    function checkSumitable() {
        setTimeout(function () {
            losses.elements.submitable = inPost ? (
            (losses.elements.contentElement.val().length <= 35)
            && (losses.elements.contentElement.val().length !== 0)
            ) : (
            (losses.elements.titleElement.val().length <= 35)
            && (losses.elements.titleElement.val().length !== 0)
            && (losses.elements.contentElement.val().length <= 35)
            && (losses.elements.contentElement.val().length !== 0)
            );

            if (!losses.elements.submitable) {
                losses.elements.submitIcon.addClass('lock');
            }
            else {
                losses.elements.submitIcon.removeClass('lock');
            }
        }, 10);
    }

    if (!inPost) {
        losses.elements.titleElement.keypress(checkSumitable);
        losses.elements.titleElement.change(checkSumitable);
    }

    losses.elements.contentElement.keypress(checkSumitable);
    losses.elements.contentElement.change(checkSumitable);

    $('#post_form').submit(function (event) {
        event.preventDefault();

        if (!losses.elements.submitable)
            return;

        var flying = true;
        var postContent = {
            'author': $('input[name="author"]').val(),
            'title': !inPost ? $('input[name="title"]').val() : null,
            'content': $('textarea[name="content"]').val(),
            'upid': $('input[name="upid"]').val()
        };

        losses.elements.submitIcon.blur()
            .addClass('fly');

        setTimeout(function () {
            flying = false;
        }, 500);

        $(this).ajaxSubmit(function (data) {
            if (inPost)
                location.reload(true);
            if (data == 'false')
                return;
            var intervalItem = setInterval(function () {
                if (!flying)
                    window.location.href = 'post.html#' + data;
            }, 100);
        });
        /*
         $.ajax({
         type: "POST",
         url: "api/?new",
         data: $('#post_form').serialize(),
         contentType: "multipart/form-data",
         success: ''
         });
         */
    });

    $('.upload_image').click(function () {
        $('#upload_image_active').click();
    });

    $('.emoji_button').click(function () {
        $('.icon_group').mouseleave();
        $('.g-show').removeClass('g-show');
        $('.g-face').addClass('g-show');

        $('.icon-menu,.icon_group').each(function () {
            $(this).addClass('up');
        });
        losses.elements.contentElement.addClass('fold');
    });

    $('#emoji_box').click(function (event) {
        if (!$(event.target).hasClass('emoji'))
            return;
        var target = losses.elements.contentElement[0]
            , faceCode = ':' + $(event.target).attr('data-value') + ':';

        target.value = target.value.substring(0, target.selectionStart) + faceCode + target.value.substring(target.selectionEnd);
    });

    $('.group_select').click(function (event) {
        var targetAttr = $(event.target).attr('data-group-name');
        if (!targetAttr) {
            losses.elements.contentElement.removeClass('fold');
            $('.icon-menu,.icon_group').each(function () {
                $(this).removeClass('up');
            });
            return;
        }
        $('.g-show').removeClass('g-show');
        $('.' + targetAttr).addClass('g-show');
    });

    var iconGroup = $('.icon_group');

    $('.icon-menu').mouseenter(function () {
        var that = $(this);

        that.addClass('hide');
        iconGroup.addClass('extend bump')
            .one('mouseleave', function () {
                $(this).removeClass('extend bump');

                that.removeClass('hide');
            });
    });

    checkSumitable();
});