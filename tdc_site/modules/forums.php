<?php
	class forums
	{
		protected $db;
		
		public function init()
		{
			global $M;
			$this->M = $M;
			
			$this->db = $this->M['mysql']->receiveDataBase();
		}
		
		// Check if a forum exists in the database.
		public function isValidForum($id = 0)
		{
			$query = $this->db->query("SELECT * FROM `forum-forums` WHERE `id`=" . $id);
			
			if ($query && $query->num_rows > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}		
		
		// Check if a topic exists in the database.
		public function isValidTopic($id = 0)
		{
			$query = $this->db->query("SELECT * FROM `forum-threads` WHERE `id`=" . $id);
			
			if ($query && $query->num_rows > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		// Get the reply count for the topic.
		public function getReplyCount($id = 0)
		{
			$query = $this->db->query("SELECT * FROM `forum-replies` WHERE `topicid`=" . $id);
			
			if ($query)
			{
				return $query->num_rows;
			}
			
			return 0;
		}
		
		// Get the statistics.
		public function getStats()
		{
			$toReturn = array();
			
			$query = $this->db->query("SELECT * FROM `forum-forums` WHERE `iscat`=1");
			
			if ($query)
			{
				$toReturn['categories'] = $query->num_rows;
			}			
			
			$query = $this->db->query("SELECT * FROM `forum-forums` WHERE `iscat`=0");
			
			if ($query)
			{
				$toReturn['forums'] = $query->num_rows;
			}			
			
			$query = $this->db->query("SELECT * FROM `forum-threads`");
			
			if ($query)
			{
				$toReturn['topics'] = $query->num_rows;
			}			
			
			$query = $this->db->query("SELECT * FROM `forum-replies`");
			
			if ($query)
			{
				$toReturn['replies'] = $query->num_rows;
			}
			
			return $toReturn;
		}
	}
?>