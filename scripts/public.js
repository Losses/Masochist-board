/**
 * Created by Don on 2/13/2015.
 */

var mKnowledge = angular.module('mKnowledge', ['ngRoute']);

mKnowledge.config(['$routeProvider',
    function ($routeProvider) {
        $routeProvider.
            when('/', {
                templateUrl: 'partials/list.html',
                controller: postCtrl
            }).
            when('/list/:listId', {
                templateUrl: 'partials/list.html',
                controller: postCtrl
            }).
            when('/category/:categoryId', {
                templateUrl: 'partials/list.html',
                controller: postCtrl
            }).
            when('/search/:searchKey', {
                templateUrl: 'partials/search.html',
                controller: postCtrl
            }).
            when('/post/:postId', {
                templateUrl: 'partials/post.html',
                controller: postCtrl
            })/*.
         when('/manage', {
         templateUrl: 'partials/manage.html',
         controller: manageStarter
         })*/.
            otherwise({redirectTo: '/'});
    }
]);

mKnowledge.controller('globalCtrl', globalCtrl);

mKnowledge.controller('dialogCtrl', dialogCtrl);

mKnowledge.controller('manageCtrl', manageCtrl);

mKnowledge.filter('trustHtml', function ($sce) {
    return function (input) {
        return $sce.trustAsHtml(input);
    }
});

var losses = {router: {}, scope: {}, data: {}, global: {}, multiSelect: []};

function processPageElement(routerResult) {
    losses.multiSelect = [];
    var body = $('body');

    losses.elements.dialogElement.hide();

    if (routerResult.postId) {
        body.addClass('post_page');
        losses.elements.titleElement.val('');
        losses.elements.upidElement.attr('value', routerResult.postId);
    }
    else {
        body.removeClass('post_page');
        losses.elements.upidElement.attr('value', 0);
    }

    if (routerResult.searchKey) {
        $('input[name="search"]').val(routerResult.searchKey);

        $('.search').addClass('extend');
    } else {
        $('.search').removeClass('extend');
    }

    setTimeout(function () {
        losses.elements.dialogElement.show()
    }, 300);


}

/*魔法！*/
function magicalLocation(path) {
    $('body').append('<a href= "' + path + '" id="magicalLocation" stype="display:none"></a>');

    $('#magicalLocation').click()
        .remove();
}

function sSelect(selector) {
    selector = selector ? selector : '.select_rebuild';

    var select = $(selector);

    function removeSelectBody() {
        setTimeout(function () {
            $("body").one("click.s_select_body", function () {
                $('.s_select_body').each(function () {
                    $(this).removeClass('selected');
                    $(document).off('.s_select_keydown');
                });
            });
        }, 1);
    }

    var selectBody = $(selector).find('.s_select_body')
        , choosenElement = $(selector).children('.s_choosen');


    choosenElement.click(function (event) {
        var that = this;
        var selectList = selectBody.children('li');

        if (!selectBody.hasClass("selected")) {
            $('body').off('.s_select_body');

            selectBody.addClass("selected");


            var _S_ = {
                select: {
                    itemId: -1,
                    totalNum: selectList.length - 1
                }
            };

            $(document).on('keydown.s_select_keydown', function (event) {
                selectList.each(function () {
                    $(this).addClass('keyboard');
                });

                $(selectList).one('mousemove', function () {
                    _S_.select.itemId = -1;
                    selectList.each(function () {
                        $(this).removeClass('selected keyboard');
                    });
                });

                function changeItem() {
                    if (_S_.select.itemId < 0) _S_.select.itemId = _S_.select.totalNum;
                    if (_S_.select.itemId > _S_.select.totalNum) _S_.select.itemId = 0;

                    selectList.each(function () {
                        $(this).removeClass("selected");
                    });

                    $(selectList[_S_.select.itemId]).addClass("selected");
                }

                if (event.which === 38) {
                    _S_.select.itemId -= 1;
                    changeItem();
                }
                if (event.which === 40) {
                    _S_.select.itemId += 1;
                    changeItem();
                }

                if (event.which === 13) {
                    $(that).blur();
                    $(selectList[_S_.select.itemId]).click();
                }

            });

            selectBody.delegate('li', 'click', function () {
                console.log('!');
                var selectObject = $(this).parents('.s_select').children('input');
                selectObject.val($(this).attr('val'))
                    .next('.s_choosen').html($(this).html());
                $(document).off(".s_select");

                selectObject.nextAll('.s_select_body').removeClass('selected');
            });

            event.preventDefault();

        }
        removeSelectBody();
    });
}

function publicWarning(text) {
    losses.elements.warningElement.html(text)
        .addClass('show');

    setTimeout(function () {
        losses.elements.warningElement.removeClass('show');
    }, 2000);
}

function manageLoginProcess() {
    if (losses.global.logined) {
        $('body').addClass('manager');

        $.getScript('scripts/manage.js');

        losses.global.reloadCate();

        var intervalItem = setInterval(function () {
            if (losses.scope.postCtrl) {
                losses.global.logined = true;
                losses.global.$digest();
                clearInterval(intervalItem);
            }
        }, 500);

    }
}

function switchLoading(status) {
    var loading = $('.loading_spin');
    if (status) {
        loading.addClass('down');
    } else {
        setTimeout(function () {
            loading.removeClass('down');
        }, 1000);

    }
}

$(document).ready(function documentReady() {
    losses.elements = {
        submitable: false,
        pause: false,
        submited: false,
        dialogElement: $('#post_dialog'),
        titleElement: $('input[name="title"]'),
        contentElement: $('textarea[name="content"]'),
        upidElement: $('input[name="upid"]'),
        submitIcon: $('.post_submit'),
        iconGroupElement: $('.icon_group'),
        warningElement: $('.public_warning')
    };
    losses.event = {
        menuTimeout: null
    };

    function checkSumitable() {
        setTimeout(function () {
            if (!losses.global.router)
                return;
            losses.elements.submitable = losses.global.router.postId ? (
            (losses.elements.contentElement.val().length <= 10000)
            && (losses.elements.contentElement.val().length !== 0)
            ) : (
            (losses.elements.titleElement.val().length <= 35)
            && (losses.elements.titleElement.val().length !== 0)
            && (losses.elements.contentElement.val().length <= 10000)
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

    (function lightBox() {
        /*lightbox*/
        $('body').append('<section class="lightbox"><div class="darkness"><img src="" class="image" /></section>')
            .delegate('.lbox', 'click', function () {
                var scale = 1;

                var that = this
                    , imageElement = $('.lightbox .image');

                imageElement.attr('src', $(this).attr('src'))
                    .css({
                        'margin-top': -(imageElement.height() / 2),
                        'margin-left': -(imageElement.width() / 2)
                    });


                $(this).addClass('up');
                $('.lightbox').addClass('up');

                var wheelFrame = false;

                $(window).bind('mousewheel.lightboxwheel', function (event, delta) {
                    if (!wheelFrame) {
                        wheelFrame = true;
                        scale += delta * 0.08;

                        imageElement.css('transform', 'scale(' + scale + ')');

                        setTimeout(function () {
                            wheelFrame = false;
                        }, 30);
                    }
                    event.preventDefault();
                });

                var moving = false;

                imageElement.on('mousedown', function (event) {
                    event.preventDefault();
                    var x = event.pageX
                        , y = event.pageY
                        , imageX = parseInt(imageElement.css('margin-left'))
                        , imageY = parseInt(imageElement.css('margin-top'))
                        , frame = false;

                    imageElement.css('cursor', 'move');

                    $(window).on('mousemove.lmove', function (event) {
                        if (frame)
                            return;

                        var moveX = x - event.pageX
                            , moveY = y - event.pageY;

                        moving = true;

                        imageElement.css({
                            'margin-left': imageX - moveX,
                            'margin-top': imageY - moveY
                        });

                        frame = true;

                        setTimeout(function () {
                            frame = false
                        }, 30);
                    })
                        .on('mouseup.lup', function () {
                            imageElement.css('cursor', 'default');
                            $(this).off('mousemove.lmove')
                                .off('mouseup.lup');
                        });
                });

                $('.darkness').on('click.lclick', function () {
                    if (moving) {
                        moving = false;
                    } else {
                        $(that).removeClass('up');
                        $('.lightbox').removeClass('up');
                        imageElement.attr('src', '')
                            .css({
                                'margin-top': 0,
                                'margin-left': 0,
                                'transform': 'default'
                            });
                        $(window).unbind('mousewheel.lightboxwheel');
                    }
                });
            });
    })();

    switchLoading(true);
    $.post('api/?manage', {'check': ''}, function (data) {
        switchLoading(false);
        var response = JSON.parse(data);

        losses.global.logined = (response.message);
        losses.global.$digest();

        manageLoginProcess();
    });


    $('#new_post>i').click(function () {

        $('#post_dialog').addClass('flow_up');

        $('.float_icon').addClass('hide')
            .removeClass('show');

        setTimeout(function () {
            $('body').on('click.activeBody', function (event) {
                if ($(event.target).parents('#post_dialog').length == 0) {
                    $('#post_dialog').removeClass('flow_up');

                    $(this).off('click.activeBody');

                    $('#new_post').removeClass('hide')
                        .addClass('show');

                }
            });
        }, 100);

    });

    $('.checkbox_rebuild>input').each(function () {
        $(this).on("change", function () {
            var that = $(this);
            setTimeout(function () {
                if (that.is(":checked"))
                    that.parent("label").addClass("selected");
                else
                    that.parent("label").removeClass("selected");
            }, 10);
        });
    });

    $('#post_form').submit(function (event) {
        event.preventDefault();
    });

    $(losses.elements.dialogElement).delegate('input,textarea', 'input change propertychange', checkSumitable);

    losses.elements.submitIcon.click(function () {
        if (!losses.elements.submitable) {
            losses.elements.submitIcon.removeClass('shake');
            setTimeout(function () {
                losses.elements.submitIcon.addClass('shake');
            }, 100);

            return;
        }

        var flying = true;

        losses.elements.submitIcon.blur()
            .addClass('fly');

        switchLoading(true);

        setTimeout(function () {
            flying = false;
        }, 500);

        $('#post_form').ajaxSubmit(function (data) {
            try {
                data = JSON.parse(data);
            } catch (e) {
                publicWarning(data);
            }
            if (data.code != 200) {
                losses.elements.submitIcon.removeClass('fly');
                switchLoading(false);
                publicWarning(data.message);
                return;
            }

            if (losses.global.router.postId) {
                var date = new Date()
                    , month = ((date.getMonth() + 1) > 9) ? (date.getMonth() + 1) : '0' + date.getMonth()
                    , day = (date.getDate() > 9) ? date.getDate() : '0' + date.getDate()
                    , time = date.getFullYear() + '-' + month + '-' + day + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
                losses.scope.postCtrl.posts.push({
                    title: '',
                    author: 'a person', /*二次开发注意需要!*/
                    time: time,
                    content: $('textarea[name="content"]')[0].value,
                    img: losses.data.lastImg
                });
                losses.scope.postCtrl.$digest();
            }

            function finishProcess() {
                $('body').click();
                losses.elements.submitIcon.removeClass('fly');
                $('#post_form')[0].reset();
                removeImage();
                $('#post_dialog .checkbox_rebuild').removeClass('selected');
                if (!losses.global.router.postId)
                    magicalLocation('#/post/' + data.message);
            }


            if (!flying) {
                finishProcess();
            } else {
                setTimeout(finishProcess, 500);
            }

            switchLoading(false);

        });
    });

    $('body').delegate('#search', 'submit', function (event) {
        event.preventDefault();
        magicalLocation('#/search/' + $('input[name="search"]').val());
    })
        .delegate('.post', 'click', function () {
            var that = $(this);

            if (!losses.global.logined)
                return;

            var multiSelectElement = $(this).children('.multi_select');
            setTimeout(function () {
                if (multiSelectElement.is(':checked')) {
                    $(that).removeClass('selected');
                    losses.multiSelect.splice($.inArray(multiSelectElement.attr('data-post-id'), losses.multiSelect), 1);
                    multiSelectElement.prop("checked", false);
                } else {
                    $(that).addClass('selected');
                    losses.multiSelect.push(multiSelectElement.attr('data-post-id'));
                    multiSelectElement.prop("checked", true);
                }
            }, 100);
        });

    losses.elements.iconGroupElement.delegate('#upload_image_active', 'change', function (evt) {
        var element = $('.hint')
            , icon = $('.icon-picture')
            , target = this
            , f = evt.target.files[0]
            , reader = new FileReader();

        function warning(text) {
            losses.elements.pause = true;

            element.html(text)
                .addClass('bubbling');

            var iconClass = icon.hasClass('selected') ? 'shine_green' : 'shine_gray';

            icon.addClass(iconClass);
            $('.upload_warp').removeClass('selected');

            setTimeout(function () {
                element.removeClass('bubbling');
                icon.removeClass('shine_gray shine_green selected');
            }, 2000);
        }

        if (!f.type.match('image.*')) {
            warning('您选择的文件不是图片，请选择一张图片。');
            target.outerHTML = target.outerHTML;

            return false;
        }

        reader.onload = (function (theFile) {

            return function (e) {
                // Render thumbnail.
                $('.image_preview')[0].innerHTML = ['<img class="thumb" src="', e.target.result,
                    '" title="', escape(theFile.name), '"/>'].join('');

                $('.upload_warp').addClass('selected');
                icon.addClass('selected');

                losses.data.lastImg = e.target.result;
            };
        })(f);

        reader.readAsDataURL(f);
    });

    function removeImage() {
        var target = $('#upload_image_active')[0];
        target.outerHTML = target.outerHTML;

        $('.upload_image').removeClass('selected');
        $('.upload_warp').removeClass('selected');
    }

    $('.upload_image').click(function () {
        $('#upload_image_active').click();
    });

    $('.remove_image').click(removeImage);

    $('.emoji_button').click(function () {
        losses.elements.iconGroupElement.mouseleave();
        $('.g-show').removeClass('g-show');
        $('.g-face').addClass('g-show');

        $('.icon_group').toggleClass('up');
        $('#post_dialog').toggleClass('fold');
    });

    $('#emoji_box').click(function (event) {
        if (!$(event.target).hasClass('emoji'))
            return;
        var target = losses.elements.contentElement[0]
            , faceCode = ':' + $(event.target).attr('data-value') + ':';

        target.value = target.value.substring(0, target.selectionStart) + faceCode + target.value.substring(target.selectionEnd);
    });

    $('.group_select').click(function (event) {
        var targetAttr = $(event.target).attr('data-group-name');
        if (!targetAttr) {
            $('#post_dialog').removeClass('fold');
            $('.icon_group').removeClass('up');
            return;
        }
        $('.g-show').removeClass('g-show');
        $('.' + targetAttr).addClass('g-show');
    });

    $('.close_dialog').click(function () {
        $('body').click();
    });

    setTimeout(checkSumitable, 1000);
})
;

function callManageDialog() {

    function requireCode() {
        switchLoading(true);
        $.post('api/?manage', {'key': ''}, function (data) {
            switchLoading(false);
            var response = JSON.parse(data);
            if (response.code === 200)
                losses.key = response.message;
            else {
                losses.key = null;
                publicWarning('无法与服务器正确沟通！');
            }

            console.log(response);
        });
    }

    function hideDialog() {
        manageElement.removeClass('up');
        inputElement.val('');
        $(this).off('click.manage');
        manageElement.off('submit.lsubmit');
    }

    var manageElement = $('.manage')
        , inputElement = $('.manage>input');
    manageElement.addClass('up');
    inputElement.focus();

    requireCode();

    manageElement.on('submit.lsubmit', function () {
        switchLoading(true);
        $.post('api/?manage', {'password': md5(md5(inputElement.val()) + losses.key)}, function (data) {
            switchLoading(false);
            var response = JSON.parse(data);

            if (response.code === 200) {
                losses.key = null;
                losses.global.logined = true;
                losses.global.$digest();
                hideDialog();
                manageLoginProcess();
                publicWarning('Welcome, my master nyan~~');

                if (losses.global.router.manage) {
                    magicalLocation('#/');
                    location.refresh(true);
                }
            }
            else {
                requireCode();
                manageElement.removeClass('shake');

                setTimeout(function () {
                    manageElement.addClass('shake');
                }, 10);

                losses.key = null;
                publicWarning('密钥错误，请重新输入。');
            }
        });
    });

    $('body').on('click.manage', function (event) {
        if (losses.global.router.manage)
            return;

        if ($(event.target).hasClass('password_acion')) {
            return false;
        } else {
            hideDialog();
        }
    });
    pointer = 0;
}

(function () {
    var pointer = 0;
    $(document).on('keydown.passwordCheck', function (event) {
        if (losses.global.logined)
            return;

        var callAction = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];

        if ((event.keyCode == callAction[pointer])
            || (event.which === callAction[pointer])) {
            pointer++;
        } else {
            pointer = 0;
        }

        if (pointer >= callAction.length) {
            callManageDialog();
        }
    });
})();

console.log('%c', 'background-image:url("https://raw.githubusercontent.com/Losses/Masochist-board/1392eb7b16a95a832dee28dcbaa27b24e8ce7fbf/images/about.png");padding:77px 225px;line-height:154px;height:1px');