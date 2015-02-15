<?php
	class emotions{
	  private $emotions;

	  function __construct(){
	    $file_emoji = file_get_contents('dbs/emotions.json');
	    $emotions = json_decode($file_emoji, true);
	  }

	  function phrase(text){

	  }
	}