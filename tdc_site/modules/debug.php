<?php
	class debug
	{
		protected $db;
		
		public function init()
		{
			global $M;
			$this->M = $M;
			
			$this->db = $this->M['mysql']->receiveDataBase();
		}
		
		public function logMessage($msg='', $key='')
		{
			$this->db->query("INSERT INTO `logs` (`message`, `key`, `timeadded`) VALUES ('" . $this->db->real_escape_string($msg) . "', '" . $this->db->real_escape_string($key) . "', " . time() . ");");
		}
	}
?>