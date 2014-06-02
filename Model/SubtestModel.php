<?php
	require_once "Model.php";

	class SubtestModel extends Model{

		public function getTestResults($subtest){
			$sth = $this->execute("SELECT * FROM users_test WHERE users_test.subtest_name = :subtestName AND users_test.test_name = :testName AND users_test.project_id = :projectId", 
				array("subtestName" => $subtest->getName(), "testName" => $subtest->getTest()->getName(), "projectId" => $subtest->getTest()->getProject()->getId()));
			$resultsDb = $sth->fetchAll();
			
			$test->resetResults();
			foreach($resultsDb as $resultDb){
				$result = new Result();
				$result->setStatus($resultDb["status"]);
				$result->setError($resultDb["errors"]);
				$subtest->addResult($subtest, $resultDb["username"]);
			}
			return $subtest;
		}
	}