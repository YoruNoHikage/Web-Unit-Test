<?php
	require_once "Model.php";

	class UserModel extends Model{

		public function getAllUsers(){
			$userArray = array();

			$sth = $this->execute("SELECT * FROM users");
			$req = $sth->fetchAll();

			foreach($req as $userDb){
				$user = new User();
				$user->setUsername($userDb["username"]);
				$user->setHash($userDb["hash"]);
				$user->setFirstname($userDb["firstname"]);
				$user->setLastname($userDb["lastname"]);
				$user->setMail($userDb["mail"]);
				array_push($userArray, $user);
			}
			return $userArray;
		}

		public function getUserRole($user){
			$sth = $this->execute("SELECT role.role FROM role INNER JOIN users ON role.role = users.role AND users.username = :username", 
				array("username" => $user->getUsername()));
			$roleDb = $sth->fetch();

			$role = new Role();
			$role->setRole($roleDb["role"]);
			$user->setRole($role);

			return $user;
		}

		public function getUserGroups($user){
			$sth = $this->execute("SELECT groups.name FROM groups INNER JOIN users_groups ON groups.name = users_groups.group_name INNER JOIN users ON users_groups.username = users.username AND users.username = :username", 
				array("username" => $user->getUsername()));
			$groupsDb = $sth->fetchAll();
			
			foreach($groupsDb as $groupDb){
				if($groupDb["name"] != null){
					$group = new Group();
					$group->setName($groupDb["name"]);
					$group->addUser($user);
					$user->addGroup($group);
				}
			}

			return $user;
		}

		public function getUserProjects($user){
			$sth = $this->execute("SELECT * FROM project INNER JOIN users ON project.username = users.username AND users.username = :username", 
				array("username" => $user->getUsername()));
			$projectsDb = $sth->fetchAll();

			foreach($projectsDb as $projectDb){
				if($projectDb["id"] != null
					&& $projectDb["name"] != null
					&& $projectDb["enabled"] != null
					&& $projectDb["due_date"] != null){
						$project = new Project();
						$project->setId($projectDb["id"]);
						$project->setName($projectDb["name"]);
						$project->setEnabled($projectDb["enabled"]);
						$project->setDue_date($projectDb["due_date"]);
						$user->addProject($project);
				}
			}
			return $user;
		}

		public function getUserResults($user){
			$sth = $this->execute("SELECT * FROM users_test WHERE users_test.username = :username", 
				array("username" => $user->getUsername()));
			$resultsDb = $sth->fetchAll();
			
			foreach($resultsDb as $resultDb){
				$result = new Result();
				$result->setStatus($resultDb["status"]);
				$result->setError($resultDb["errors"]);
				$user->addResult($subtest, $resultDb["subtest_name"] . ":" . $resultDb["test_name"] . ":"  . $resultDb["project_id"]);
			}
			return $user;
		}
	}