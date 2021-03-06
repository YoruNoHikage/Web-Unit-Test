<?php
	require_once "Model.php";

	class ProjectModel extends Model{

		public function getAllProjects(){
			$projectArray = array();

			$sth = $this->execute("SELECT * FROM project ORDER BY project.due_date DESC");
			$req = $sth->fetchAll();

			foreach($req as $projectDb){
				$project = new Project();
				$project->setId($projectDb["id"]);
				$project->setName($projectDb["name"]);
				$project->setEnabled($projectDb["enabled"]);
				$project->setDue_date(DateTime::createFromFormat("Y-m-d H:i:s", $projectDb["due_date"]));
				array_push($projectArray, $project);
			}
			return $projectArray;
		}

		public function getProjectStats($project)
		{
			$stats = array();
			foreach($project->getTests() as $test)
			{
				$params = array("testName" => $test->getName(), "projectId" => $project->getId());
				//nb subtests
				$sth = $this->execute("SELECT COUNT(*) AS nbSubtests FROM users_test WHERE test_name = :testName AND project_id = :projectId", $params);
				$req = $sth->fetch();
				$nbSubtests = $req["nbSubtests"];

				//nb success subtests
				$sth = $this->execute("SELECT COUNT(*) AS nbSubtestsSuccess FROM users_test WHERE test_name = :testName AND project_id = :projectId AND status = 1", $params);
				$req = $sth->fetch();
				$nbSubtestsSuccess = $req["nbSubtestsSuccess"];

				$stats[$test->getName()] = ($nbSubtests != 0) ? $nbSubtestsSuccess * 100 / $nbSubtests : 0;
			}
			return $stats;
		}

		public function getOneProjectBy($id)
		{
			$sth = $this->execute("SELECT * FROM project WHERE project.id = :projectId", array("projectId" => $id));
			$req = $sth->fetch();
            
            $group = new Group();
            $group->setName($req['target_group']);

			$project = new Project();
			$project->setId($req["id"]);
			$project->setName($req["name"]);
			$project->setEnabled($req["enabled"]);
			$project->setDue_date(DateTime::createFromFormat("Y-m-d H:i:s", $req["due_date"]));
            $project->setTargetGroup($group);

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
				$project->setDue_date(DateTime::createFromFormat("Y-m-d H:i:s", $projectDb["due_date"]));
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
        
        public function getProjectsFromGroup($group)
        {
            $sth = $this->execute("SELECT * FROM project WHERE project.target_group = :groupname",
                                  array("groupname" => $group->getName()));
            
            $projectsDb = $sth->fetchAll();
            
            $projects = array();
            foreach($projectsDb as $projectDb)
            {
                $project = new Project();
                $project->setId($projectDb['id']);
                $project->setName($projectDb['name']);
                $project->setOwner($projectDb['username']);
                $project->setEnabled($projectDb['enabled']);
                $project->setDue_date(DateTime::createFromFormat("Y-m-d H:i:s", $projectDb["due_date"]));
                
                $group = new Group();
                $group->setName($projectDb['target_group']);
                $project->setTargetGroup($group);
                
                array_push($projects, $project);
            }
            
            return $projects;
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

		public function getAllTestNames($projectId)
		{
			$sth = $this->execute("SELECT test.name FROM test WHERE test.project_id = :project_id", 
                                  array("project_id" => $projectId));
			$namesDb = $sth->fetchAll();

			$namesArray = array();
			foreach($namesDb as $nameDb)
			{
				array_push($namesArray, $nameDb["name"]);
			}
			return $namesArray;
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
			//envoi du projet
			$sth = $this->execute("INSERT INTO project (username, name, enabled, due_date, target_group) 
                                    VALUES (:username, :projectName, :enabled, :due_date, :group)", array(
				"username" => $project->getOwner()->getUsername(),
				"projectName" => $project->getName(),
				"enabled" => $project->getEnabled(),
				"due_date" => $project->getDue_date()->format("Y-m-d H:i:s"),
                "group" => $project->getTargetGroup()->getName()
			));
			//on recupere son id
			$sth = $this->execute("SELECT LAST_INSERT_ID() as lastInsertId");
			$req = $sth->fetch();
			$project->setId(intval($req["lastInsertId"]));
		}
        
        public function updateProject($project)
		{
			 $sth = $this->execute("UPDATE project SET name = :projectName, enabled = :enabled, due_date = :due_date, target_group = :targetgroup
                                    WHERE id = :id",
                                   array("id" => $project->getId(), 
                                         "projectName" => $project->getName(),
                                         "enabled" => $project->getEnabled(),
                                         "due_date" => $project->getDue_date()->format("Y-m-d H:i:s"),
                                         "targetgroup" => $project->getTargetGroup()->getName()
                                        ));
			$req = $sth->fetch();
		}

		public function addTests($projectId, $tests)
		{
			$testsQuery = "INSERT INTO test (project_id, name, description) VALUES ";
			$subtestsQuery = "INSERT INTO subtest (project_id, test_name, name, weight, kind) VALUES ";

			$testEntries = array();
			$subtestEntries = array();
			$testParams = array();
			$subtestParams = array();
			$i = 0;

			foreach($tests as $test)
			{
				array_push($testEntries, "(:project_id_" . $i . ", :name_" . $i . ", :description_" . $i . ")");
				$testParams["project_id_" . $i] = $projectId;
				$testParams["name_" . $i] = $test->getName();
				$testParams["description_" . $i] = $test->getDescription();
				$i++;

				$subtests = $test->getSubtests();
				foreach($subtests as $subtest)
				{
					array_push($subtestEntries, "(:project_id_" . $i . ", :test_name_" . $i . ", :name_" . $i . ", :weight_" . $i . ", :kind_" . $i . ")");
					$subtestParams["project_id_" . $i] = $projectId;
					$subtestParams["test_name_" . $i] = $test->getName();
					$subtestParams["name_" . $i] = $subtest->getName();
					$subtestParams["weight_" . $i] = $subtest->getWeight();
					$subtestParams["kind_" . $i] = $subtest->getKind();
					$i++;
				}
			}
			$sth = $this->execute($testsQuery . implode(", ", $testEntries), $testParams);
			$sth = $this->execute($subtestsQuery . implode(", ", $subtestEntries), $subtestParams);
		}
        
        public function updateTestsSubtests($projectId, $tests)
        {            
            $display_names = array();
            
            foreach($tests as $test)
            {
            	$subtests = array();
            	$sql = "UPDATE subtest
                    SET weight = CASE name ";

                $subs = $test->getSubtests();

                foreach($subs as $sub)
                {
	                // TO DO : Fix this
	                // doesn't work with redundancy
	                $sql .= "WHEN '" . $sub->getName() . "' THEN " . $sub->getWeight() . " ";
	                $display_names["'" . $sub->getName() . "'"] = $sub->getWeight();
	            }

                $names = implode(',', array_keys($display_names));
            
	            $sql .= "END WHERE name IN (" . $names . ") AND test_name = '" . $test->getName() . "' AND project_id = " . $projectId;
	        	$sth = $this->execute($sql);
            }          
        }

		public function deleteProject($projectId)
		{
			echo $projectId;
			$param["projectId"] = $projectId;
			$sth = $this->execute("DELETE FROM users_test WHERE users_test.project_id = :projectId;", $param);
			$sth = $this->execute("DELETE FROM subtest WHERE subtest.project_id = :projectId;", $param);
			$sth = $this->execute("DELETE FROM test WHERE test.project_id = :projectId;", $param);
			$sth = $this->execute("DELETE FROM project WHERE project.id = :projectId;", $param);
		}
	}