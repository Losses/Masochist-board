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