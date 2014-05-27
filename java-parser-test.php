<?php
	if(isset($_REQUEST['className']))
	{
		if(isset($_REQUEST['className']) != '')
		{
			$url = 'test/' . $_REQUEST['className'] . '.java';
			$file = fopen($url, 'r');
			$content = fread($file, filesize($url));
			fclose($file);

			preg_match_all('#@Test([\t\n\r\s])+public void (.*?)\(#', $content, $matches);
			$testFunc = $matches[2];

			var_dump($testFunc);
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Upload Test</title>
	</head>
	<body>
		<form enctype="multipart/form-data" action="java-parser-test.php" method="post">
		  	<input type="text" name="className" id="className" placeholde="MaClasseDeTest" />
		  	<input type="submit" value="Parser les tests de la classe"/>
		</form>		
	</body>
</html>