<?php
	abstract class Model{

		private static $db;

		private static function getDb(){
			if(self::$db === null){
				self::$db = new PDO("mysql:host=localhost;dbname=projet_web;charset=utf8", "root", "");
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