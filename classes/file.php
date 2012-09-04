<?php
    class file
    {
    	private $filepath;
		
		function __construct($param) {
			$this->filepath = $param;
		}
		
		public function getContent() {
			if(file_exists($this->filepath)) {
				$content = file_get_contents($this->filepath);
			} else {
				$content = "";
			}
			return $content;
		}
		
		public function setContent($content) {
			file_put_contents($this->filepath, $content);
		}
    }
?>