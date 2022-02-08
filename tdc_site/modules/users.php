<?php
	class users
	{
		protected $db;
		
		public function init()
		{
			global $M;
			$this->M = $M;
			
			$this->db = $this->M['mysql']->receiveDataBase();
		}
		
		public function getUserInfo($id = 0)
		{
			$userInfo = false;
			
			if ($id > 0)
			{
				$query = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $id);
				
				if ($query)
				{
					while ($row = $query->fetch_assoc())
					{
						$userInfo = json_decode($row['sinfo'], true);
						
						// Additional information.
						$userInfo['id'] = $row['id'];
						$userInfo['lastIP'] = $row['lastIP'];
						$userInfo['lastupdated'] = $row['lastupdated'];
						$userInfo['timeadded'] = $row['timeadded'];
						$userInfo['groupid'] = $row['groupid'];
					}
				}
			}
			else
			{
				if (isset($_SESSION['steamid']))
				{
					// First, update the user's information.
					$this->updateUser();
					
					require($_SERVER['DOCUMENT_ROOT'] . '/steamauth/userInfo.php');
					
					$userInfo = $steamprofile;
					
					// Additional information.
					$query = $this->db->query("SELECT * FROM `users` WHERE `sid`='" . $userInfo['steamid'] . "'");
					
					if ($query)
					{
						while ($row = $query->fetch_assoc())
						{
							$userInfo['id'] = $row['id'];
							$userInfo['lastIP'] = $row['lastIP'];
							$userInfo['lastupdated'] = $row['lastupdated'];
							$userInfo['timeadded'] = $row['timeadded'];
							$userInfo['groupid'] = $row['groupid'];
						}
					}
				}
			}
			
			return $userInfo;
		}
		
		// Updates the user.
		public function updateUser()
		{
			if (isset($_SESSION['steamid']))
			{
				require_once($_SERVER['DOCUMENT_ROOT'] . '/steamauth/userInfo.php');
				
				$cQuery = $this->db->query("SELECT * FROM `users` WHERE `sid`='" . $steamprofile['steamid'] . "'");
				
				if ($cQuery)
				{
					$sInfo = $this->M['main']->json_readable_encode($steamprofile);
					if ($cQuery->num_rows > 0)
					{
						// Update user.
						$this->db->query("UPDATE `users` SET `sinfo`='" . $sInfo . "', `lastIP`='" . $_SERVER['REMOTE_ADDR'] . "', `lastupdated`=" . time() . " WHERE `sid`='" . $steamprofile['steamid'] . "'");
					}
					else
					{
						// Insert user.
						$this->db->query("INSERT INTO `users` (`sid`, `groupid`, `sinfo`, `timeadded`, `lastupdated`, `lastIP`) VALUES ('" . $steamprofile['steamid'] . "', 1, '" . $sInfo . "', " . time() . ", " . time() . ", '" . $_SERVER['REMOTE_ADDR'] . "')");
					}
				}
			}
		}
		
		// Formats an Username.
		public function formatUser($id = 0, $menu = true)
		{
			$toReturn = '<Not Found>';
			
			if ($id > 0)
			{
				$query = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $id);
				
				if ($query)
				{
					while ($row = $query->fetch_assoc())
					{
						$query2 = $this->db->query("SELECT * FROM `usergroups` WHERE `id`=" . $row['groupid']);
						
						if ($query2)
						{
							while ($row2 = $query2->fetch_assoc())
							{
								$userInfo = json_decode($row['sinfo'], true);
								$color = $row2['color'];
								
								$menuText = '';
								
								if ($menu)
								{
									$menuText = ' onmouseover="pushCard(this, ' . $row['id'] . ');" onmouseleave="pullCard(this, ' . $row['id'] . ');"';
								}
								
								$toReturn = '<a href="' . $userInfo['profileurl'] . '" target="_blank" style="text-decoration: none;" ' . $menuText . '><span style="color: ' . $row2['color'] . ';">' . $userInfo['personaname'] . '</span></a>';
							}
						}
					}
				}
			}
			
			return $toReturn;
		}
		
		// Check if a user is in a "disabled" group.
		public function isUserBanned($userInfo)
		{
			if ($userInfo)
			{
				$query = $this->db->query("SELECT * FROM `usergroups` WHERE `id`=" . $id);
				
				if ($query)
				{
					while ($row = $query->fetch_assoc())
					{
						if ($row['disabled'] == 1)
						{
							return true;
						}
					}
				}
			}
			return false;
		}
		
		// Check if the user has power. 5 by default is the highest value (e.g. Roy, etc).
		public function checkLevel($userInfo, $powerLevel=5)
		{	
			if ($userInfo)
			{
				$query = $this->db->query("SELECT * FROM `usergroups` WHERE `id`=" . $userInfo['groupid']);
				
				if ($query)
				{
					while ($row = $query->fetch_assoc())
					{
						// Check the level.
						if ($row['level'] >= $powerLevel)
						{
							return true;
						}
					}
				}
			}
			
			return false;
		}
		
		// Formats A Group.
		public function formatGroup($id, $type=3)
		{
			$toReturn = 'Unknown';
			/*
				Types:
				1 - Use style.
				2 - Use color.
				3 - Use both (color -> style).
			*/
		
			$query = $this->db->query("SELECT * FROM `usergroups` WHERE `id`=" . $id);
			
			if ($query)
			{
				while ($row = $query->fetch_assoc())
				{
					$toReturn = '<a href="/pages/users/viewusers.php?groupid=' . $row['id'] . '" style="text-decoration: none;">';
					
					if ($type == 1)
					{
						$toReturn .= '<span style="' . $row['style'] . '">' . $row['display'] . '</span>';
					}
					elseif ($type == 2)
					{
						$toReturn .= '<span style="color: ' . $row['color'] . ';">' . $row['display'] . '</span>';
					}
					elseif ($type == 3)
					{
						$toReturn .= '<span style="color: ' . $row['color'] . ';"><span style="' . $row['style'] . '">' . $row['display'] . '</span></span>';
					}
					else
					{
						// Use default.
						$toReturn .= '<span style="color: ' . $row['color'] . ';"><span style="' . $row['style'] . '">' . $row['display'] . '</span></span>';
					}
					
					$toReturn .= '</a>';
				}
			}
			
			return $toReturn;
		}
	}
?>