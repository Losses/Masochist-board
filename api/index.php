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
    'charest'   =>  'utf8',
    'option'    =>  [PDO::ATTR_CASE =>  PDO::CASE_NATURAL]
  
  ]);

  $columns_sql = [];
  $where_sql   = [];
  $data_sql  = [];

  $current_time = $database->query('SELECT NOW()')->fetchAll()[0][0];

  if (isset($_GET['new'])) {
    
    $post_title   = isset($_POST['title'])    ? $_POST['title']   : '';
    $post_content = isset($_POST['content'])  ? $_POST['content'] : '';
    $post_upid    = isset($_POST['upid'])   ? $_POST['upid']    : 0;
    $post_sage    = isset($_POST['sage'])   ? 1         : 0;
    $post_cate    = isset($_POST['category']) ? $_POST['category']  : 0;

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
                $rename_img = md5(md5_file('../upload/'
                  .$_FILES['image']['name'])
                  .date('Y-m-d H:i:s')).'.'
                  .$type_img[count ($type_img) - 1];
                rename('../upload/' . $_FILES['image']['name'],
                  '../upload/'  . $rename_img);
                
                }
            
          }
    }else {

        $rename_img = null;
        
    }

    $data_sql += [

      'author'    =>  $_POST['author'],
        'title'     =>  $post_title,
        'content'   =>  htmlspecialchars($post_content),
        'time'      =>  $current_time,
      'active_time' =>  $current_time,
      'img'     =>  $rename_img,
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

      'AND' =>  ['upid[=]'  =>  0],
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

elseif (isset($_GET['category'])) {
  $data = $database->select('category', '*');

  echo json_encode($data);
}

elseif (isset ($_GET['post'])) {
  require_once('../libs/parsedown.php');

  $Parsedown = new Parsedown();

  $_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 1;

  $data = $database->select('content', [
    'id',
    'title',
    'content',
    'author',
    'time',
    'img',
	'category'
    ],[
      'OR'        =>  [
        'upid[=]' =>  $_GET['id'],
        'id[=]'   =>  $_GET['id']
        ],
      'ORDER'     =>  ['upid','id'],
      'LIMIT'     =>  [($_GET['page'] - 1) * 10, $_GET['page'] * 10]
  ]);

  if (isset ($data[0]['upid']) && $data[0]['upid'] != 0) {
    response_message(301, $data[0]['upid']);
  }

  $data_length = count($data);

  for ($i = 0; $i < $data_length; $i++) {
    $data[$i]['content'] = $emotion->phrase($Parsedown->text($data[$i]['content']));
  }

  echo json_encode($data);

  exit();
}

elseif (isset($_GET['manage'])) {
  
  //session check
  session_start();

  if (isset($_POST['key'])) {
    $_SESSION['key'] = md5(md5(date('Y-m-d H:i:s')) . UR_SALT);
    response_message(200, $_SESSION['key']);
  }elseif (isset($_POST['password'])) {
    if (!isset($_SESSION['key'])) {
      response_message(403,'You need a key!');
    }else {
      if(md5(md5(UR_PASSWORD) . $_SESSION['key'])  ==  $_POST['password']) {
        $_SESSION['logined'] = true;
        response_message(200, 'Login success!');
      }else {
        $_SESSION['logined'] = false;
        response_message(403, 'Wrong password!');
      }
    }
  }elseif (isset($_POST['check'])) {
    if (isset($_SESSION['logined']) && $_SESSION['logined'] == true) {
      response_message(200, true);
    }else {
      response_message(200, false);
    }
  }
  
  //function
  if (isset($_POST['action']) && ($_POST['action'] == 'delete')) {
    foreach ($_POST['target'] as $post_target) {
      $post_target_id = $database->select('content', [
        'upid'
      ],[
        'id[=]'  =>  $post_target
      ]);
      if (isset($post_target_id) && $post_target_id != 0) {
        $data = $database->delete('content', [
          'upid[=]' =>  $post_target_id
        ]);
      }else {
        $data = $database->delete('content', [
          'AND' =>  [
            'upid'  =>  $post_target_id,
            'id'    =>  $post_target_idk
        ]]);
      }
      if ($data == false) {
        echo $post_target_id;
      }
    }
    if ($data == true) {
      response_message(200,'Delet Success!');
    }else{
      response_message(403,'Delet Failed!');
    }
  }

  if (isset($_POST['action']) && ($_POST['action'] == 'sage')) {
    foreach ($_POST['target'] as $post_target) {
      $data = $database->update('content', [
        'sage'  =>  1
      ],[
        'id[=]'    =>  $post_target
      ]);
      if ($date == false) {
        echo $post_target;
      }
    }
    if ($data  == false) {
      response_message(403, 'Sage Failed!');
    }else {
      response_message(200, 'Sage Success!');
    }
  }

  if (isset($_POST['action']) && ($_POST['action'] == 'trans')) {
    foreach ($_POST['target'] as $post_target) {
      $data = $database->update('content', [
        'category'  =>  $_POST['target_cate']
      ],[
        'id[=]'        =>  $post_target
      ]);
      if ($data == false) {
        echo $post_target;
      }
    }
    if ($data == false) {
      response_message(403, "Die, Autobots!");
    }else {
      response_message(200, "Autobots, Transform and Roll Out.");
    }
  }

  if (isset($_POST['action']) && ($_POST['action'] == 'hummer')) {
    $data = $database->update('content', [
        'hummer' => $_POST['content']
      ]);
    if ($data == false) {
      response_message(403, 'UCCU');
    }else {
      response_message(200, '炸鸡馒头');
    }
  }
}

function response_message($code,$message) {
  $response = [
    'code'    =>  $code,
    'message' =>  $message
  ];

  echo json_encode($response);

  exit();
}