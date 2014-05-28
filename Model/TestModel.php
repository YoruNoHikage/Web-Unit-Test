<?php
	require_once "Model.php";

	class TestModel extends Model{

		//probably useless
		/*public function getAllTests(){
			$testArray = array();

			$sth = $this->execute("SELECT * FROM test");
			$req = $sth->fetchAll();

			foreach($req as $testDb){
				$test = new Test();
				$test->setName($testDb["name"]);
				$test->setDescription($testDb["description"]);
				array_push($testArray, $test);
			}
			return $testArray;
		}*/

		public function getTestSubtests($test){
			$sth = $this->execute("SELECT * FROM subtest WHERE subtest.test_name = :testName AND subtest.project_id = :projectId", 
				array("testName" => $test->getName(), "projectId" => $test->getProject()->getId()));
			$subtestsDb = $sth->fetchAll();
			
			foreach($subtestsDb as $subtestDb){
				$subtest = new Subtest();
				$subtest->setName($subtestDb["name"]);
				$subtest->setWeight($subtestDb["weight"]);
				$subtest->setKind($subtestDb["kind"]);
				$test->addSubtest($subtest);
			}
			return $test;
		}
	}