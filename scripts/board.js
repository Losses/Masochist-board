/**
 * Created by Don on 2/13/2015.
 */
var mKnowledge = angular.module('mKnowledge', []);

mKnowledge.controller('getPostCtrl', function ($http, $scope) {

    var page = 1
        , loading = false;

    $scope.posts = [];

    function pushContent() {
        if (!loading) {
            loading = true;
            $http.get("api/?list&page=" + page)
                .success(function (response) {
                    for (var i = 0; i <= response.length - 1; i++) {
                        $scope.posts.push(response[i]);
                    }
                    loading = false;
                });

            page++;
        }
    }

    $(window).on('scroll', function () {
        if ($(document).scrollTop() + $(window).height() >= $(document).height()) {
            pushContent();
        }
    });

    pushContent();
});

$(document).ready(function () {
    var submitable = false
        , titleElement = $('input[name="title"]')
        , contentElement = $('textarea[name="content"]')
        , submitIcon = $('input[type="submit"]');

    function checkSumitable() {
        submitable = (
        (titleElement.val().length <= 35)
        && (titleElement.val().length !== 0)
        && (contentElement.val().length <= 35)
        && (contentElement.val().length !== 0)
        );

        if (!submitable) {
            submitIcon.addClass('lock');
        }
        else {
            submitIcon.removeClass('lock');
        }
    }

    titleElement.keypress(checkSumitable);

    contentElement.keypress(checkSumitable);

    $('form').submit(function (event) {
        var flying = true;
        if (submitable) {
            var postContent = {
                'author': $('input[name="author"]').val(),
                'title': $('input[name="title"]').val(),
                'content': $('textarea[name="content"]').val()
            };

            submitIcon.blur()
                .addClass('fly');

            setTimeout(function () {
                flying = false;
            }, 500);

            $.post('api/?new', postContent, function (data) {
                var intervalItem = setInterval(function () {
                    if (!flying)
                        window.location.href = 'post.html#' + data;
                }, 100);
            });
        }

        event.preventDefault();
    });

    checkSumitable();
});