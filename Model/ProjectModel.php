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

		public function getOneProjectBy($id)
		{
			$sth = $this->execute("SELECT * FROM project WHERE project.id = :projectId", array("projectId" => $id));
			$req = $sth->fetch();

			$project = new Project();
			$project->setId($req["id"]);
			$project->setName($req["name"]);
			$project->setEnabled($req["enabled"]);
			$project->setDue_date($req["due_date"]);

			return $project;			
		}

		public function getProjectsBy($ids)
		{
			$projectArray = array();

			$sth = $this->execute("SELECT * FROM project IN (:ids)", array("ids", implode(",", $ids)));
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

		public function getProjectParticipants($project)
		{
			$sth = $this->execute("SELECT users.*, users_test.*, subtest.* FROM users
				INNER JOIN users_test ON users.username = users_test.username
				INNER JOIN subtest ON users_test.subtest_name = subtest.name
				AND users_test.test_name = subtest.test_name
				AND users_test.project_id = subtest.project_id
				WHERE subtest.project_id = :id_project",
				array("id_project" => $project->getId()));
			$resultsDb = $sth->fetchAll();

			$participants = array();
			foreach($resultsDb as $resultDb)
			{
				$newParticipant = null;
				foreach($participants as $participant)
				{
					if($resultDb["username"] == $participant->getUsername())
						$newParticipant = $participant;
				}
				if($newParticipant == null)
				{
					$newParticipant = new User();
					$newParticipant->setUsername($resultDb["username"]);
					$newParticipant->setHash($resultDb["hash"]);
					$newParticipant->setFirstname($resultDb["firstname"]);
					$newParticipant->setLastname($resultDb["lastname"]);
					$newParticipant->setMail($resultDb["mail"]);
					array_push($participants, $newParticipant);
				}
				$result = new Result();
				$result->setStatus($resultDb["status"]);
				$result->setError($resultDb["errors"]);

				$subtest = new Subtest();
				$subtest->setName($resultDb["subtest_name"]);
				$subtest->setFullname($resultDb["subtest_name"] . ":" . $resultDb["test_name"] . ":" . $resultDb["project_id"]);
				$subtest->setWeight($resultDb["weight"]);
				$subtest->setKind($resultDb["kind"]);

				$newParticipant->addResult($result, $subtest);
			}

			return $participants;
		}

		public function getProjectTests($project){
			$sth = $this->execute("SELECT test.name, test.description, test.project_id FROM test WHERE test.project_id = :projectId", 
				array("projectId" => $project->getId()));
			$testsDb = $sth->fetchAll();

			$project->resetTests();
			foreach($testsDb as $testDb){
				$test = new Test();
				$test->setName($testDb["name"]);
				$test->setFullname($testDb["name"] . ":" . $testDb["project_id"]);
				$test->setDescription($testDb["description"]);

				$project->addTest($test);
			}
			return $project;
		}

		public function getProjectTotalWeight($project)
		{
			$sth = $this->execute("SELECT subtest.weight FROM subtest WHERE subtest.project_id = :projectId", 
				array("projectId" => $project->getId()));
			$totalWeightDb = $sth->fetchAll();

			$totalWeight = 0;
			foreach($totalWeightDb as $subtestWeight)
			{
				$totalWeight += intval($subtestWeight["weight"]);
			}

			return $totalWeight;
		}

		public function newProject($project)
		{
			$sth = $this->execute("INSERT INTO project (username, name, enabled, due_date) VALUES (:username, :projectName, :enabled, NOW())", array(
				"username" => $project->getOwner()->getUsername(),
				"projectName" => $project->getName(),
				"enabled" => $project->getEnabled()
				/*"due_date" => $project->getDue_date()*/
			));

		}
	}