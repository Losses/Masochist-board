<?php

require_once('../config.php');

require_once('../libs/medoo.php');

$database = new medoo([
	'database_type'	=>		'mysql',
	'database_name'	=>		DB_NAME,
	'server'		=>		DB_HOST,
	'username'		=>		DB_USER,
	'password'		=>		DB_PASSWORD,
	'charset'		=>		'utf8',
	'port'			=>		DB_PORT,

	'option'        =>		[
								PDO::ATTR_CASE => PDO::CASE_NATURAL
							]
]);

if (isset ($_GET['new'])){
	
	$_POST['upid'] = isset($_POST['upid']) ? $_POST['upid'] : 0;
	$_POST['title'] = isset($_POST['title']) ? $_POST['title'] : '';

	$database->insert('content',[
		'author'	=>		$_POST['author'],
		'title'		=>		$_POST['title'],
		'content'	=>		$_POST['content'],
		'upid'		=>		$_POST['upid']
	]);
	
	if (isset($_POST['upid']) && ($_POST['upid'] != 0)){
		$database->update('content',[
			'active_time'		=>		date('Y-m-d H:i:s')
		],[
			'id'				=>		$_POST['upid']
		]);
	}
	
	echo 'finished';
	exit();
}

elseif (isset ($_GET['list'])){

	$_GET['page'] = isset($_GET['page']) ? $_GET['page'] : 1;

	$data = $database->select('content',[
				'id',
				'title',
				'author',
				'time'
			],[
				'ORDER'		=>		['active_time DESC','time DESC'],
				'upid[=]'	=>		0,
				'LIMIT'		=>		[($_GET['page']-1)*10, $_GET['page']*10]
			]);

	echo json_encode($data);
	exit();
}

elseif (isset ($_GET['post'])){
		$data = $database->select('content',[
				'id',
				'title',
				'content',
				'author',
				'time'
			],[
				'OR'		=>		[
										'upid[=]'	=>		$_GET['id'],
										'AND'		=>		[
											'upid[=]'		=>		0,
											'id[=]'			=>		$_GET['id']
										]
									],
				'ORDER'		=>		['upid','id']
			]);

	echo json_encode($data);
	exit();
}