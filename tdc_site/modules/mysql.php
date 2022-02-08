<?php
	class mysql
	{
		protected $db;
		
		public function init()
		{
			// Other Modules.
			global $M;
			$this->M = $M;
			
			// Variables.
			$this->db = new mysqli('localhost', 'xxxxx', 'xxxxx', 'xxxxx');
		}
		
		public function receiveDataBase()
		{
			return $this->db;
		}
	}
?>