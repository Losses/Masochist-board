/**
 * Created by Don on 2/15/2015.
 */

function postCtrl($http, $scope, $routeParams) {

    losses.router = $routeParams;
    processPageElement(losses.router);

    var page = 1
        , loading = false;

    $scope.posts = [];

    function pushContent() {
        function getContent(apiRule) {
            if (!loading) {
                loading = true;
                $http.get(apiRule)
                    .success(function (response) {
                        for (var i = 0; i <= response.length - 1; i++) {
                            $scope.posts.push(response[i]);
                        }
                        loading = false;
                    });
            }
        }

        if (!$routeParams.postId) {
            getContent("api/?list&page=" + page);
        } else if ($routeParams.categoryId) {
            getContent("api/?category=" + $routeParams.categoryId + "&page=" + page);
        } else if ($routeParams.searchKey) {
            getContent("api/?search=" + $routeParams.searchKey + "&page=" + page);
        } else {
            getContent("api/?post&id=" + $routeParams.postId + "&page=" + page);
        }

        page++;
    }

    $(window).on('scroll', function () {
        if ($(document).scrollTop() + $(window).height() >= $(document).height()) {
            pushContent();
        }
    });

    if (!$routeParams.postId) {
        $http.get("api/?category")
            .success(function (response) {
                losses.scope.categories = response;
                $scope.categories = response;
            });
    }

    pushContent();
}

function dialogCtrl($http, $scope, $interval) {
    $scope.groups = [];
    $scope.categories = [];

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

    var intervalItem = $interval(function () {
        if (losses.scope.categories !== undefined) {
            $scope.categories = losses.scope.categories;

            setTimeout(sSelect, 500);
            $interval.cancel(intervalItem);
        }
    }, 500);


}