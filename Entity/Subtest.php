<?php
	class Subtest {
		
		private $name;
		private $weight;
		private $kind;
		private $test;
		private $results;

		function __construct(){
			$this->results = array();
		}

		public function getName(){
			return $this->name;
		}

		public function getFullName(){
			return $this->name + ":" + $this->test->getFullName();
		}

		public function setName($name){
			$this->name = $name;
		}

		public function getWeight(){
			return $this->weight;
		}

		public function setWeight($weight){
			$this->weight = $weight;
		}

		public function getKind(){
			return $this->kind;
		}

		public function setKind($kind){
			$this->kind = $kind;
		}

		public function getTest(){
			return $this->test;
		}

		public function setTest($test){
			$this->test = $test;
		}

		public function getResults(){
			return $this->results;
		}

		public function setResults($results){
			$this->results = $results;
		}

		public function addResult($result, $username){
			$this->results[$username] = $result;
		}

		public function getUserResults($user){
			return $this->results[$user->getUsername()];
		}

		public function removeResult($user){
			if(isset($this->results[$user->getUsername()])){
				$user->removeResult($this);
				unset($results[$user->getUsername()]);
			}
		}
	}
?>