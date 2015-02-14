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
            submitIcon: $('input[type="submit"]')
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

    $('form').submit(function (event) {
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

        $.post('api/?new', postContent, function (data) {
            if (inPost)
                location.reload(true);
            if (data == 'false')
                return;
            var intervalItem = setInterval(function () {
                if (!flying)
                    window.location.href = 'post.html#' + data;
            }, 100);
        });

        event.preventDefault();
    });

    checkSumitable();
});