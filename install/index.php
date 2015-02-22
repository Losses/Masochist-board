<?php
session_start();
require_once('../libs/medoo.php');
if (count($_GET) == 0) {
    header("Location: ?check");
    exit();
}

if (isset($_GET['check_connection'])) {
    if (check_connection()) {
        print_r(json_encode('200'));
        exit();
    } else {
        print_r(json_encode('403'));
        exit();
    }
}

$reinstall_mode = is_file('../config.php');

if ($reinstall_mode
    && ((isset($_SESSION['logined']) && ($_SESSION['logined'] != 1)) || !isset($_SESSION['logined']))
    && isset($_GET['install'])
) {
    header("Location: ?info");
    exit();
}

if (isset($_SESSION['info_catched'])
    && ($_SESSION['info_catched'] == true)
    && isset($_GET['ajax_install'])
) {
    install_mb();
    print_r(200);
    exit();
}
?>
    <!DOCTYPE html>
    <html ng-app='mKnowledge'>
    <head lang="en">
        <meta charset="UTF-8">
        <meta name="theme-color" content="#5c00ff">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <link type="text/css" rel="stylesheet" href="../styles/main.css"/>
        <link type="text/css" rel="stylesheet" href="../styles/page.css"/>
        <link type="text/css" rel="stylesheet" href="../styles/board.css"/>
        <script src="../scripts/jquery.js"></script>
        <script src="../scripts/prefixfree.min.js"></script>
        <title>Masochist-board 安装向导</title>

        <style>
            @keyframes shake_submit {
                0% {
                    right: 4px;
                }
                50% {
                    right: 0;
                }
                100% {
                    right: -4px;
                }
            }

            @keyframes rotate {
                100% {
                    transform: rotate(360deg);
                }
            }

            @keyframes dash {
                0% {
                    stroke-dasharray: 1, 200;
                    stroke-dashoffset: 0;
                }
                50% {
                    stroke-dasharray: 89, 200;
                    stroke-dashoffset: -35;
                }
                100% {
                    stroke-dasharray: 89, 200;
                    stroke-dashoffset: -124;
                }
            }

            @keyframes color {
                100%, 0% {
                    stroke: #f44336;
                }
                40% {
                    stroke: #2196f3;
                }
                66% {
                    stroke: #4caf50;
                }
                80%, 90% {
                    stroke: #ffc107;
                }
            }

            @keyframes shine {
                from {
                    background: #FFFFFF;
                }
                to {
                    background: #2c184f;
                }
            }

            html, body {
                height: 100%;
            }

            .warp {
                min-height: 100%;
                height: auto !important;
                height: 100%;
                margin: 0 auto -80px;
            }

            .push {
                height: 80px;
            }

            legend {
                color: #16092D;
                font-size: 14px;
            }

            fieldset {
                border: #a89cbe 1px solid;
                border-radius: 3px;
                padding: 15px;
                background: #f1eef5;
                margin-top: 20px;
            }

            fieldset:nth-of-type(0) {
                margin-top: 0;
            }

            input {
                padding: 10px;
                margin: 5px;
                width: calc(20% - 35px);
                border: 0;
                border-radius: 3px;
                transition: all 300ms;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            }

            input.locked {
                color: gray;
            }

            input:hover {
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
            }

            input:focus {
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
            }

            .loading_spin {
                top: 50%;
                left: 50%;
                width: 100px;
                height: 100px;
                margin: -90px 0 0 -50px;
                position: fixed;
                transform: scale(0.6);
                transition: all 300ms;
                opacity: 0;
                visibility: hidden;
            }

            .loading_spin.down {
                margin-top: -50px;
                opacity: 1;
                visibility: visible;
                transition: margin-top 300ms cubic-bezier(0.45, 1.36, 0.97, 1.17), opacity 300ms ease-in-out;
            }

            .loading_spin .circular {
                height: 100px;
                width: 100px;
                position: relative;
                animation: rotate 2s linear infinite;
            }

            .loading_spin .path {
                stroke-dasharray: 1, 200;
                stroke-dashoffset: 0;
                stroke-linecap: round;
                animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
            }

            .loading_spin .background {
                top: 50%;
                left: 50%;
                width: 90px;
                height: 90px;
                margin: -45px 0 0 -45px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.9);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
                position: fixed;
            }

            .install_button {
                margin: 20px;
                padding: 10px 15px;
                color: white;
                font-size: 14px;
                background: #410cc7;
                border: 0;
                border-radius: 3px;
                display: inline-block;
                float: right;
                transition: opacity 300ms;
                position: relative;
            }

            .install_button.shake {
                animation: shake_submit 60ms 14 alternate;
            }

            .install_button.gray {
                background: gray;
            }

            .install_button:hover {
                opacity: 0.9;
            }

            .install_button:link,
            .install_button:visited {
                color: white;
                text-decoration: none;
            }

            .status {
                margin: 0 30px;
            }

            .status li {
                width: 33%;
                color: #16092D;
                font-size: 14px;
                text-align: center;
                margin-top: 10px;
                padding-top: 12px;
                float: left;
                position: relative;
            }

            .status li::before {
                content: '\feff';
                top: -8px;
                left: 50%;
                height: 14px;
                width: 14px;
                margin-left: -7px;
                border: 1px solid #2c184f;
                border-radius: 50%;
                opacity: 1;
                display: block;
                transition: all 300ms;
                position: absolute;
            }

            .status li::after {
                content: '\feff';
                top: -5px;
                left: 50%;
                height: 8px;
                width: 8px;
                margin-left: -4px;
                border: 1px solid #2c184f;
                border-radius: 50%;
                opacity: 0;
                display: block;
                transition: all 300ms;
                position: absolute;
            }

            .status li:hover::before {
                transform: scale(0.8);
            }

            .status li:hover::after {
                background: #2c184f;
                opacity: 1;
                transform: scale(1.2);
            }

            .status.check li:nth-of-type(1)::after,
            .status.info li:nth-of-type(1)::after,
            .status.info li:nth-of-type(2)::after,
            .status.install li:nth-of-type(1)::after,
            .status.install li:nth-of-type(2)::after,
            .status.finished li:nth-of-type(1)::after,
            .status.finished li:nth-of-type(2)::after,
            .status.finished li:nth-of-type(3)::after {
                background: #2c184f;
                opacity: 1;
            }

            .status.install li:nth-of-type(3)::after {
                opacity: 1;
                animation: shine 300s alternate;
            }

            #install_body {
                margin: 10px 30px;
            }

            #install_body article {
                background: #ded3f2;
                padding: 10px;
                margin: 20px 0;
                border-radius: 3px;
                font-size: 12px;
                color: #16092D;
            }

            #install_body.check p {
                line-height: 1.8em;

                font-size: 14px;
                margin: 0 20px;
            }

            #install_body.check p.c1 {
                color: #4caf50;
            }

            #install_body.check p.c1::before {
                content: '[通过]';
            }

            #install_body.check p.c {
                color: #f44336;
            }

            #install_body.check p.c::before {
                content: '[失败]';
            }

            #install_body.info #admin_information {
                display: none;
            }

            #install_body.install {
                min-height: 250px;
                position: relative;
            }

            #install_body.install .loading_spin {
                top: 50%;
                opacity: 1;
                visibility: visible;
                position: absolute;
            }

            #database_login {
                display: none;
            }

            .push {
                height: 300px;
                overflow: hidden;
                position: relative;
            }

            .install .central_content {
                height: 200px;
                color: #16092D;
                font-size: 14px;
                line-height: 300px;
                text-align: center;
                margin-top: 40px;
            }

            .install .push .loading_spin {
                margin-top: -80px;
            }

            .finished .central_content {
                top: 0;
                line-height: 300px;
                font-size: 14px;
                color: #16092D;
                text-align: center;
            }
        </style>
    </head>
    <body>
    <div class="warp">
        <header class="clear">
            <nav>
                <i class="highlight"></i>
                <li class="nav_item a"><a href="#">首页</a></li>
                <li class="nav_item b"><a href="#">手册</a></li>
                <li class="nav_item c"><a href="#">动态</a></li>
                <li class="nav_item d"><a href="#">关于</a></li>
            </nav>

            <div id="intro">
                <h1>安装向导</h1>

                <p class="subtitle">欢迎使用Masochist-board！</p>
            </div>
        </header>

        <section id="main">

            <nav class="status <?= key($_GET) ?> clear">
                <li>检查环境</li>
                <li>填写信息</li>
                <li>开始安装</li>
            </nav>

            <div id="install_body" class=" <?= key($_GET) ?> ">
                <?php switch (key($_GET)) :
                    case 'check':
                        ?>
                        <article>
                            为了保证 Masochist-board 在今后能正常运行，我们将检查目录的可读性。请将对应的目录权限设为可写入。
                        </article>

                        <p class="c<?= is_writable('../') ?>">./</p>
                        <p class="c<?= is_writable('../backup/') ?>">./backup</p>
                        <p class="c<?= is_writable('../upload/') ?>">./upload</p>

                        <div class="icon_collection clear">
                            <?php if (
                                is_writable('../')
                                && is_writable('../upload/')
                                && is_writable('../backup/')
                            ): ?>
                                <a class="install_button" href="?info">下一步</a>
                            <?php else: ?>
                                <a class="install_button gray" href="?check">刷新</a>
                            <?php endif; ?>

                        </div>

                        <?php break;
                    case 'info':
                        ?>

                        <article>
                            <?php if ($reinstall_mode): ?>
                                检测到您之前安装过 Masochist-board 请确认您需要重新安装，该操作将清空所有已经存在的数据，原来的数据将被保存至 backup 目录 <br>
                            <?php endif; ?>
                            接下来请填写必要的数据，我们将会把这些数据存储在 Masochist-board 的根目录下，如需要转移数据库或更换密码，请修改 config.php
                        </article>

                        <form method="post" action="?install">

                            <section id="database_login"
                                <?php if ($reinstall_mode): ?>
                                    style="display:block;"
                                <?php endif;?> >
                                <fieldset>
                                    <legend>管理人员身份确认</legend>
                                    <input placeholder="管理员密码" data-name="password"/>
                                </fieldset>
                                <div class="icon_collection clear">
                                    <button class="check_admin install_button">检查管理员密码</button>
                                </div>
                            </section>

                            <section id="database_information"
                                <?php if ($reinstall_mode): ?>
                                    style="display:none;"
                                <?php endif;?> >
                                <fieldset>
                                    <legend>数据库信息</legend>
                                    <input placeholder="数据库地址" name="DB_HOST"/>
                                    <input placeholder="数据库端口" name="DB_PORT"/>
                                    <input placeholder="数据库用户名" name="DB_USER"/>
                                    <input placeholder="数据库密码" name="DB_PASSWORD"/>
                                    <input placeholder="数据库名称" name="DB_NAME"/>
                                </fieldset>
                                <div class="icon_collection clear">
                                    <button class="check_form install_button">检查数据库设置</button>
                                </div>
                            </section>

                            <section id="admin_information">
                                <fieldset>
                                    <legend>管理人员信息</legend>
                                    <input placeholder="管理员密码" name="UR_PASSWORD"/>
                                    <input placeholder="盐，随便打点啥" name="UR_SALT"/>
                                </fieldset>

                                <div class="icon_collection clear">
                                    <button class="install_button" type="submit" data-noexp>下一步</button>
                                </div>
                            </section>

                        </form>
                        <?php break;
                    case 'install' :
                        ?>
                        <?php $_SESSION['info_catched'] = true ?>
                        <div class="push">
                            <div class="loading_spin">
                                <div class="background"></div>
                                <svg class="circular">
                                    <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="5"
                                            stroke-miterlimit="10"/>
                                </svg>
                            </div>
                            <div class="working central_content">请稍候，正在安装Masochist-board</div>
                        </div>

                        <?php break;
                    case 'finished' :
                        ?>
                        <div class="push">
                            <div class="working central_content">安装完成，为确保安全请删除install文件夹。</div>
                        </div>
                    <?php endswitch; ?>
            </div>
        </section>

        <section id="common">
            <div class="loading_spin">
                <div class="background"></div>
                <svg class="circular">
                    <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10"/>
                </svg>
            </div>
        </section>
    </div>
    <footer>
        <p>&copy; 2015 somebody</p>

        <p>Designed By <a href="https://plus.google.com/u/0/+LossesDon/posts">Losses Don</a>, Programmed By <a
                href="https://plus.google.com/+SkyLark2333">Sky Lark</a></p>
        <script src="../scripts/main.js"></script>

        <script>
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


            $(document).ready(function () {
                var skipLimit;

                $('button[data-noexp]').click(function () {
                    skipLimit = true;
                });

                $(window).submit(function (event) {
                    if (!skipLimit) {
                        event.preventDefault();
                    } else {
                        skipLimit = false;
                    }
                });

                $('.check_form').click(function (event) {
                    var that = this
                        , response;
                    switchLoading(true);

                    function errorProcess() {
                        $(that).removeClass('shake')
                            .html('重新检查数据库设置');

                        setTimeout(function () {
                            $(that).addClass('shake');
                        }, 300);
                    }

                    $.post('?check_connection', {
                        'DB_HOST': $('input[name="DB_HOST"]').val(),
                        'DB_USER': $('input[name="DB_USER"]').val(),
                        'DB_PASSWORD': $('input[name="DB_PASSWORD"]').val(),
                        'DB_NAME': $('input[name="DB_NAME"]').val(),
                        'DB_PORT': $('input[name="DB_PORT"]').val()
                    }, function (data) {
                        switchLoading(false);
                        try {
                            response = JSON.parse(data);
                        } catch (e) {
                            errorProcess();
                        }

                        if (response != 200)
                            errorProcess();
                        else {
                            $('#database_information input').each(function () {
                                $(this).attr('readonly', 'true')
                                    .addClass('locked');
                            })
                            $(that).slideUp();
                            $('#admin_information').slideDown();
                        }
                    });
                    event.preventDefault();
                });
            });
        </script>

        <?php if ($reinstall_mode && isset($_GET['info'])): ?>
            <script src="../scripts/md5.min.js"></script>
            <script>
                var key;

                function requireCode() {
                    $.post('../api/?manage', {key: ''}, function (data) {
                        var response = JSON.parse(data);
                        key = response.message;
                    });
                }

                requireCode();

                $('.check_admin').click(function () {
                    var inputElement = $('input[data-name="password"]')
                        , that = this;
                    switchLoading(true);

                    $.post('../api/?manage', {'password': md5(md5(inputElement.val()) + key)}, function (data) {
                        switchLoading(false);
                        var response = JSON.parse(data);

                        if (response.code === 200) {
                            key = null;

                            inputElement.addClass('locked')
                                .attr('readonly', '1');

                            $('#database_information').slideDown(300);

                            $(that).slideUp(300);
                        }
                        else {
                            requireCode();
                            $(that).removeClass('shake');

                            setTimeout(function () {
                                $(that).addClass('shake');
                            }, 10);

                            losses.key = null;
                            $(that).html('重新检查管理员密码');
                        }
                    });
                })
            </script>
        <?php endif; ?>

        <?php if (isset($_GET['install']) && isset($s)): ?>
            <script>
                $.post('?ajax_install', <?php echo (json_encode($_POST)) ?>, function (data) {
                    if (data == 200) {
                        location.href = '?finished';
                    } else {
                        alert(data);
                    }
                })
            </script>

        <?php endif; ?>
    </footer>
    </body>
    </html>

<?php


function check_connection()
{
    try {
        $database = new medoo([
            'database_type' => 'mysql',
            'database_name' => $_POST['DB_NAME'],
            'server' => $_POST['DB_HOST'],
            'username' => $_POST['DB_USER'],
            'password' => $_POST['DB_PASSWORD'],
            'charset' => 'utf8',
            'port' => $_POST['DB_PORT'],
        ]);
    } catch (Exception $e) {
        return false;
    }

    return $database;
}

function install_mb()
{
    require_once('../libs/mysql_backup.php');

    $time = time();
    $dir = "../backup/$time";
    if (!is_dir($dir))
        mkdir($dir);

    rename('../config.php', "$dir/config.php");

    $config_content = "
        <?php

        define('DB_HOST', '" . $_POST['DB_HOST'] . "');
        define('DB_USER', '" . $_POST['DB_PASSWORD'] . "');
        define('DB_PASSWORD', '" . $_POST['DB_USER'] . "');
        define('DB_NAME', '" . $_POST['DB_NAME'] . "');
        define('DB_PORT', '" . $_POST['DB_PORT'] . "');
        define('UR_PASSWORD', '" . $_POST['UR_PASSWORD'] . "');
        define('UR_SALT', '" . $_POST['UR_SALT'] . "');

    ";

    file_put_contents('../config.php', $config_content, LOCK_EX);

    if (check_connection()) {
        backup_tables($_POST['DB_HOST'], $_POST['DB_PORT'], $_POST['DB_USER'],
            $_POST['DB_PASSWORD'], $_POST['DB_NAME'], '*', "$dir/database.sql");
    }

    check_connection()->query("
    SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
    SET time_zone = '+00:00';

    DROP TABLE IF EXISTS category,content;

    CREATE TABLE IF NOT EXISTS `category` (
    `id` int(11) NOT NULL,
      `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
      `mute` tinyint(4) NOT NULL DEFAULT '0',
      `hide` tinyint(4) NOT NULL DEFAULT '0',
      `theme` varchar(8) COLLATE utf8_unicode_ci NOT NULL
    ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    INSERT INTO `category` (`id`, `name`, `mute`, `hide`, `theme`) VALUES
    (1, '兄贵', 0, 0, 'red'),
    (2, '搞基', 0, 0, 'orange'),
    (3, '卖萌', 0, 0, 'pink'),
    (4, '百合', 0, 0, 'green'),
    (5, '天空', 0, 0, 'blue'),
    (6, '种子', 1, 1, 'teal');

    CREATE TABLE IF NOT EXISTS `content` (
    `id` int(11) NOT NULL,
      `author` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
      `title` text COLLATE utf8_unicode_ci,
      `content` text COLLATE utf8_unicode_ci NOT NULL,
      `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `active_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `img` tinytext COLLATE utf8_unicode_ci,
      `upid` int(11) NOT NULL,
      `sage` tinyint(4) NOT NULL DEFAULT '0',
      `category` int(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    ALTER TABLE `category`
     ADD PRIMARY KEY (`id`);

    ALTER TABLE `content`
     ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id_UNIQUE` (`id`), ADD FULLTEXT KEY `index_content` (`title`,`content`);

    ALTER TABLE `category`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;

    ALTER TABLE `content`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
                                ")->fetchAll();
}