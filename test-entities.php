<?php
	require_once 'Entity/Group.php';
	require_once 'Entity/Project.php';
	require_once 'Entity/Result.php';
	require_once 'Entity/Role.php';
	require_once 'Entity/Subtest.php';
	require_once 'Entity/Test.php';
	require_once 'Entity/User.php';
	require_once 'Model/UserModel.php';
	require_once 'Model/ProjectModel.php';
	require_once 'Model/TestModel.php';

	/*$userModel = new UserModel();
	$users = $userModel->getAllUsers();

	foreach ($users as $user) {
		$userModel->getUserRole($user);
		$userModel->getUserGroups($user);
		$userModel->getUserProjects($user);

		echo $user->toString() . "<br/>";
	}*/

	//pas de *
	//verification de non existence avant 1 add

	$projectModel = new ProjectModel();
	$testModel = new TestModel();
	$projects = $projectModel->getAllProjects();

	foreach ($projects as $project) {
		$tests = $projectModel->getProjectTests($project)->getTests();
		foreach($tests as $test){
			$testModel->getTestSubtests($test);
		}
		//echo $project->getTests()[0]->getSubtests()[0]->toString();
		echo $project->toString() . "<br/>";
	}
