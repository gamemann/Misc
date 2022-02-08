<?php
	class servers
	{
		protected $db;
		
		public function init()
		{
			global $M;
			$this->M = $M;
			
			$this->db = $this->M['mysql']->receiveDataBase();
		}
		
		// Check if the user has access to the server.
		public function havePermission($userInfo, $sID=0)
		{
			$toReturn = false;
			$query = $this->db->query("SELECT * FROM `gameservers` WHERE `id`=" . $sID);
			
			if ($query)
			{
				while ($row = $query->fetch_assoc())
				{
					if ($row['haspermissions'] == 0)
					{
						$toReturn = true;
						continue;
					}
					
					if ($userInfo)
					{
						$permissions = json_decode($row['permissions'], true);
						
						// Check group permissions.
						foreach ($permissions['groups'] as $group)
						{
							if ($group == $userInfo['groupid'])
							{
								$toReturn = true;
							}
						}
						
						// Check individual permissions.
						if ($toReturn === FALSE)
						{
							foreach ($permissions['users'] as $user)
							{
								if ($user == $userInfo['id'])
								{
									$toReturn = true;
								}
							}
						}
					}
				}
			}
			
			return $toReturn;
		}
		
		// Checks if a server exists in the database.
		public function isValidServer($id)
		{
			$query = $this->db->query("SELECT * FROM `gameservers` WHERE `id`=" . $id);
			
			if ($query && $query->num_rows > 0)
			{
				return true;
			}
			
			return false;
		}
		
		// Checks if a user has Server Manager for the specific server.
		public function isManager($userInfo, $sID)
		{
			if ($userInfo)
			{
				$query = $this->db->query("SELECT * FROM `gameservers` WHERE `id`=" . $sID);
				
				if ($query)
				{
					while ($row = $query->fetch_assoc())
					{
						$details = json_decode($row['details'], true);
						if (isset($details['managers']))
						{
							foreach ($details['managers'] as $manager)
							{
								if ($manager == $userInfo['id'])
								{
									return true;
								}
							}
						}
					}
				}
			}
			
			return false;
		}
	}
?>