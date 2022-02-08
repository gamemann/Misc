<?php
	class config
	{
		protected $db;
		
		public function init()
		{
			global $M;
			$this->M = $M;
			
			$this->db = $this->M['mysql']->receiveDataBase();
		}
		
		// Gets a config variable.
		public function getValue($key)
		{
			$toReturn = 0;
			
			$query = $this->db->query("SELECT * FROM `config` WHERE `key`='" . $key . "'");
			
			if ($query)
			{
				while ($row = $query->fetch_assoc())
				{
					$toReturn = $row['value'];
				}
			}
			
			return $toReturn;
		}
	}
?>