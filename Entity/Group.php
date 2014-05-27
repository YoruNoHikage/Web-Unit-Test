<?php
	class Group{

		private $name;
		private $users;

		function __construct(){
			$this->users = array();
		}

		public function getName(){
			return $this->name;
		}

		public function setName($name){
			$this->name = $name;
		}

		public function getUsers(){
			return $this->users;
		}

		public function setUsers($users){
			$this->users = $users;
		}

		public function addUser($user){
			// ?
			array_push($this->users, $user);
		}

		public function removeUser($user){
			foreach ($this->users as $index => $userIteration){
				if($userIteration->getUsername() == $user->getUsername()){
					$userIteration->removeGroup($user);
					array_splice($this->users, $index, 1);
				}
			}
		}
	}
?>