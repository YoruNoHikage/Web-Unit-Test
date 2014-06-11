<?php
	abstract class Model{

		private static $db;

		private static function getDb(){
			if(self::$db === null){
                $conf = parse_ini_file('conf.ini');
				
                self::$db = new PDO("mysql:host=" . $conf['host'] .
                                    ";dbname=" . $conf['dbname'] . ";charset=utf8",
                                    $conf['user'],
                                    $conf['password']);
			}
			return self::$db;
		}

		protected function execute($sql, $params = null){
			if($params == null){
				$results = self::getDb()->query($sql);
			}else{
				$results = self::getDb()->prepare($sql);
				$results->execute($params);
				//$results->debugDumpParams();
			}
			return $results;
		}

	}