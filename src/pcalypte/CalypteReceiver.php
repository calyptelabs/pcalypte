<?php

namespace calypte\pcalypte;

use stdClass;
use calypte\pcalypte\CacheException;

class CalypteReceiver{
	
	function __construct(){
	}
	
	public function processPutResult($con){
		
		$resp = $this->readLine($con);
		
		switch ($resp[0]) {
			case 's':
				return false;
			case 'r':
				return true;
			default:
				$error = $this->parseError($resp);
				throw new CacheException($error->message, $error->code);
		}
				
	}

	public function processReplaceResult($con){
	
		$resp = $this->readLine($con);
	
		switch ($resp[0]) {
			case 'r':
				return true;
			case 'n':
				return false;
			default:
				$error = $this->parseError($resp);
				throw new CacheException($error->message, $error->code);
		}
	
	}

	public function processGetResult($con){
	
		$resp = $this->readLine($con);
	
		if($resp[0] == 'v'){
			$entry = $this->getObject($con, $resp);
			
			$boundary = $this->readLine($con);
				
			if(strcmp($boundary, CalypteConnection::$BOUNDARY) != 0){
				throw new CacheException("expected end");
			}
				
			return $entry == null? null : unserialize($entry->dta);
		}
		else{
			$error = $this->parseError($resp);
			throw new CacheException($error->message, $error->code);
		}
	
	}

	public function processMultiGetResult($con){
	
		$resp   = $this->readLine($con);
		$result = Array();
		
		while($resp[0] == 'v'){
			$entry = $this->getObject($con, $resp);
			
			if($entry != null){
				$result[$key] = unserialize($entry->dta);
			}
			
			$resp   = $this->readLine($con);
		}
		
		if(!strcmp($resp, CalypteConnection::$BOUNDARY)){
			$error = $this->parseError($resp);
			throw new CacheException($error->message, $error->code);
		}
		
		return $result;
	}
	
	public function processRemoveResult($con){
	
		$resp = $this->readLine($con);
	
		switch ($resp[0]) {
			case 'o':
				return true;
			case 'n':
				return false;
			default:
				$error = $this->parseError($resp);
				throw new CacheException($error->message, $error->code);
		}
	
	}

	public function processSetResult($con){
	
		$resp = $this->readLine($con);
	
		switch ($resp[0]) {
			case 's':
				return true;
			case 'n':
				return false;
			default:
				$error = $this->parseError($resp);
				throw new CacheException($error->message, $error->code);
		}
	
	}

	public function processBeginTransactionResult($con){
		$this->processDefaultTransactionCommandResult($con);
	}
	
	public function processCommitTransactionResult($con){
		$this->processDefaultTransactionCommandResult($con);
	}
	
	public function processRollbackTransactionResult($con){
		$this->processDefaultTransactionCommandResult($con);
	}
	
	public function processDefaultTransactionCommandResult($con){
	
		$resp = $this->readLine($con);
	
		if($resp[0] != 'o'){
			$error = $this->parseError($resp);
			throw new CacheException($error->message, $error->code);
		}
		
	}

	public function processShowVarResult($con, $var){
	
		$resp = $this->readLine($con);
	
		$expectedPrefix = $var . ": ";
		$prefixLen = strlen($expectedPrefix);
		
		$prefix = substr($resp, 0, strlen($expectedPrefix));
		
		if(strcmp($expectedPrefix, $prefix) == 0){
			$value = substr($resp, strlen($expectedPrefix), strlen($resp));
			return $value;
		}
		else{
			$error = $this->parseError($resp);
			throw new CacheException($error->message, $error->code);
		}
	
	}

	public function processSetVarResult($con){
	
		$resp = $this->readLine($con);
	
		if($resp[0] != 'o'){
			$error = $this->parseError($resp);
			throw new CacheException($error->message, $error->code);
		}
			
	}
	
	private function getObject($con, $header){
		
		$params = explode(CalypteConnection::$SEPARATOR_COMMAND, $header);
		
		$key   = $params[1];
		$size  = intval($params[2]);
		$flags = intval($params[3]);
		
		if($size > 0){
			$buf = fread($con, $size + 2);
			//$end = fread($con, 2);
			
			//if(!strcmp($end, CalypteConnection::$CRLF)){
			//	throw new CacheException("corrupted data: " . $key);
			//}
			
			$entry = new stdClass();
			$entry->key   = $key;
			$entry->size  = $size;
			$entry->flags = $flags;
			$entry->dta   = $buf;
			return $entry;
		}
		else{
			return null;
		}
	}
	
	private function readLine($con){
		$lin = fgets($con);
		$lin = str_replace("\r\n", "", $lin);
		//error_log("receive: " . $lin);
		return $lin;
	}
	
	private function parseError($resp){
		$code    = substr($resp, 6, 4);
		$message = substr($resp, 12, strlen($resp) - 12);
		
		$error = new stdClass();
		$error->code    = intval($code);
		$error->message = $message;
		return $error;
	}
	
}