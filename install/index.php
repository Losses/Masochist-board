<?php
require_once('../libs/medoo.php');
if (count($_GET) == 0) {
    header("Location: ?check");
    exit();
}

if (isset($_GET['check_connection'])) {

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
        print_r(json_encode('403'));
        exit();
    }
    print_r(json_encode('200'));
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
        .status.finish li:nth-of-type(1)::after,
        .status.finish li:nth-of-type(2)::after,
        .status.finish li:nth-of-type(3)::after {
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

        #admin_information {
            margin-top: 20px;
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

        .central_content {
            top: 50%;
            width: calc(100% - 60px);
            color: #16092D;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
            position: absolute;
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
                    <p class="c<?= is_writable('../upload/') ?>">./upload</p>

                    <div class="icon_collection clear">
                        <?php if (is_writable('../') && is_writable('../upload/')): ?>
                            <a class="install_button" href="?info">下一步</a>
                        <?php else: ?>
                            <a class="install_button gray" href="?check">刷新</a>
                        <?php endif; ?>

                    </div>

                    <?php break;
                case 'info':
                    ?>

                    <article>
                        接下来请填写必要的数据，我们将会把这些数据存储在 Masochist-board 的根目录下，如需要转移数据库或更换密码，请修改 config.php
                    </article>

                    <form method="post" action="?install">

                        <section id="database_information">
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
                                <button class="install_button" type="submit">下一步</button>
                            </div>
                        </section>

                    </form>
                    <?php break;
                case 'install' :
                    ?>
                    <div class="loading_spin">
                        <div class="background"></div>
                        <svg class="circular">
                            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="5"
                                    stroke-miterlimit="10"/>
                        </svg>
                    </div>
                    <div class="working central_content">请稍候，正在安装Masochist-board</div>

                    <!--安装代码请放在这里-->

                    <?php break;
                case 'finish' :
                    ?>

                    <div class="working central_content">安装完成，为确保安全请删除install文件夹。</div>
                <?php endswitch; ?>

        </div>
        <div class="push"></div>
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
</footer>
</body>
</html>