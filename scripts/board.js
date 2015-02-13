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