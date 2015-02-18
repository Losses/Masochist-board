<?php

require_once ('../config.php');

require_once ('../libs/medoo.php');

require_once ('../libs/emotions.php');

$emotion = new emotions();

$database = new medoo([
  'database_type' =>  'mysql',
  'database_name' =>  DB_NAME,
  'server'        =>  DB_HOST,
  'username'      =>  DB_USER,
  'password'      =>  DB_PASSWORD,
  'charset'       =>  'utf8',
  'port'          =>  DB_PORT,
  'option'        =>  [
                        PDO::ATTR_CASE => PDO::CASE_NATURAL
]]);

if (isset ($_GET['new'])) {
  $_POST['upid']      = isset($_POST['upid'])     ?   $_POST['upid']  : 0;
  $_POST['category']  = isset($_POST['category']) &&  ($_POST['upid'] == 0)
    ? $_POST['category']  : 0;
  $_POST['title']     = isset($_POST['title'])    ?   $_POST['title'] : '';

  if (($_POST['upid'] ==  0)  &&  ($_POST['title']  ==  '')) {
    response_message(403, 'You need a title!');
    exit();
  }

  if (count($_FILES) > 0) {
    if ((($_FILES['image']['type'] == 'image/gif')
      || ($_FILES['image']['type'] == 'image/jpeg')
      || ($_FILES['image']['type'] == 'image/pjpeg')
      || ($_FILES['image']['type'] == 'image/png'))
      && ($_FILES['image']['size'] < 50000000)) {
      if ($_FILES['image']['error']) {
        response_message(500, 'Internal Server Error!');
      }else {
        move_uploaded_file($_FILES['image']['tmp_name'],
          '../upload/' . $_FILES['image']['name']);
        $retype = explode('.', '../upload/' . $_FILES['image']['name']);
        $name = md5(md5_file('../upload/' . $_FILES['image']['name']) .
          date('Y-m-d H:i:s')) . '.' . $retype[count($retype) - 1];
        rename('../upload/' . $_FILES['image']['name'], '../upload/' . $name);
      }
    }
  }else {
    $name = null;
  }

  $insert_sql = [
    'author'  =>  $_POST['author'],
    'title'   =>  $_POST['title'],
    'content' =>  htmlspecialchars($_POST['content']),
    'upid'    =>  $_POST['upid'],
    'img'     =>  $name,
  ];

  if ($_POST['upid'] == 0) {
    $insert_sql +=  ['category' =>  $_POST['category']];
  }

  if (isset($_POST['sage'])) {
    $insert_sql +=  ['sage' =>  $_POST['sage']];
  }

  $result = $database->insert('content', $insert_sql);

  if (isset($_POST['upid']) && $_POST['upid'] != 0) {
    $issage = $database->select('content',[
      'sage'
    ],[
      'id[=]' =>  $_POST['upid']
    ])[0]['sage'] === '1';

    if ($issage) {
      $current_time = $database->query('SELECT NOW()')->fetchAll()[0][0];
      $database->update('content',[
        'active_time' =>  $current_time
      ],[
        'id'          =>  $_POST['upid']
      ]);
    }
  }

  response_message(200,$result);
}

elseif (isset ($_GET['list'])) {
  $_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 1;

  if (isset($_GET['search'])) {
    $search_text = explode(" ", $_GET['search']);
    foreach ($search_text as $word) {
      $result_condition   .=  '*' . $word . '* ';
    }
    $search_text = $database->quote($result_condition);
    $data  = $database->query("SELECT * FROM content 
                              WHERE MATCH (title,content)
                              AGAINST ($search_text IN BOOLEAN MODE)")
                            ->fetchAll();
    echo json_encode($data);
    exit();
  }

  $condition_sql = [];

  if (isset($_GET['category'])) {
    $condition_sql['AND']['category[=]'] = (int)$_GET['category'];
  }

  $condition_sql +=
  [
        'AND'     =>    ['upid[=]' =>  0],
        
        'ORDER'   =>    ['active_time DESC','time DESC'],
        
        'LIMIT'   =>    [($_GET['page']-1)*10, $_GET['page']*10]
  ];

  $data = $database->select('content',[
    'id',
    'title',
    'author',
    'time',
    'category',
    'sage'
  ], $condition_sql);

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
    'img'
    ],[
      'OR'        =>  [
        'upid[=]' =>  $_GET['id'],
        'id[=]'   =>  $_GET['id']
        ],
      'ORDER'     =>  ['upid','id'],
      'LIMIT'     =>  [($_GET['page'] - 1) * 10, $_GET['page'] * 10]
  ]);

  if ($data[0]['upid'] != 0) {
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