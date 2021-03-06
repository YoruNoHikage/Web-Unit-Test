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

		public function getNbUsers(){
			$userArray = array();

			$sth = $this->execute("SELECT COUNT(*) AS nb_users FROM users");
			$req = $sth->fetch();
			$nbUsers = $req['nb_users'];

			return $nbUsers;
		}
		
		public function getOneUserBy($username)
        {
            $sth = $this->execute("SELECT u.username, u.hash, u.firstname, u.lastname, u.mail, u.role, ug.group_name 
                                    FROM users u 
                                    LEFT JOIN users_groups ug ON u.username = ug.username 
                                    LEFT JOIN groups g ON ug.group_name = g.name
                                    WHERE u.username = :username",
                                    array('username' => $username));
            $userDb = $sth->fetchAll();
            
            if($userDb)
            {
                $user = new User();
                $user->setUsername($userDb[0]['username']);
                $user->setFirstname($userDb[0]['firstname']);
				$user->setLastname($userDb[0]['lastname']);
				$user->setMail($userDb[0]['mail']);
                $user->setRole($userDb[0]['role']);
                foreach($userDb as $userGroup)
                {
                    $group = new Group();
                    $group->setName($userGroup['group_name']);
                    $user->addGroup($group);
                }
                
                var_dump($user);
                
                return $user;
            }
            
            return null;
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
				
				$project = new Project();
				$project->setId($projectDb["id"]);
				$project->setName($projectDb["name"]);
				$project->setEnabled($projectDb["enabled"]);
				$project->setDue_date(DateTime::createFromFormat("Y-m-d H:i:s", $projectDb["due_date"]));
				$user->addProject($project);
			}
			return $user;
		}

		public function getUserResults($user){
			$sth = $this->execute("SELECT users_test.*, subtest.* FROM users
				INNER JOIN users_test ON users.username = users_test.username
				INNER JOIN subtest ON users_test.subtest_name = subtest.name
				AND users_test.test_name = subtest.test_name
				AND users_test.project_id = subtest.project_id
				WHERE users.username = :username", 
				array("username" => $user->getUsername()));

			$resultsDb = $sth->fetchAll();
			
			foreach($resultsDb as $resultDb){
				$result = new Result();
				$result->setStatus($resultDb["status"]);
				$result->setError($resultDb["errors"]);

				//full subtest name ?
				$subtest = new Subtest();
				$subtest->setName($resultDb["name"]);
				$subtest->setFullname($resultDb["name"] . ":" . $resultDb["test_name"] . ":" . $resultDb["project_id"]);
				$subtest->setWeight($resultDb["weight"]);
				$subtest->setKind($resultDb["kind"]);

				$user->addResult($result, $subtest);
			}
			return $user;
		}
		
		public function getUserWithProjectResults($userId, $projectId){
			$sth = $this->execute("SELECT * FROM users AS u 
				                    NATURAL JOIN users_test AS ut 
				                    NATURAL JOIN subtest AS st 
				                    WHERE u.username = :username 
				                        AND ut.project_id = :projectid
				                        AND ut.test_name = st.test_name
				                        AND ut.subtest_name=st.name
				                        AND ut.project_id = st.project_id;",
				                    array("username" => $userId,
				                          "projectid" => $projectId));

			$resultsDb = $sth->fetchAll();
			
			$user = new User();
            $user->setUsername($resultsDb[0]['username']);
            $user->setFirstname($resultsDb[0]['firstname']);
            $user->setLastname($resultsDb[0]['lastname']);
            $user->setMail($resultsDb[0]['mail']);
            $user->setRole($resultsDb[0]['role']);
			
			foreach($resultsDb as $resultDb)
            {
				$result = new Result();
				$result->setStatus($resultDb["status"]);
				$result->setError($resultDb["errors"]);

				//full subtest name ?
				$subtest = new Subtest();
				$subtest->setName($resultDb["name"]);
				$subtest->setFullname($resultDb["name"] . ":" . $resultDb["test_name"] . ":" . $resultDb["project_id"]);
				$subtest->setWeight($resultDb["weight"]);
				$subtest->setKind($resultDb["kind"]);
				
				$test = new Test();
				$test->setName($resultDb["test_name"]);
				$subtest->setTest($test);

				$user->addResult($result, $subtest);
			}
			return $user;
		}

		public function deleteUserResults($user, $projectId)
		{
			$params = array("projectId" => $projectId, "username" => $user->getUsername());
			$sth = $this->execute("DELETE FROM users_test WHERE users_test.project_id = :projectId AND users_test.username = :username", $params);
			var_dump($sth->errorInfo());
		}
	}