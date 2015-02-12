<?php

	require('config.php');
	
	function sql_query($sqlcon)
	{
		$con=mysql_connect(DB_HOST, DB_USER,DB_PASSWORD);
		mysql_select_db(DB_NAME);
		mysql_query("SET NAMES 'utf8'");
		$result = mysql_query($sqlcon);
		mysql_close($con);
		return $result;
	}

	$gay_sky = sql_query("SELECT * FROM content");
	print_r($gay_sky);
	$gay_losses = sql_query("insert into content ("id", "author")
		values (01, "gay")");
	print_r($gay_losses);

?>