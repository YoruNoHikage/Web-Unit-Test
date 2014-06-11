<?php
	class Project {
		
		private $id;
		private $name;
		private $enabled;
		private $due_date;
		private $owner;
		private $tests;
        private $targetGroup;

		function __construct(){
			$this->tests = array();
		}

		public function getId(){
			return $this->id;
		}

		public function setId($id){
			$this->id = $id;
		}

		public function getName(){
			return $this->name;
		}

		public function setName($name){
			$this->name = $name;
		}

		public function getEnabled(){
			return $this->enabled;
		}

		public function setEnabled($enabled){
			$this->enabled = $enabled;
		}

		public function getDue_date(){
			return $this->due_date;
		}

		public function setDue_date($due_date){
			$this->due_date = $due_date;
		}

		public function getOwner(){
			return $this->owner;
		}

		public function setOwner($owner){
			$this->owner = $owner;
		}

		public function getTests(){
			return $this->tests;
		}

		public function setTests($tests){
			$this->tests = $tests;
		}

		public function resetTests(){
			$this->tests = array();
		}

		public function addTest($test){
			$test->setProject($this);
			array_push($this->tests, $test);
		}

		public function removeTest($test){
			foreach ($this->tests as $index => $testIteration){
				if($testIteration->getName() == $test->getName())
					array_splice($this->tests, $index, 1);
			}
		}
        
        public function getTargetGroup() {
            return $this->targetGroup;
        }
        
        public function setTargetGroup($group) {
            $this->targetGroup = $group;
        }

		public function toString(){
		
			$testsToString = "";
			foreach ($this->tests as $testIteration){
				$testsToString .= ("--->" . $testIteration->toString() . "<br/>");
			}

			return $this->id . "<br/>" . $testsToString;
		}
	}
?>