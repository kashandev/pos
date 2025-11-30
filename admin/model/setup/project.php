<?php

	class ModelSetupProject extends HModel {

		public function getTable()
		{
			return 'project';
		}

		public function getprojectCode()
		{
			$sql = "SELECT MAX(CONVERT(`code`, UNSIGNED)) AS `code` FROM project";
			$query = $this->db->query($sql);
			return $query->row;
		}

	}


?>