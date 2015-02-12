<?php
	
	function sql_query($sqlcon)
	{
		$con=mysql_connect(DB_HOST, DB_USER,DB_PASSWORD);
		mysql_select_db(DB_NAME);
		mysql_query("SET NAMES 'utf8'");
		$result = mysql_query($sqlcon);
		mysql_close($con);
		return $result;
	}

	sql_query("SELECT * FROM guestbook");
?>