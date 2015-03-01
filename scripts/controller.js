/**
 * Created by Don on 2/15/2015.
 */

function globalCtrl($scope, $http, $routeParams) {
    losses.global = $scope;
    $scope.reloadCate = loadCate;
    $scope.router = $routeParams;
    $scope.categories = {};

    function loadCate() {
        $http.get("api/?category")
            .success(function (response) {
                for (var i = 0; i < response.length; i++) {
                    $scope.categories[response[i].id] = response[i];
                }
                $scope.firstCategoryKey = Object.keys($scope.categories)[0];


                setTimeout(checkFunctionMenu, 300);
                setTimeout(function () {
                    sSelect('.post_category');
                    sSelect('.transport_category');
                }, 1000);
            });
    };

    loadCate();
}

function postCtrl($http, $scope, $rootScope, $routeParams) {

    losses.scope.postCtrl = $scope;
    processPageElement($scope.router);

    var page = 1
        , loading = false;

    $scope.posts = [];

    function pushContent() {
        switchLoading(true);
        function getContent(apiRule) {
            if (!loading) {
                loading = true;
                $http.get(apiRule)
                    .success(function (response) {
                        page++;
                        if (response.length === 0) {
                            page--;
                        }

                        for (var i = 0; i <= response.length - 1; i++) {
                            $scope.posts.push(response[i]);
                        }

                        if (page == 2)
                            switchTitle();

                        loading = false;

                        switchLoading(false);
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
    }

    function switchTitle() {
        if ($routeParams.categoryId) {
            $rootScope.title = $scope.categories[$routeParams.categoryId - 1].name + ' - ';
        } else if ($routeParams.searchKey) {
            $rootScope.title = "搜索：" + $routeParams.searchKey + ' - ';
        } else if ($routeParams.postId) {
            $rootScope.title = $scope.posts[0].title + ' - ';
        } else {
            $rootScope.title = '';
        }
    }

    $(window).off('scroll.globalScroll')
        .on('scroll.globalScroll', function () {
            if ($(document).scrollTop() + $(window).height() >= $(document).height()) {
                pushContent();
            }
        });

    pushContent();
}

function dialogCtrl($http, $scope) {
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

function manageCtrl($scope) {
    losses.scope.manage = $scope;
}

function manageStarter() {
    losses.global.router.manage = true;

    if (!losses.logined) {
        callManageDialog();
    } else {
        losses.global.router.manage = false;
        location.href = '#/';
        location.refresh(true);
    }
}