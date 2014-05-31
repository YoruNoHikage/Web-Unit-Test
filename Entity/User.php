<?php
	class User{
		
		private $username;
		private $firstname;
		private $lastname;
		private $hash;
		private $mail;
		private $role;
		private $groups;
		private $results;
		private $projects;

		function __construct(){
			$this->groups = array();
			$this->results = array();
			$this->projects = array();
		}

		public function getUsername(){
			return $this->username;
		}

		public function setUsername($username){
			$this->username = $username;
		}

		public function getFirstname(){
			return $this->firstname;
		}

		public function setFirstname($firstname){
			$this->firstname = $firstname;
		}

		public function getLastname(){
			return $this->lastname;
		}

		public function setLastname($lastname){
			$this->lastname = $lastname;
		}

		public function getHash(){
			return $this->hash;
		}

		public function setHash($hash){
			$this->hash = $hash;
		}

		public function getMail(){
			return $this->mail;
		}

		public function setMail($mail){
			$this->mail = $mail;
		}

		public function getRole(){
			return $this->role;
		}

		public function setRole($role){
			$this->role = $role;
		}

		public function getGroups(){
			return $this->groups;
		}

		public function setGroups($groups){
			$this->groups = $groups;
		}

		public function resetGroups(){
			$this->groups = array();
		}

		public function addGroup($group){
			// ?
			array_push($this->groups, $group);
		}

		public function removeGroup($group){
			foreach ($this->groups as $index => $groupIteration){
				if($groupIteration->getName() == $group->getName()){
					$groupIteration->removeUser($this);
					array_splice($this->groups, $index, 1);
				}
			}
		}

		public function getProjects(){
			return $this->projects;
		}

		public function setProjects($projects){
			$this->projects = $projects;
		}

		public function resetProjects(){
			$this->projects = array();
		}

		public function addProject($project){
			$project->setOwner($this);
			array_push($this->projects, $project);
		}

		public function removeProject($project){
			foreach ($this->project as $index => $projectIteration){
				if($projectIteration->getId() == $project->getId())
					array_splice($this->projects, $index, 1);
			}
		}

		public function getResults(){
			return $this->results;
		}

		public function setResults($results){
			$this->results = $results;
		}

		public function resetResults(){
			$this->results = array();
		}

		public function addResult($result, $subtestFullName){
			$this->results[$subtestFullName] = $result;
		}

		public function getSubtestResults($subtest){
			return $this->results[$subtest->getFullName()];
		}

		public function removeResult($subtest){
			if(isset($results[$subtest->getFullName()])){
				$user->removeResult($this);
				unset($results[$user->getUsername()]);
			}
		}

		public function toString(){
			$groups = "";
			if(count($this->groups) > 0){
				foreach($this->groups as $group)
					$groups .= ($group->getName().";");
			}
			else
				$groups = "doesn't belong to any group";

			$projects = "";
			if(count($this->projects) > 0){
				foreach($this->projects as $project)
					$projects .= ($project->getName().";");
			}
			else
				$projects = "doesn't has any projects";

			return "username=" . $this->username . "<br/>"
			. "firstname=" . $this->firstname . "<br/>"
			. "lastname=" . $this->lastname . "<br/>"
			. "role=" . $this->role->getRole() . "<br/>"
			. "groups=" . $groups . "<br/>"
			. "projects=" . $projects . "<br/>";
		}
	}
?>