<?php
	class Result{

		private $status;
		private $error;

		public function getStatus(){
			return $this->status;
		}

		public function setStatus($status){
			$this->status = $status;
		}

		public function getError(){
			return $this->error;
		}

		public function setError($error){
			$this->error = $error;
		}
	}