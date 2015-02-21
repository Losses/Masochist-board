<?php

  require_once ('../config.php');

  require_once ('../libs/medoo.php');

  require_once ('../libs/emotions.php');

  $emotion  = new emotions();

  $database = new medoo([

    'database_type' =>  'mysql',
    'database_name' =>  DB_NAME,
    'server'    =>  DB_HOST,
    'username'    =>  DB_USER,
    'password'    =>  DB_PASSWORD,
    'port'      =>  DB_PORT,
    'charset'   =>  'utf8',
    'option'    =>  [PDO::ATTR_CASE =>  PDO::CASE_NATURAL]
  
  ]);

  $columns_sql = [];
  $where_sql   = [];
  $data_sql  = [];

  $current_time = $database->query('SELECT NOW()')->fetchAll()[0][0];

  if (isset($_GET['new'])) {
    
    $post_title   = isset($_POST['title'])        ? $_POST['title']   : '';
    $post_content = isset($_POST['content'])      ? $_POST['content'] : '';
    $post_upid    = isset($_POST['upid'])       ? $_POST['upid']    : 0;
    $post_sage    = isset($_POST['sage'])       ? 1         : 0;
    $post_cate    = isset($_POST['category'])     ? $_POST['category']  : 0;
    $post_author  = isset($_SESSIOM['logined'] == true) ? '猴子'  : $_POST['author'];

    if ($post_upid == 0) {

      if ($post_title == '') {

        response_message(403, "You need a title!");
        exit();
      
      }
      if ($post_content == '') {
      
        response_message(403, "You need some contents!");
        exit();
      
      }

      $data_sql = ['active_time'  =>  $current_time,];

    }else {

      if ($post_content == '') {
      
        response_message(403, "You need some contents!");
        exit();
      
      }

      $columns_sql = ['sage'];
      $where_sql   = ['id[=]' =>  $post_upid];
      $issage = $database->select('content',
        $columns_sql, $where_sql)[0]['sage'] === '1';
      if (!$issage) {

        $data_sql = ['active_time'  =>  $current_time,];
        $data = $database->update('content', $data_sql, $where_sql);
      
      }

    }

    if ($_FILES > 0) {

        if ((($_FILES['image']['type'] == 'image/gif')
        || ($_FILES['image']['type'] == 'image/jpeg')
          || ($_FILES['image']['type'] == 'image/svg')
          || ($_FILES['image']['type'] == 'image/bmp')
          || ($_FILES['image']['type'] == 'image/wbmp')
          || ($_FILES['image']['type'] == 'image/png'))
          && ($_FILES['image']['size'] < 50000000)) {

              if ($_FILES['image']['error']) {

                response_message(500, 'Internal Server Error!');
              
              }else {

                move_uploaded_file($_FILES['image']['tmp_name'],
                  '../upload/' . $_FILES['image']['name']);
                
                $type_img   = explode('.', '../upload/'
                  .$_FILES['image']['name']);
                $post_img = md5(md5_file('../upload/'
                  .$_FILES['image']['name'])
                  .date('Y-m-d H:i:s')).'.'
                  .$type_img[count ($type_img) - 1];
                rename('../upload/' . $_FILES['image']['name'],
                     '../upload/' . $post_img);
                
                }
            
          }
    }else {

        $post_img = NULL;
        
    }

    $data_sql += [

      'author'    =>  $post_author,
        'title'     =>  $post_title,
        'content'   =>  htmlspecialchars($post_content),
        'time'      =>  $current_time,
      'active_time' =>  $current_time,
      'img'     =>  $post_img,
        'upid'      =>  $post_upid,
        'sage'      =>  $post_sage,
        'category'    =>  $post_cate
    ];

    $result = $database->insert('content', $data_sql);

    response_message(200, $result);

  }

  elseif (isset($_GET['list'])) {
    
    $_GET['page'] = isset($_GET['page'])  ? $_GET['page'] : 1;

    if (isset($_GET['search'])) {
      
      $search_key = explode(' ', $_GET['search']);
      foreach ($search_key as $key) {
        
        $result_key .= '*' . $key . '* ';

      }
      $search_key = $database->quote($result_key);
      $data = $database->query('SELECT * FROM content
                   WHERE MATCH (title, content)
                   AGAINST ($search_key IN BOOLEAN MODE)')
                   ->fetchAll();
      echo json_encode($data);
      exit();

    }

    $columns_sql = [

      'id',
      'title',
      'author',
      'time',
      'category',
      'sage'

    ];
    $where_sql = [

      'AND'   =>  ['upid[=]'  =>  0],
      'ORDER' =>  ['active_time DESC', 'time DESC'],
      'LIMIT' =>  [($_GET['page'] - 1) * 10, $_GET['page'] * 10],

    ];
    if (isset($_GET['category'])) {
      
      $where_sql += [

        'AND'  =>  ['category[=]'  =>  $_GET['category']]

      ];

    }
    $data = $database->select('content', $columns_sql, $where_sql);

    echo json_encode($data);

    exit();

  }

  elseif (isset($_GET('category'))) {
    
    $data = $database->select('category', '*');

    echo json_encode($data);

  }

  elseif (isset($_GET['post'])) {
    
    require_once ('../libs/parsedown.php');

    $Parsedown = new Parsedown();

    $columns_sql = [

      'id',
      'title',
      'content',
      'author',
      'time',
      'img',
      'category'

    ];
    $where_sql = [

      'OR'  =>  [

        'upid[=]' =>$_GET['id'],
        'id[=]'   =>$_GET['id']

      ],
      'ORDER' =>  ['upid','id'],
      'LIMIT' =>  [($_GET['page'] -1) * 10, $_GET['page'] * 10]

    ];
    $data = $database->select('content', $columns_sql, $where_sql);

    if (isset($data[0]['upid']) && $data[0]['upid'] != 0) {
      
      response_message(301, $data[0]['upid']);

    }

    $data_length = count($data);
    for ($i=0; $i < $data_length; $i++) { 
      
      $data[$i]['content'] =
        $emotion->phrase($Parsedown->text($data[$i]['content']));

    }

    echo json_encode($data);

    exit();

  }

  elseif (isset($_GET['manage'])) {
    
    session_start();

    if (isset($_POST['key'])) {
      
      $_SESSIOM['key'] = md5(md5(date('Y-m-d H:i:s')) . UR_SALT);

      response_message(200, $_SESSIOM['key']);

    }elseif (isset($_POST['password'])) { 

      if (!isset($_SESSIOM['key'])) {
        
        response_message(403, 'You need a key!');

      }else {

        if (md5(md5(UR_PASSWORD) . $_SESSIOM['key']) == $_POST['password']) {
          
          $_SESSIOM['logined'] = true;

          response_message(200, '兽人永不为奴!');

        }else {

          $_SESSIOM['logined'] = false;

          response_message(403, 'UCCU输错密码的样子，真是丑陋!');

        }

      }

    }elseif (isset($_POST['check'])) {
      
      if (isset($_SESSIOM['logined']) && ($_SESSIOM['logined'] == true)) {
        
        response_message(200, true);

      }else {

        response_message(403, false);
      
      }

    }

    if (isset($_POST['action']) && ($_POST['action'] == 'delete')) {

      $data_false = [];

      foreach ($_POST['target'] as $post_target) {

        $columns_sql = ['upid'];
        $where_sql   = ['id[=]' =>  $post_target];
        $post_target_id = $database->select('content', $columns_sql, $where_sql);

        if (isset($post_target_id) && ($post_target_id != 0)) {

            $data_sql = ['upid[=]'  =>  $post_target_id];
            $data = $database->delete('content', $data_sql);

        }else {

            $data_sql = ['AND'  =>  [

              'upid'  =>  $post_target_id,
              'id'  =>  $post_target_id

            ]];
          $data = $database->delete('content', $data_sql);

        }

        if ($data == false) {

            $data_false += $post_target_id;

        }
      }

      if (count($data_false) == 0) {
        
        response_message(200, 'rm -rf /');

      }else {

        response_message(403, 'Delete failed OAQ ' . implode(' ', $data_false));

      }

    }

    if (isset($_POST['action']) && ($_POST['action'] == 'sage')) {

      $data_false = [];

      foreach ($_POST['target'] as $post_target_id) {
        
        $data_sql  = ['sage'  =>  1];
        $where_sql = ['id[=]' =>  $post_target_id];
        $data = $database->update('content', $data_sql, $where_sql);

        if ($date == false) {
            
            $data_false += $post_target_id;
        
        }
    
      }

      if (count($data_false) == 0) {
        
        response_message(200, '基本法!');

      }else {

        response_message(403, 'Sage failed OAQ ' . implode(' ', $data_false));

      }
    
    }

    if (isset($_POST['action']) && ($_POST['action'] == 'trans')) {

      $data_false = [];

      foreach ($_POST['target'] as $post_target_id) {
      
        $data_sql  = ['category'  =>  $post_target_id];
        $where_sql = ['id[=]'   =>  $post_target_id]
        $data = $database->update('content', $data_sql, $where_sql);

        if ($date == false) {
            
            $data_false += $post_target_id;
        
        }
      }

      if (count($data_false) == 0) {
        
        response_message(200, 'Autobots, Transform and Roll Out!');

      }else {

        response_message(403, 'Trans failed OAQ ' . implode(' ', $data_false));

      }
    }

    if (isset($_POST['action']) && ($_POST['action'] == 'hummer')) {

      $data_sql  = ['hummer'  =>  $_POST['hummer']];
      $where_sql = ['id[=]' =>  $_POST['target']];
      $data = $database->update('content', $data_sql, $where_sql);

      if ($data == false) {
        
        response_message(403, 'Hummer failed OAQ ');
      
      }else {

        response_message(200, '炸鸡馒头');
      }
    
    }


  }

  function response_message($Code,$Message) {

    $Response = [

      'code'    =>  $Code,
      'message' =>  $Message
    
    ];

    echo json_encode($Response);

    exit();
  
  }