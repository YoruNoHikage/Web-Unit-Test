<?php
	require_once "Model.php";

	class ProjectModel extends Model{

		public function getAllProjects(){
			$projectArray = array();

			$sth = $this->execute("SELECT * FROM project");
			$req = $sth->fetchAll();

			foreach($req as $projectDb){
				$project = new Project();
				$project->setId($projectDb["id"]);
				$project->setName($projectDb["name"]);
				$project->setEnabled($projectDb["enabled"]);
				$project->setDue_date($projectDb["due_date"]);
				array_push($projectArray, $project);
			}
			return $projectArray;
		}

		public function getProjectOwner($project){
			$sth = $this->execute("SELECT * FROM users INNER JOIN project ON project.username = users.username AND project.id = :projectId", 
				array("projectId" => $project->getId()));
			$ownerDb = $sth->fetch();

			$owner = new User();
			$owner->setUsername($ownerDb["username"]);
			$owner->setHash($ownerDb["hash"]);
			$owner->setFirstname($ownerDb["firstname"]);
			$owner->setLastname($ownerDb["lastname"]);
			$owner->setMail($ownerDb["mail"]);

			$project->setOwner($owner);
			$owner->addProject($project);

			return $project;
		}

		public function getProjectTests($project){
			$sth = $this->execute("SELECT test.name, test.description FROM test WHERE test.project_id = :projectId", 
				array("projectId" => $project->getId()));
			$testsDb = $sth->fetchAll();

			$project->resetTests();
			foreach($testsDb as $testDb){
				$test = new Test();
				$test->setName($testDb["name"]);
				$test->setDescription($testDb["description"]);

				$project->addTest($test);
			}
			return $project;
		}
	}