<?php
	class main
	{
		// Config goes here.
		// *
		protected $db;
		
		public function init()
		{
			// Other Modules.
			global $M;
			$this->M = $M;
			
			$this->db = $this->M['mysql']->receiveDataBase();
		}
		
		// Loads all the JavaScript files.
		public function loadJS()
		{
			// Add files outside the JavaScript folder here.
			echo '<script src="/DataTables/datatables.min.js" type="text/javascript"></script>';	// DataTables
			
			// Default Files (declared in the database).
			$query = $this->db->query("SELECT * FROM `loadfiles` WHERE `type`=2 ORDER BY `listorder` ASC");
			
			if ($query)
			{
				while ($row = $query->fetch_assoc())
				{
					echo '<script src="/js/' . $row['file'] . '" type="text/javascript"></script>';
				}
			}
		}
		
		// Loads all the CSS files.
		public function loadCSS()
		{
			// Add files outside the JavaScript folder here.
			echo '<link rel="stylesheet" type="text/css" href="/DataTables/datatables.min.css" />';
			
			// Default Files (declared in the database).
			$query = $this->db->query("SELECT * FROM `loadfiles` WHERE `type`=1 ORDER BY `listorder` ASC");
			
			if ($query)
			{
				while ($row = $query->fetch_assoc())
				{
					echo '<link rel="stylesheet" type="text/css" href="/css/' . $row['file'] . '" />';
				}
			}
		}
		
		// Loads the NavBar.
		public function loadNavBar($currentPage='', $userInfo = 0)
		{
			echo '<div id="navbar" class="container">';
				echo '<ul id="navlist">';
					$query = $this->db->query("SELECT * FROM `navbar` WHERE `parent`=0 ORDER BY `listorder` ASC");
					
					if ($query)
					{
						while ($row = $query->fetch_assoc())
						{
							$newTab = '';
							$selected = '';
							
							if ($row['newtab'])
							{
								$newTab = 'target="_new"';
							}
							
							// Check if it's the same file.
							$fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $row['url'];
							
							if ($fullPath == $currentPage)
							{
								$selected = ' navSelected';
							}
							
							echo '<a href="/' . $row['url'] . '" ' . $newTab . '><li class="navitem' . $selected . '">' . $row['display'] . '</li></a>';
						}
					}
				echo '</ul>';
			echo '</div>';
		}
		
		// Loads the Logo.
		public function loadLogo()
		{
			// Currently, it's going to be text only.
			echo '<div id="logo">';
				echo '<h1 class="text-center">TDC <span class="gstext">Game Servers</span></h1>';
			echo '</div>';
		}
		
		// Retrieves all the stats.
		public function getStats()
		{
			$toReturn = array();
			
			$query = $this->db->query("SELECT * FROM `users`");
			
			if ($query)
			{
				$toReturn['users'] = $query->num_rows;
			}
			
			return $toReturn;
		}
		
		// Loads the footer.
		public function loadFooter()
		{
			echo '<div id="footer">';
				echo '<p>Website coded by <a href="http://steamcommunity.com/id/halladay/" target="_blank">Christian Deacon</a>.</p>';
				echo '<p>Website started on <span class="colored"><strong>11-14-15</strong></span>.</p>';
			echo '</div>';
		}
		
		// From the web.
		public function json_readable_encode($in, $indent = 0, $from_array = false)
		{
			$_myself = __FUNCTION__;
			$_escape = function ($str)
			{
				return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $str);
			};

			$out = '';

			foreach ($in as $key=>$value)
			{
				$out .= str_repeat("\t", $indent + 1);
				$out .= "\"".$_escape((string)$key)."\": ";

				if (is_object($value) || is_array($value))
				{
					$out .= "\n";
					$out .= $this->json_readable_encode($value, $indent + 1);
				}
				elseif (is_bool($value))
				{
					$out .= $value ? 'true' : 'false';
				}
				elseif (is_null($value))
				{
					$out .= 'null';
				}
				elseif (is_string($value))
				{
					$out .= "\"" . $_escape($value) ."\"";
				}
				else
				{
					$out .= $value;
				}

				$out .= ",\n";
			}

			if (!empty($out))
			{
				$out = substr($out, 0, -2);
			}

			$out = str_repeat("\t", $indent) . "{\n" . $out;
			$out .= "\n" . str_repeat("\t", $indent) . "}";

			return $out;
		}
		
		// Loads the UserBar
		public function loadUserBar($userInfo = 0)
		{
			echo '<div id="userbar">';
				echo '<ul id="userbar-navlist">';
					if ($userInfo)
					{
						echo '<li class="userbar-navitemuser">Welcome back, ' . $userInfo['personaname'] . '!</li>';
						echo '<a href="/index.php?logout"><li class="userbar-navitem">Log Out</li></a>';
					}
					else
					{
						echo '<a href="/index.php?login"><li class="userbar-navitem">Log In!</li></a>';
					}
				echo '</ul>';
			echo '</div>';
		}
	};
?>