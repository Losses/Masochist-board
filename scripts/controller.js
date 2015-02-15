/**
 * Created by Don on 2/15/2015.
 */

function postCtrl($http, $scope, $routeParams) {

    processPageElement($routeParams);

    var page = 1
        , loading = false;

    $scope.posts = [];

    function pushContent() {
        if (!$routeParams.postId) {
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
                $http.get("api/?post&id=" + $routeParams.postId + "&page=" + page)
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
}

function emojiCtrl($http, $scope) {
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
}