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
                templateUrl: 'partials/list.html',
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

mKnowledge.controller('dialogCtrl', dialogCtrl);

mKnowledge.controller('manageCtrl', manageCtrl);

mKnowledge.filter('trustHtml', function ($sce) {
    return function (input) {
        return $sce.trustAsHtml(input);
    }
});

var losses = {router: {}, scope: {}, data: {}, multiSelect: []};

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

    select.each(function () {

        $(this).wrap('<span class="s_select"></span>');

        $(this).parent('.s_select').append('<button class="s_choosen"></button><ul class="s_select_body"></ul>');

        var options = [],
            values = [],
            classes = [],
            x;
        $(this).children('option').each(function () {
            var className = $(this).attr('ls-class') ? $(this).attr('ls-class') : '';
            options.push($(this).html());
            values.push($(this).attr('value'));
            classes.push(className);
        });
        $(this).next('.s_choosen').html(options[0]);
        var selectBody = $(this).nextAll('.s_select_body');
        for (x in options) {
            selectBody.append('<li val="' + values[x] + '" class="' + classes[x] + '">' + options[x] + '</li>');
        }
    });

    $('.s_choosen').click(function (event) {
        var that = this;
        var selectBody = $(this).next('.s_select_body');
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

            $('.s_select_body>li').click(function () {
                var selectObject = $(this).parents('.s_select').children('select');
                selectObject.val($(this).attr('val'));
                selectObject.next('.s_choosen').html($(this).html());
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
    if (losses.logined) {
        $('body').addClass('manager');

        $.getScript('scripts/manage.js');
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
        submitIcon: $('button[type="submit"]'),
        iconGroupElement: $('.icon_group'),
        warningElement: $('.public_warning')
    };
    losses.event = {
        menuTimeout: null
    };

    function checkSumitable() {
        setTimeout(function () {
            if (!losses.router)
                return;
            losses.elements.submitable = losses.router.postId ? (
            (losses.elements.contentElement.val().length <= 35)
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
                        , frame = false
                        , intervalEvent;

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

    $.post('api/?manage', {'check': ''}, function (data) {
        var response = JSON.parse(data);

        losses.logined = (response.message);

        manageLoginProcess();
    });


    $('#new_post>i').click(function () {

        $('#post_dialog').addClass('flow_up');

        $('#new_post').addClass('hide')
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

    var MutationObserver = window.MutationObserver
        , postArea = losses.elements.dialogElement[0]
        , DocumentObserver = new MutationObserver(checkSumitable)
        , DocumentObserverConfig = {
            attributes: true,
            childList: true,
            characterData: true,
            subtree: true
        };
    DocumentObserver.observe(postArea, DocumentObserverConfig);

    $('#post_form').submit(function (event) {
        event.preventDefault();
    });

    $('.post_submit').click(function () {
        if (!losses.elements.submitable)
            return;

        var flying = true;

        losses.elements.submitIcon.blur()
            .addClass('fly');

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
                publicWarning(data.message);
                return;
            }

            if (losses.router.postId) {
                var date = new Date()
                    , time = date.getFullYear() + '-' + date.getMonth() + '-' + date.getDay() + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
                losses.scope.postCtrl.posts.push({
                    author: 'a person', /*二次开发注意需要!*/
                    time: time,
                    content: $('textarea[name="content"]')[0].value
                });
                losses.scope.postCtrl.$digest();
            }

            function finishProcess() {
                losses.elements.submitIcon.removeClass('fly');
                if (!losses.router.postId)
                    magicalLocation('#/post/' + data.message);
                $('body').click();
                removeImage();
                $('#post_form')[0].reset();

            }


            if (!flying) {
                finishProcess();
            } else {
                setTimeout(finishProcess, 500);
            }

        });
    });

    $('body').delegate('#search', 'submit', function (event) {
        event.preventDefault();
        magicalLocation('#/search/' + $('input[name="search"]').val());
    })
        .delegate('.post', 'click', function () {
            var that = $(this);

            if (!losses.logined)
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

        $('.icon-menu,.icon_group').each(function () {
            $(this).toggleClass('up');
        });
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
            $('.icon-menu,.icon_group').each(function () {
                $(this).removeClass('up');
            });
            return;
        }
        $('.g-show').removeClass('g-show');
        $('.' + targetAttr).addClass('g-show');
    });

    $('.close_dialog').click(function () {
        $('body').click();
    });

    $('.icon-menu').mouseenter(function () {
        var that = $(this);

        if (losses.event.menuTimeout)
            clearTimeout(losses.event.menuTimeout);

        that.addClass('hide');
        losses.elements.iconGroupElement.addClass('extend bump')
            .one('mouseleave', function () {
                var menu = $(this);

                function action() {
                    losses.elements.pause = false;
                    menu.removeClass('extend bump');

                    that.removeClass('hide');
                }

                if (losses.elements.pause)
                    losses.event.menuTimeout = setTimeout(action, 2000);
                else
                    action();
            });
    });

    setTimeout(checkSumitable, 1000);
})
;

function callManageDialog() {

    function requireCode() {
        $.post('api/?manage', {'key': ''}, function (data) {
            var response = JSON.parse(data);
            if (response.code === 200)
                losses.key = response.message;
            else {
                losses.key = null;
                publicWarning('无法与服务器正确沟通！');
            }
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
        $.post('api/?manage', {'password': md5(md5(inputElement.val()) + losses.key)}, function (data) {
            var response = JSON.parse(data);

            if (response.code === 200) {
                losses.key = null;
                losses.logined = true;
                hideDialog();
                manageLoginProcess();
                publicWarning('Welcome, my master nyan~~');

                if (losses.router.manage) {
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
        if (losses.router.manage)
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
        if (losses.logined)
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
