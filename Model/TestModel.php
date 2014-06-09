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
        
        public function getTestsSubtestsByProjectId($projectId) {
            $sth = $this->execute("SELECT test.project_id, test.name, description, subtest.name as subtest_name, weight, kind 
                                    FROM test 
                                    INNER JOIN subtest ON test.name = subtest.test_name
                                    WHERE test.project_id = :projectid", 
                                  array("projectid" => $projectId));
			
            $testsSubDb = $sth->fetchAll();
            
            $currentTest = null;
            $tests = array();
			foreach($testsSubDb as $key => $testSubDb)
            {
                if($key == 0 || $testsSubDb[$key]['name'] !== $currentTest->getName()) {
                    $currentTest = new Test();
                    $currentTest->setName($testSubDb['name']);
                    $currentTest->setFullname($testSubDb["name"] . ":" . $testSubDb["project_id"]);
                    $currentTest->setDescription($testSubDb['description']);
                }
                
                // in all cases, we have to add the subtest to the test
                $subtest = new Subtest();
                $subtest->setName($testSubDb["subtest_name"]);
                $subtest->setFullname($testSubDb["subtest_name"] . ":" . $testSubDb["name"] . ":" . $testSubDb["project_id"]);
                $subtest->setWeight($testSubDb["weight"]);
                $subtest->setKind($testSubDb["kind"]);
                $currentTest->addSubtest($subtest);
                
                // if the next line isn't the same subtest, we're done for it
                if(!isset($testsSubDb[$key + 1]) || $testsSubDb[$key + 1]['name'] !== $currentTest->getName()) {
                    array_push($tests, $currentTest);
                }
			}
			return $tests;
        }

		public function getTestSubtests($test){
			$sth = $this->execute("SELECT * FROM subtest WHERE subtest.test_name = :testName AND subtest.project_id = :projectId", 
				array("testName" => $test->getName(), "projectId" => $test->getProject()->getId()));
			$subtestsDb = $sth->fetchAll();

			$test->resetSubtests();
			foreach($subtestsDb as $subtestDb){
				$subtest = new Subtest();
				$subtest->setName($subtestDb["name"]);
				$subtest->setFullname($subtestDb["name"] . ":" . $subtestDb["test_name"] . ":" . $subtestDb["project_id"]);
				$subtest->setWeight($subtestDb["weight"]);
				$subtest->setKind($subtestDb["kind"]);
				$test->addSubtest($subtest);
			}
			return $test;
		}

		public function getAllTestNames()
		{
			$sth = $this->execute("SELECT test.name FROM test");
			$namesDb = $sth->fetchAll();

			$namesArray = array();
			foreach($namesDb as $nameDb)
			{
				array_push($namesArray, $nameDb["name"]);
			}
			return $namesArray;
		}
        
        public function deleteTest($projectId, $testName)
		{
			$params = array("projectId" => $projectId, "testName" => $testName);
            var_dump($params);
			$sth = $this->execute("DELETE FROM users_test WHERE users_test.project_id = :projectId AND users_test.test_name = :testName", $params);
			$sth = $this->execute("DELETE FROM subtest WHERE subtest.project_id = :projectId AND subtest.test_name = :testName", $params);
			$sth = $this->execute("DELETE FROM test WHERE test.project_id = :projectId AND test.name = :testName", $params);
            var_dump($sth->errorInfo());
		}
	}