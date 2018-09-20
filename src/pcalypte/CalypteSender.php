<?php

namespace calypte\pcalypte;

use calypte\pcalypte\CalypteConnection;


class CalypteSender{
	
	function __construct(){
	}
	
	public function put($con, $key, $value, $timeToLive, $timeToIdle){
		
		$data = serialize($value);
		
		$header =
			CalypteConnection::$PUT_COMMAND . CalypteConnection::$SEPARATOR_COMMAND .
			$key . CalypteConnection::$SEPARATOR_COMMAND . 
			$timeToLive . CalypteConnection::$SEPARATOR_COMMAND .
			$timeToIdle . CalypteConnection::$SEPARATOR_COMMAND .
			strlen($data) . CalypteConnection::$SEPARATOR_COMMAND .
			CalypteConnection::$DEFAULT_FLAGS . CalypteConnection::$CRLF;
		
		$this->send($con, $header);
		$this->send($con, $data);
		$this->send($con, CalypteConnection::$CRLF);
	}

	public function replace($con, $key, $value, $timeToLive, $timeToIdle){
	
		$data = serialize($value);
	
		$header =
			CalypteConnection::$REPLACE_COMMAND . CalypteConnection::$SEPARATOR_COMMAND .
			$key . CalypteConnection::$SEPARATOR_COMMAND .
			$timeToLive . CalypteConnection::$SEPARATOR_COMMAND .
			$timeToIdle . CalypteConnection::$SEPARATOR_COMMAND .
			strlen($data) . CalypteConnection::$SEPARATOR_COMMAND .
			CalypteConnection::$DEFAULT_FLAGS . CalypteConnection::$CRLF;
			
		$this->send($con, $header);
		$this->send($con, $data);
		$this->send($con, CalypteConnection::$CRLF);
	}

	public function set($con, $key, $value, $timeToLive, $timeToIdle){
	
		$data = serialize($value);
	
		$header =
			CalypteConnection::$SET_COMMAND . CalypteConnection::$SEPARATOR_COMMAND .
			$key . CalypteConnection::$SEPARATOR_COMMAND .
			$timeToLive . CalypteConnection::$SEPARATOR_COMMAND .
			$timeToIdle . CalypteConnection::$SEPARATOR_COMMAND .
			strlen($data) . CalypteConnection::$SEPARATOR_COMMAND .
			CalypteConnection::$DEFAULT_FLAGS . CalypteConnection::$CRLF;
			
		$this->send($con, $header);
		$this->send($con, $data);
		$this->send($con, CalypteConnection::$CRLF);
	}

	public function get($con, $key, $forUpdate){
	
		$header =
			CalypteConnection::$GET_COMMAND . CalypteConnection::$SEPARATOR_COMMAND .
			$key . CalypteConnection::$SEPARATOR_COMMAND .
			($forUpdate? "1" : "0") . CalypteConnection::$SEPARATOR_COMMAND .
			CalypteConnection::$DEFAULT_FLAGS . CalypteConnection::$CRLF;
			
		$this->send($con, $header);
	}

	public function remove($con, $key){
	
		$header =
			CalypteConnection::$REMOVE_COMMAND . CalypteConnection::$SEPARATOR_COMMAND .
			$key . CalypteConnection::$SEPARATOR_COMMAND .
			CalypteConnection::$DEFAULT_FLAGS . CalypteConnection::$CRLF;
			
		$this->send($con, $header);
	}

	public function beginTransaction($con){
	
		$header =
			CalypteConnection::$BEGIN_TX_COMMAND . CalypteConnection::$CRLF;
			
		$this->send($con, $header);
	}

	public function commitTransaction($con){
	
		$header =
			CalypteConnection::$COMMIT_TX_COMMAND . CalypteConnection::$CRLF;
			
		$this->send($con, $header);
	}

	public function rollbackTransaction($con){
	
		$header =
			CalypteConnection::$ROLLBACK_TX_COMMAND . CalypteConnection::$CRLF;
			
		$this->send($con, $header);
	}

	public function showVar($con, $var){
	
		$header =
			CalypteConnection::$SHOW_VAR . CalypteConnection::$SEPARATOR_COMMAND .
			$var . CalypteConnection::$CRLF;
		
		$this->send($con, $header);
	}

	public function showVars($con){
	
		$header = CalypteConnection::$SHOW_VARS . CalypteConnection::$CRLF;
		$this->send($con, $header);
	}
	
	public function setVar($con, $var, $value){
	
		$header =
			CalypteConnection::$SET_VAR . CalypteConnection::$SEPARATOR_COMMAND .
			$var . CalypteConnection::$SEPARATOR_COMMAND .
			$value . CalypteConnection::$CRLF;
		
		$this->send($con, $header);
	}
	
	private function send($con, $value){
		fwrite($con, $value);
		//error_log("send: " . str_replace("\r\n", "", $value));
	}
	
}