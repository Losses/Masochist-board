/**
 * Created by Don on 2/15/2015.
 */

function postCtrl($http, $scope, $routeParams) {

    losses.router = $routeParams;
    losses.scope.postCtrl = $scope;
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

        if ($routeParams.categoryId) {
            getContent("api/?list&category=" + $routeParams.categoryId + "&page=" + page);
        } else if ($routeParams.searchKey) {
            getContent("api/?list&search=" + $routeParams.searchKey + "&page=" + page);
        } else if ($routeParams.postId) {
            getContent("api/?post&id=" + $routeParams.postId + "&page=" + page);
        } else {
            getContent("api/?list&page=" + page);
        }

        page++;
    }

    $(window).on('scroll', function () {
        if ($(document).scrollTop() + $(window).height() >= $(document).height()) {
            pushContent();
        }
    });


    $http.get("api/?category")
        .success(function (response) {
            console.log(response);
            var category = [];
            category[0] = {name: '错误', theme: 'blue_gray'};
            for (var i = 0; i <= response.length - 1; i++) {
                category[response[i].id] = {
                    'name': response[i].name,
                    'theme': response[i].theme
                }
            }
            losses.data.categories = response;
            losses.scope.postCtrl.category = category;
            //losses.scope.postCtrl.$digest();
            $scope.categories = response;

        });


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
        if (losses.data.categories !== undefined) {
            $scope.categories = losses.data.categories;

            setTimeout(sSelect, 500);
            $interval.cancel(intervalItem);
        }
    }, 500);
}

function manageCtrl($scope) {
    losses.scope.manage = $scope;
}

function manageStarter() {
    losses.router.manage = true;

    if (!losses.logined) {
        callManageDialog();
    } else {
        losses.router.manage = false;
        location.href = '#/';
        location.refresh(true);
    }
}