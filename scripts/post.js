/**
 * Created by Don on 2/13/2015.
 */
var mKnowledge = angular.module('mKnowledge', []);

mKnowledge.controller('getPostCtrl', function ($http, $scope) {
    var hash = window.location.hash.split('#')[1];
    $http.get("api/?post&id=" + hash)
        .success(function (response) {
            $scope.posts = response;
        });
});

$(document).ready(function () {
    $('#catchId').attr('value', window.location.hash.split('#')[1]);
});