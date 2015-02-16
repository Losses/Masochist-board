<?php
	class emotions
	{
		private $emotions;
		
		function __construct()
		{
			$file_emoji = file_get_contents('../dbs/emotions.json');

			$this->emotions = [];

			$emotion_group = json_decode($file_emoji, true);

			foreach($emotion_group as $group){
				foreach ($group as $key=>$value){
					$this->emotions[$value] = $key;
				}
			}
		}
		
		public function phrase($text)
		{
			preg_match_all('/(:.*?:)/i',$text,$results);
			$process = $text;		
			foreach($results[0] as $result){
				$name = explode(":",$result)[1];
				if (isset($this->emotions[$name])){
					$process = str_replace($result , '<i class="emoji sprite-' . $this->emotions[$name] . '"></i>',$process);
				}
			}


			return($process);
		}
	}