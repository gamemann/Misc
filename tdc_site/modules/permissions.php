<?php
	class permissions
	{
		protected $db;
		
		public function init()
		{
			global $M;
			$this->M = $M;
			
			$this->db = $this->M['mysql']->receiveDataBase();
		}
		
		public function havePermission($userInfo, $key)
		{
			$toReturn = false;
			
			if ($userInfo)
			{
				$query = $this->db->query("SELECT * FROM `permissions` WHERE `groupid`=" . $userInfo['groupid'] . " AND `key`='" . $key . "'");
				
				if ($query)
				{
					while ($row = $query->fetch_assoc())
					{
						if ($row['value'] == 1)
						{
							// As simple as that.
							return true;
						}
					}
				}
			}
		}
	}
?>