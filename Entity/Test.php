<?php
	class Test {
		
		private $name;
		private $description;
		private $project;
		private $subtests;

		function __construct(){
			$this->subtests = array();
		}
		
		public function getName(){
			return $this->name;
		}

		public function getFullName(){
			return $this->name + ":" + $this->project->getId();
		}

		public function setName($name){
			$this->name = $name;
		}

		public function getDescription(){
			return $this->description;
		}

		public function setDescription($description){
			$this->description = $description;
		}

		public function getProject(){
			return $this->project;
		}

		public function setProject($project){
			$this->project = $project;
		}

		public function getSubtests(){
			return $this->subtests;
		}

		public function setSubtests($subtests){
			$this->subtests = $subtests;
		}

		public function addSubtest($subtest){
			$subtest->setTest($this);
			array_push($this->subtests, $subtest);
		}

		public function removeSubtest($subtest){
			foreach ($this->subtests as $index => $subtestIteration){
				if($subtestIteration->getName() == $subtest->getName())
					array_splice($this->subtests, $index, 1);
			}
		}

		public function toString(){
			$subtestsToString = "";
			foreach ($this->subtests as $subtestIteration){
				$subtestsToString .= ("------>" . $subtestIteration->getName() . "<br/>");
			}
			return $this->name . "<br/>" . $subtestsToString;
		}
	}
?>