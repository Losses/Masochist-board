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

	$data = $database->select('content',[
				'id',
				'title',
				'author',
				'time'
			],[
				'ORDER'		=>		['active_time DESC','time DESC'],
				'upid[=]'	=>		0
			]);

	echo json_encode($data);
	exit();
}