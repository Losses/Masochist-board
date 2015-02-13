/**
 * Created by Don on 2/13/2015.
 */
var mKnowledge = angular.module('mKnowledge', []);

mKnowledge.controller('getPostCtrl', function ($http, $scope) {

    $http.get("api/?list")
        .success(function (response) {
            $scope.posts = response;
        });
});

$(document).ready(function () {
    $('#new_post').click(function () {
        $('#post_dialog').addClass('flow_up')
            .removeClass('flow_down')
            .on('click.activeForm', function () {
                return false;
            });

        $('#new_post').addClass('hide')
            .removeClass('show');

        setTimeout(function () {
            $('body').on('click.activeBody', function () {
                $('#post_dialog').addClass('flow_down')
                    .removeClass('flow_up')
                    .off('click.activeForm');

                $(this).off('click.activeBody');

                $('#new_post').removeClass('hide')
                    .addClass('show');
            });
        }, 100);

    });
});