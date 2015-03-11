<?php
//NOTICE: DO NOT EDIT THE 'MB_VERSION' BASED ON ANY REASON, IT MAY CAUSE THE UPDATER WORK IN AN ABNORMAL WAY!
    define('MB_VERSION', '0.9 PreAlpha');

    require_once('../config.php');

    require_once('../libs/medoo.php');

    require_once('../libs/emotions.php');

    require_once('../libs/plugin.php');

    require_once('../libs/remove_xss.php');

    $emotion = new emotions();

    $plugin = new plugin();

    $database = new medoo
    (
        [
            'database_type' => 'mysql',
            'database_name' => DB_NAME,
            'server'        => DB_HOST,
            'username'      => DB_USER,
            'password'      => DB_PASSWORD,
            'port'          => DB_PORT,
            'charset'       => 'utf8',
            'option'        => [PDO::ATTR_CASE => PDO::CASE_NATURAL]
        ]
    );

    $columns_sql = [];
    $where_sql   = [];
    $data_sql    = [];
    $join_sql    = [];

    $current_time = $database->query('SELECT NOW()')->fetchAll()[0][0];

    $plugin->load_hook("HOOK-BEFORE_START");

    session_set_cookie_params(31536000);
    session_start();

    if (isset($_GET['new']))
    {
        $post_title = isset($_POST['title'])     ? $_POST['title']    : '';
        $post_content = isset($_POST['content']) ? $_POST['content']  : '';
        $post_upid = isset($_POST['upid'])       ? $_POST['upid']     : 0;
        $post_sage = isset($_POST['sage'])       ? 1                  : 0;
        $post_cate = isset($_POST['category'])   ? $_POST['category'] : 0;
        $post_ip = get_ip_address();
        $post_author = (isset($_SESSION['logined'])
            && $_SESSION['logined'] == true)     ? 'Admin'            : 'a person';

        $plugin->load_hook("HOOK-BEFORE_NEW");

        if ($post_upid == 0)
        {
            if ($post_title == '')
            {
                response_message(403, "You need a title!");
                exit();
            }
            if ($post_content == '')
            {
                response_message(403, "You need some contents!");
                exit();
            }

            $data_sql = ['active_time' => $current_time,];
        }
        else
        {
            if ($post_content == '')
            {
                response_message(403, "You need some contents!");
                exit();
            }

            $columns_sql = ['sage'];
            $where_sql = ['id[=]' => $post_upid];
            $issage = $database->select('content',
                    $columns_sql, $where_sql)[0]['sage'] === '1';
            if (!$issage)
            {
                $data_sql = ['active_time' => $current_time,];
                $data = $database->update('content', $data_sql, $where_sql);
            }
        }

        if (count($_FILES) > 0)
        {
            $type_img = ['image/gif', 'image/jpeg', 'image/svg+xml',
                'image/bmp', 'image/wbmp', 'image/png'];

            if ((in_array($_FILES['image']['type'], $type_img))
                && ($_FILES['image']['size'] < 50000000))
            {
                if ($_FILES['image']['error'])
                {
                    response_message(500, 'Internal Server Error!');
                }
                else
                {
                    move_uploaded_file($_FILES['image']['tmp_name'],
                        '../upload/' . $_FILES['image']['name']);

                    $type_img = explode('.', '../upload/'
                        . $_FILES['image']['name']);
                    $post_img = md5(md5_file('../upload/'
                        . $_FILES['image']['name'])
                        . date('Y-m-d H:i:s')) . '.'
                        . $type_img[count($type_img) - 1];
                    rename('../upload/' . $_FILES['image']['name'],
                        '../upload/' . $post_img);
                }
            }
        }
        else
        {
            $post_img = NULL;
        }

        $columns_sql = ['mute'];
        $where_sql = ['id[=]' => $post_cate];
        $is_mute = $database->select('category',
            $columns_sql, $where_sql)[0]['mute'] === '1';
        if ($is_mute && (!isset($_SESSION['logined'])
            || $_SESSION['logined'] == false))
        {
            response_message(403, "You can't post at mute category!");
            exit();
        }

        $data_sql +=
        [
            'author' => $post_author,
            'title' => $post_title,
            'content' => str_replace(array("\r\n", "\r", "\n"),
                "\n\n", htmlspecialchars($post_content)),
            'time' => $current_time,
            'active_time' => $current_time,
            'img' => $post_img,
            'upid' => $post_upid,
            'sage' => $post_sage,
            'category' => $post_cate,
            'ip' => $post_ip
        ];

        $result = $database->insert('content', $data_sql);

        response_message(200, $result);
    }
    elseif (isset($_GET['list']))
    {
        $plugin->load_hook("HOOK-BEFORE_LIST");

        $_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 1;

        $join_sql =
        [
            '[><]category'  =>
            [
                'category'  =>  'id'
            ]
        ];

        $columns_sql =
        [
            'content.id',
            'content.title',
            'content.author',
            'content.time',
            'content.category',
            'content.sage',
            'content.img'
        ];
        $where_sql =
        [
            'AND'   =>  ['content.upid[=]' => 0],
            'ORDER' =>  ['content.active_time DESC', 'content.time DESC'],
            'LIMIT' =>  [($_GET['page'] - 1) * 10, 10],
        ];
        if (!isset($_SESSION['logined']) || $_SESSION['logined'] == false)
        {
            $where_sql['AND']['category.hide[=]'] = 0;
        }
        if (isset($_GET['category']))
        {
            $where_sql['AND']['content.category[=]'] = (int)$_GET['category'];
        }
        $data = $database->select('content', $join_sql, $columns_sql, $where_sql);

        $plugin->load_hook("HOOK-AFTER_LIST");

        echo json_encode($data);
        exit();
    }
    elseif (isset($_GET['search']))
    {
        require_once('../libs/parsedown.php');

        $Parsedown = new Parsedown();
        
        $search_key = explode(' ', $_GET['search']);
        $result_key = '';
        $page_start = isset($_GET['page']) ? (((int)$_GET['page'] - 1) * 10) : 0;

        foreach ($search_key as $key)
        {
            $result_key .= '*' . $key . '* ';
        }
        $search_key = $database->quote($result_key);
        
        $data = $database->query("  
                                    SELECT * FROM content
                                    WHERE MATCH (title, content)
                                    AGAINST ($search_key IN BOOLEAN MODE)
                                    LIMIT $page_start, 10
                                ")->fetchAll();

        if (!isset($_SESSION['logined']) || $_SESSION['logined'] == false)
        {
            for ($i = 0; $i < count($data); $i++)
            {
                $columns_sql = ['hide'];
                $where_sql = ['id[=]' => $data[$i]['category']];
                $ishide = $database->select('category',
                    $columns_sql, $where_sql)[0]['hide'] == '1';
                
                if ($ishide)
                {
                    $data[$i] = null;
                }
            }
            if ($data[0] == null)
            {
                $data = null;
            }
        }
        
        $search_result = [];

        foreach ($data as $result)
        {
            if ($result['upid'] == 0)
            {
                $search_result[$result['id']]['post'] = $result;
            }
            else
            {
                $search_result[$result['upid']]['reply'][] = $result;
            }
        }

        foreach ($search_result as $result)
        {
            if (isset($result['reply']) && !isset($result['post']))
            {
                $search_result[$result['reply'][0]['upid']] =
                    ['post' =>  [], 'reply' =>  []];
                $where_sql =
                [
                    'id[=]' =>  $result['reply'][0]['upid']
                ];
                $search_result[$result['reply'][0]['upid']]['post'] =
                    $database->select('content', '*', $where_sql)[0];

                for ($i=0; $i <= count($result['reply']) - 1; $i++)
                {
                    $where_sql =
                    [
                        'id[=]' =>  $result['reply'][$i]['id']
                    ];
                    $search_result[$result['reply'][$i]['upid']]['reply'][] =
                        $database->select('content', '*', $where_sql)[0];
                }
            }
        }
        
        foreach ($search_result as $value)
        {
            $search_result[$value['post']['id']]['post']['content'] =
                $emotion->phrase(RemoveXSS($Parsedown->text(
                    $search_result[$value['post']['id']]['post']['content'])));
                    
            if ($search_result[$value['post']['id']]['post']['img'] 
                && ($search_result[$value['post']['id']]['post']['img'] != ''))
            {
                $search_result[$value['post']['id']]['post']['img'] =
                    'upload/' . $search_result[$value['post']['id']]['post']['img'];
            }
        }
        
        
        
        echo json_encode(array_values($search_result));
        exit();
    }
    elseif (isset($_GET['category']))
    {
        if (isset($_SESSION['logined']) && $_SESSION['logined'] == true)
        {
            $data = $database->select('category', '*');
        }
        else
        {
            $where_sql = ['hide[=]' => 0];
            $data = $database->select('category', '*', $where_sql);
        }
        echo json_encode($data);
    }
    elseif (isset($_GET['post']))
    {
        $plugin->load_hook("HOOK-BEFORE_POST");

        require_once('../libs/parsedown.php');

        $Parsedown = new Parsedown();

        if (!isset($_GET['page']))
            $_GET['page'] = 1;

        $columns_sql =
        [
            'id',
            'title',
            'content',
            'author',
            'time',
            'img',
            'sage',
            'category',
            'upid'
        ];
        $where_sql =
        [
            'OR'    =>
            [
                'upid[=]'   =>  $_GET['id'],
                'id[=]'     =>  $_GET['id']
            ],
            'ORDER' =>  ['upid', 'id'],
            'LIMIT' =>  [($_GET['page'] - 1) * 10, 10]
        ];
        $data = $database->select('content', $columns_sql, $where_sql);

        $data_length = count($data);
        for ($i = 0; $i < $data_length; $i++)
        {
            $data[$i]['content'] =
                $emotion->phrase(RemoveXSS($Parsedown->text($data[$i]['content'])));

            if (isset($data[$i]['img']) && ($data[$i]['img'] != ''))
            {
                $data[$i]['img'] = 'upload/' . $data[$i]['img'];
            }
        }

        $plugin->load_hook("HOOK-AFTER_POST");

        echo json_encode($data);
        exit();
    }
    elseif (isset($_GET['manage']))
    {
        if (isset($_POST['key']))
        {
            $_SESSION['key'] = md5(md5(date('Y-m-d H:i:s')) . UR_SALT);
            response_message(200, $_SESSION['key']);
        }
        elseif (isset($_POST['password']))
        {
            $plugin->load_hook("HOOK-BEFORE_LOGIN");
            if (!isset($_SESSION['key']))
            {
                response_message(403, 'You need a key!');
            }
            else
            {
                if (md5(md5(UR_PASSWORD) . $_SESSION['key']) == $_POST['password'])
                {
                    $_SESSION['logined'] = true;
                    response_message(200, '兽人永不为奴!');
                }
                else
                {
                    $_SESSION['logined'] = false;
                    response_message(403, 'UCCU输错密码的样子，真是丑陋!');
                }
            }
        }
        elseif (isset($_POST['check']))
        {
            if (isset($_SESSION['logined']) && ($_SESSION['logined'] == true))
            {
                response_message(200, true);
            }
            else
            {
                response_message(403, false);
            }
        }

        if (isset($_SESSION['logined']) && $_SESSION['logined'] == true)
        {
            if (isset($_POST['action']) && ($_POST['action'] == 'delete'))
            {
                $data_false = [];

                foreach ($_POST['target'] as $post_target_id)
                {
                    $data_sql =
                    [
                        'OR' =>
                        [
                            'id[=]' => $post_target_id,
                            'upid[=]' => $post_target_id
                        ]
                    ];
                    $data = $database->delete('content', $data_sql);

                    if ($data == false)
                    {
                        $data_false += [$post_target_id];
                    }
                }

                if (count($data_false) == 0)
                {
                    response_message(200, 'rm -rf /');
                }
                else
                {
                    response_message(403, 'Delete failed OAQ '
                        . implode(',', $data_false));
                }
            }

            if (isset($_POST['action']) && ($_POST['action'] == 'sage'))
            {
                $data_false = [];

                foreach ($_POST['target'] as $post_target_id)
                {
                    $columns_sql = ['sage'];
                    $where_sql = ['id[=]' => $post_target_id];
                    $issage = $database->select('content',
                        $columns_sql, $where_sql)[0]['sage'] === '1';
                    if ($issage)
                    {
                        $data_sql = ['sage' => 0];
                    }
                    else
                    {
                        $data_sql = ['sage' => 1];
                    }
                    $where_sql = ['id[=]' => $post_target_id];
                    $data = $database->update('content', $data_sql, $where_sql);

                    if ($data == false)
                    {
                        array_push($data_false, $post_target_id);
                    }
                }
                if (count($data_false) == 0)
                {
                    response_message(200, '基本法!');
                }
                else
                {
                    response_message(403, 'Sage or unsage failed OAQ '
                        . implode(',', $data_false));
                }
            }

            if (isset($_POST['action']) && ($_POST['action'] == 'trans'))
            {
                $data_false = [];

                foreach ($_POST['target'] as $post_target_id)
                {
                    $data_sql = ['category' => $_POST['category']];
                    $where_sql = ['id[=]' => $post_target_id];
                    $data = $database->update('content', $data_sql, $where_sql);

                    if ($data == false)
                    {
                        array_push($data_false, $post_target_id);
                    }
                }

                if (count($data_false) == 0)
                {
                    response_message(200, 'Autobots, Transform and Roll Out!');
                }
                else
                {
                    response_message(403, 'Trans failed OAQ '
                        . implode(' ', $data_false));
                }
            }

            if (isset($_POST['action']) && ($_POST['action'] == 'hummer'))
            {
                $data_sql = ['hummer' => $_POST['hummer']];
                $where_sql = ['id[=]' => $_POST['target']];
                $data = $database->update('content', $data_sql, $where_sql);

                if ($data == false)
                {
                    response_message(403, 'Hummer failed OAQ ');
                }
                else
                {
                    response_message(200, '炸鸡馒头');
                }
            }

            if (isset($_POST['action']) && ($_POST['action'] == 'hide_cate'))
            {
                $columns_sql = ['hide'];
                $where_sql = ['id[=]' => $_POST['target']];
                $ishide = $database->select('category',
                    $columns_sql, $where_sql)[0]['hide'] == '1';
                if ($ishide == 1)
                {
                    $data_sql = ['hide' => 0];
                }
                else
                {
                    $data_sql = ['hide' => 1];
                }
                $where_sql = ['id[=]' => $_POST['target']];
                $data = $database->update('category', $data_sql, $where_sql);

                if ($data != false)
                {
                    response_message(200, 'Hide success!');
                }
                else
                {
                    response_message(403, 'Hide failed OAQ ');
                }
            }

            if (isset($_POST['action']) && ($_POST['action'] == 'mute_cate'))
            {
                $columns_sql = ['mute'];
                $where_sql = ['id[=]' => $_POST['target']];
                $ismute = $database->select('category',
                    $columns_sql, $where_sql)[0]['mute'] == '1';
                if ($ismute)
                {
                    $data_sql = ['mute' => 0];
                }
                else
                {
                    $data_sql = ['mute' => 1];
                }
                $where_sql = ['id[=]' => $_POST['target']];
                $data = $database->update('category', $data_sql, $where_sql);

                if ($data != false)
                {
                    response_message(200, 'Mute success!');
                }
                else
                {
                    response_message(403, 'Mute failed OAQ ');
                }
            }

            if (isset($_POST['action']) && ($_POST['action'] == 'rename_cate'))
            {

                $data = $database->select('category', 'name');
                foreach ($data as $result)
                {
                    if ($_POST['name'] == $result)
                    {
                        response_message(403, '当前名称板块已存在');
                        exit();
                    }
                }
                $data_sql = ['name' => $_POST['name']];
                $where_sql = ['id[=]' => $_POST['target']];
                $data = $database->update('category', $data_sql, $where_sql);


                if ($data == true)
                {
                    response_message(200, 'Rename success!');
                }
                else
                {
                    response_message(403, 'Rename failed OAQ ');
                }
            }

            if (isset($_POST['action']) && ($_POST['action'] == 'logout'))
            {

                if ($_SESSION['logined'])
                {
                    $_SESSION['logined'] = false;
                    response_message(200, 'logout successfully');
                }
                else
                {
                    response_message(403, 'you have not logined');
                }
            }

            if (isset($_POST['action']) && ($_POST['action'] == 'add_cate'))
            {
                $data = $database->select('category', 'name');
                foreach ($data as $result)
                {
                    if ($_POST['name'] == $result)
                    {
                        response_message(403, '当前名称板块已存在');
                        exit();
                    }
                }
                $data_sql =
                [
                    'name' => $_POST['name'],
                    'theme' => $_POST['theme']
                ];
                $data = $database->insert('category', $data_sql);

                if ($data == true)
                {
                    response_message(200, 'Add category success!');
                }
                else
                {
                    response_message(403, 'Add category failed OAQ ');
                }
            }

            if (isset($_POST['system_info']))
            {
                if (!$_SESSION['logined'])
                    response_message(403,
                        'You do not have the permission to access these information.');

                $system_info =
                    [
                        'Current PHP version' => phpversion(),
                        'Current MySQL version' => $database->info()['version'],
                        'Masochist-board version' => MB_VERSION
                    ];

                masochist_debug($system_info);

                echo json_encode($system_info);
                exit();
            }
        }
    }
    elseif (isset($_GET['plugin']))
    {
        if (isset ($_POST['plugin_page']))
        {
            $plugin->load_public_page($_POST['plugin_page']);
        }
        else if (isset($_POST['api']))
        {
            $plugin->load_api($_POST['api']);
        }
    }

    function response_message($Code, $Message)
    {
        $Response =
            [
                'code' => $Code,
                'message' => $Message
            ];

        echo json_encode($Response);
        exit();
    }

    function get_ip_address()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
        {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        }
        else if (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        }
        else if (getenv('HTTP_X_FORWARDED'))
        {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        }
        else if (getenv('HTTP_FORWARDED_FOR'))
        {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        }
        else if (getenv('HTTP_FORWARDED'))
        {
            $ipaddress = getenv('HTTP_FORWARDED');
        }
        else if (getenv('REMOTE_ADDR'))
        {
            $ipaddress = getenv('REMOTE_ADDR');
        }
        else
        {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    function masochist_debug($info)
    {
        $date = date('Y-m-d H:i:s');

        if(is_array($info))
            $info = json_encode($info);

        $write_str = "[$date] $info \n";
        file_put_contents('../dbs/debug_info', $write_str, FILE_APPEND);
    }
