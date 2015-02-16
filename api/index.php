<?php

require_once('../config.php');

require_once('../libs/medoo.php');

require_once('../libs/emotions.php');

$emotion = new emotions();

$database = new medoo([
  'database_type'  =>    'mysql',
  'database_name'  =>    DB_NAME,
  'server'         =>    DB_HOST,
  'username'       =>    DB_USER,
  'password'       =>    DB_PASSWORD,
  'charset'        =>    'utf8',
  'port'           =>    DB_PORT,

  'option'         =>    [
                            PDO::ATTR_CASE => PDO::CASE_NATURAL
                         ]
]);

if (isset ($_GET['new'])){
  $_POST['upid']  = isset($_POST['upid'])  ? $_POST['upid']  : 0;
  $_POST['title'] = isset($_POST['title']) ? $_POST['title'] : '';

  if(count($_FILES) > 0)
  {
    if ((($_FILES['image']['type'] == 'image/gif')
      || ($_FILES['image']['type'] == 'image/jpeg')
      || ($_FILES['image']['type'] == 'image/pjpeg')
      || ($_FILES['image']['type'] == 'image/png'))
      && ($_FILES['image']['size'] < 50000000))
  	{
  		if ($_FILES['image']['error'])
  		{
  			echo 'Error: ' . $_FILES['image']['error'] . '<br>';
  	  }
      else
      {
      	move_uploaded_file($_FILES['image']['tmp_name'], '../upload/' . $_FILES['image']['name']);

      	$retype = explode('.', '../upload/' . $_FILES['image']['name']);

        $name = md5(md5_file('../upload/' . $_FILES['image']['name']) . date('Y-m-d H:i:s')) . '.' . $retype[count($retype) - 1];
        rename('../upload/' . $_FILES['image']['name'], '../upload/' . $name);
  	  }
  	}
  } else {
	$name = null;
  }

  if(($_POST['upid'] == 0) && ($_POST['title'] == '')){
    print_r('false');
    exit();
  }

  $result = $database->insert('content',[
    'author'   =>    $_POST['author'],
    'title'    =>    $_POST['title'],
    'content'  =>    htmlspecialchars($_POST['content']),
    'upid'     =>    $_POST['upid'],
    'img'      =>    $name,
  ]);

  if (isset($_POST['upid']) && ($_POST['upid'] != 0)){
    $database->update('content',[
      'active_time'    =>    date('Y-m-d H:i:s')
    ],[
      'id'             =>    $_POST['upid']
    ]);
  }

  print_r($result);
  exit();
}

elseif (isset ($_GET['list'])){

  $_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 1;

  $condition_cate =
  [
        'ORDER'    =>    ['active_time DESC','time DESC'],
        'upid[=]'  =>    0,
        'LIMIT'    =>    [($_GET['page']-1)*10, $_GET['page']*10]
  ];
  $where_cate =
  [
    'id',
    'title',
    'author',
    'time',
  ];
  if (isset($_GET['category']))
  {
    $where_cate['category'];
    $condition_cate['category[=]'] = _GET['category'];
  }

  $data = $database->select('content', $where_cate, $condition_cate);

  echo json_encode($data);
  exit();
}

elseif (isset($_GET['category'])){

  $data = $database->select('category', '*');

  echo json_encode($data);
}

elseif (isset ($_GET['post'])){

  require_once('../libs/parsedown.php');

  $Parsedown = new Parsedown();

  $_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 1;

  $data = $database->select('content',[
      'id',
      'title',
      'content',
      'author',
      'time',
      'img'
    ],[
      'OR'       =>    [
                     'upid[=]'      =>    $_GET['id'],
                     'AND'          =>    [
                                               'upid[=]'    =>    0,
                                               'id[=]'      =>    $_GET['id']
                                          ]
                ],
      'ORDER'    =>    ['upid','id'],
      'LIMIT'    =>    [($_GET['page']-1)*10, $_GET['page']*10]
    ]);

  $data_length = count($data);
  for ($i = 0; $i < $data_length; $i++){
    $data[$i]['content'] = $emotion->phrase($Parsedown->text($data[$i]['content']));
  }

  echo json_encode($data);
  exit();
}

/*
//search功能
$searchs = _POST('search')
function search($searchs)
{
  $returndata = $database->select('content',
    [

    ])
}
*/

//response_message
function response_message($code)
{
  switch ($code) {
    case '200':
      echo '200 OK';
      break;
    case '201':
      echo '201 Created';
      break;
    case '202':
      echo '202 Accepted';
      break;
    case '203':
      echo '203 Non-Authoritative Information';
      break;
    case '204':
      echo '204 No Content';
      break;
    case '205':
      echo '205 Reset Content';
      break;
    case '206':
      echo '206 Partial Content';
      break;
    case '207':
      echo '207 Multi-Status';
      break;
    case '300':
      echo '300 Multiple Choices';
      break;
    case '301':
      echo '301 Moved Permanently';
      break;
    case '302':
      echo '302 Found';
      break;
    case '303':
      echo '303 See Other';
      break;
    case '304':
      echo '304 Not Modified';
      break;
    case '305':
      echo '305 Use Proxy';
      break;
    case '306':
      echo '306 Switch Proxy';
      break;
    case '307':
      echo '307 Temporary Redirect';
      break;
    default:
      break;
  }
}