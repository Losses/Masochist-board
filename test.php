<?php
	$file_emoji = file_get_contents('dbs/emotions.json');
	$arr_emoji = json_decode($file_emoji, true);
	foreach($arr_emoji as $emoji){
		if(is_array($emoji)){
			foreach($emoji as $detial){
				echo $detial . '<br>';
			}
		}else{
			echo $emjio . '<br>';
		}
	};
	
	fcloes(fopen('dbs/emotions.json', 'r'));
