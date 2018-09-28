<?php

namespace calypte\pcalypte;

use calypte\pcalypte\CalypteSender;
use calypte\pcalypte\CalypteReceiver;
use calypte\pcalypte\CacheException;
use Exception;

/**
 * Permite o armazenamento, atualização, remoção de um item em um servidor Calypte.
 *
 * @author Brandao.
 */
class CalypteConnection{
	
	public static $CRLF                  = "\r\n";
	
	public static $DEFAULT_FLAGS         = "0";
	
	public static $SHOW_VAR              = "show_var";
	
	public static $SET_VAR               = "set_var";
	
	public static $SHOW_VARS             = "show_vars";
	
	public static $BOUNDARY              = "end";
	
	public static $PUT_COMMAND           = "put";
	
	public static $REPLACE_COMMAND       = "replace";
	
	public static $SET_COMMAND           = "set";
	
	public static $GET_COMMAND           = "get";
	
	public static $REMOVE_COMMAND        = "remove";
	
	public static $BEGIN_TX_COMMAND      = "begin";
	
	public static $COMMIT_TX_COMMAND     = "commit";
	
	public static $ROLLBACK_TX_COMMAND   = "rollback";
	
	public static $ERROR                 = "error";
	
	public static $VALUE_RESULT          = "value";
	
	public static $SUCCESS               = "ok";
	
	public static $PUT_SUCCESS           = "stored";
	
	public static $REPLACE_SUCCESS       = "replaced";
	
	public static $NOT_STORED            = "not_stored";
	
	public static $NOT_FOUND             = "not_found";
	
	public static $SEPARATOR_COMMAND     = " ";
		
	private $host;
	
	private $port;
	
	private $pointer;

	private $sender;
	
	private $receiver;
	
	private $pcon;
	
	function __construct($host = "localhost", $port = 1044, $pcon = false){
		$this->host      = $host;
		$this->port      = $port;
		$this->pcon      = $pcon;
		$this->pointer   = $pcon? pfsockopen($this->host, $port) : fsockopen($this->host, $port);
		$this->sender    = new CalypteSender();  
		$this->receiver  = new CalypteReceiver();
	}
	
	/**
	 * Fecha a conexão com o servidor.
	 *
	 * @throws CacheException Lançada caso ocorra alguma falha ao tentar fechar a conexão com o servidor.
	 */
	public function close(){
		if($this->pcon){
			throw new CacheException("can't close this connection");
		}
		fclose($this->pointer);
	}
	
	/**
	 * Verifica se a conexão está fechada.
	 * @return <code>true</code> se a conexão está fechada. Caso contrátio, <code>false</code>.
	 */
	public function isClosed(){
		return !is_resource($this->pointer);
	}
	
	/* métodos de coleta*/
	
	/**
	 * Substitui o valor associado à chave somente se ele existir.
	 * @param key chave associada ao valor.
	 * @param value valor para ser associado à chave.
	 * @param timeToLive é a quantidade máxima de tempo que um item expira após sua criação.
	 * @param timeToIdle é a quantidade máxima de tempo que um item expira após o último acesso.
	 * @return <code>true</code> se o valor for substituido. Caso contrário, <code>false</code>.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor.
	 */
	public function replace(
			$key, $value, $timeToLive = 0, $timeToIdle = 0){
		try{
			$this->sender->replace($this->pointer, $key, $value, $timeToLive, $timeToIdle);
			return $this->receiver->processReplaceResult($this->pointer);
		}
		catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
	}
	
	/**
	 * Substitui o valor associado à chave somente se ele for igual a um determinado valor.
	 * @param key chave associada ao valor.
	 * @param oldValue valor esperado associado à chave.
	 * @param newValue valor para ser associado à chave.
	 * @param cmp função que faz a comparação entre o valor atual e o novo valor.
	 * @param timeToLive é a quantidade máxima de tempo que um item expira após sua criação.
	 * @param timeToIdle é a quantidade máxima de tempo que um item expira após o último acesso.
	 * @return <code>true</code> se o valor for substituido. Caso contrário, <code>false</code>.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor.
	 */
	public function replaceValue(
			$key, $oldValue,
			$newValue, $cmp, $timeToLive = 0, $timeToIdle = 0){
		
		if(!is_callable($cmp)){
			throw new CacheException("cmp is not a valid function", 0, null);
		}
		
		$localTransaction = null;
		
		try{
			$localTransaction = $this->startLocalTransaction();
			$o = $this->get($key, true);

			if($o != null && $cmp($o, $oldValue)){
				$result = $this->put($key, $newValue, $timeToLive, $timeToIdle);
			}
			else{
				$result = false;
			}
				
			$this->commitLocalTransaction($localTransaction);
			return $result;
		}
		catch(Exception $e){
			throw $this->rollbackLocalTransaction($localTransaction, $e);
		}
		
	}
	
	/**
	 * Associa o valor à chave somente se a chave não estiver associada a um valor.
	 * @param key chave associada ao valor.
	 * @param value valor para ser associado à chave.
	 * @param timeToLive é a quantidade máxima de tempo que um item expira após sua criação.
	 * @param timeToIdle é a quantidade máxima de tempo que um item expira após o último acesso.
	 * @return object valor anterior associado à chave.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor.
	 */
	public function putIfAbsent(
			$key, $value, $timeToLive = 0, $timeToIdle = 0){

		$localTransaction = null;
		
		try{
			$localTransaction = $this->startLocalTransaction();
			$o = $this->get($key, true);
			$this->set($key, $value, $timeToLive, $timeToIdle);
			$this->commitLocalTransaction($localTransaction);
			return $o;
		}
		catch(Exception $e){
			throw $this->rollbackLocalTransaction($localTransaction, $e);
		}
		
	}

	/**
	 * Associa o valor à chave.
	 * @param key chave associada ao valor.
	 * @param value valor para ser associado à chave.
	 * @param timeToLive é a quantidade máxima de tempo que um item expira após sua criação.
	 * @param timeToIdle é a quantidade máxima de tempo que um item expira após o último acesso.
	 * @return <code>true</code> se o item for substituido. Caso contrário, <code>false</code>
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor.
	 */
	public function put($key, $value, $timeToLive = 0, $timeToIdle = 0){
	
		try{
			$this->sender->put($this->pointer, $key, $value, $timeToLive, $timeToIdle);
			return $this->receiver->processPutResult($this->pointer);
		}
		catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
		
	}
	
	/**
	 * Associa o valor à chave somente se a chave não estiver associada a um valor.
	 * @param key chave associada ao valor.
	 * @param value valor para ser associado à chave.
	 * @param timeToLive é a quantidade máxima de tempo que um item expira após sua criação.
	 * @param timeToIdle é a quantidade máxima de tempo que um item expira após o último acesso.
	 * @return <code>true</code> se o valor for substituído. Caso contrário, <code>false</code>.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor.
	 */
	public function set(
			$key, $value, $timeToLive = 0, $timeToIdle = 0){
		
		try{
			$this->sender->set($this->pointer, $key, $value, $timeToLive, $timeToIdle);
			return $this->receiver->processSetResult($this->pointer);
		}
			catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
				
	}
	
	/**
	 * Obtém o valor associado à chave bloqueando ou não
	 * seu acesso as demais transações.
	 * @param key chave associada ao valor.
	 * @param forUpdate <code>true</code> para bloquear o item. Caso contrário <code>false</code>.
	 * @return object valor associado à chave ou <code>null</code>.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor.
	 */
	public function get($key, $forUpdate = false){
		
		try{
			$this->sender->get($this->pointer, $key, $forUpdate);
			return $this->receiver->processGetResult($this->pointer);
		}
			catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
				
	}
	
	/* métodos de remoção */
	
	/**
	 * Remove o valor associado à chave.
	 * @param key chave associada ao valor.
	 * @return <code>true</code> se o valor for removido. Caso contrário, <code>false</code>.
     * @throws CacheException Lançada se ocorrer alguma falha com o servidor.
	 */
	public function remove($key){
		
    	try{
	    	$this->sender->remove($this->pointer, $key);
	        return $this->receiver->processRemoveResult($this->pointer);
    	}
		catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
    							
	}

	/**
	 * Remove o valor assoiado à chave somente se ele for igual a um determinado valor.
	 * @param key chave associada ao valor.
	 * @param value valor associado à chave.
	 * @param cmp função que faz a comparação do valor com o que será removido.
	 * @return <code>true</code> se o valor for removido. Caso contrário, <code>false</code>.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor.
	 */
	public function removeValue($key, $value, $cmp){
	
		$localTransaction = null;
	
		try{
			$localTransaction = $this->startLocalTransaction();
			$o = $this->get($key, true);
			if($o != null && $cmp($o,$value)){
				$result = $this->remove($key);
			}
			else
				$result = false;
	
			$this->commitLocalTransaction($localTransaction);
			return $result;
		}
		catch(Exception $e){
			throw $this->rollbackLocalTransaction($localTransaction, $e);
		}
	
	}
	
	/**
	 * Define o modo de confirmação automática. Se o modo de confirmação automática
	 * estiver ligado, todas as operações serão tratadas como transações individuais. Caso contrário,
	 * as operações serão agrupadas em uma transação que deve ser confirmada com o método {@link CalypteConnection::commit()} ou
	 * descartadas com o método {@link CalypteConnection::rollback()}. Por padrão, cada nova conexão inicia com o
	 * modo de confirmação automática ligada.
	 * @param value <code>true</code> para ligar o modo de confirmação automática. Caso contrário, <code>false</code>.
	 * @throws CacheException Lançada se o estado desejado já estiver em vigor ou se a conexão estiver fechada.
	 */
	public function setAutoCommit($value){
		
		try{
			$this->sender->setVar($this->pointer, "auto_commit", $value? "true" : "false");
			$this->receiver->processSetVarResult($this->pointer);
		}
		catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
				
	}
	
	/**
	 * Obtém o estado atual do modo de confirmação automática.
	 * @return <code>true</code> se o modo de confirmação automática estiver ligado. Caso contrário, <code>false</code>.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor ou se a conexão estiver fechada.
	 */
	public function isAutoCommit(){
		try{
			$this->sender->showVar($this->pointer, "auto_commit");
			$var = $this->receiver->processShowVarResult($this->pointer, "auto_commit");
			return strcmp($var,"true") == 0;
		}
		catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
			}
	
	/**
	 * Confirma todas as operações da transação atual e libera todos os bloqueios detidos por essa conexão.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor, se a conexão estiver fechada ou se o
	 * modo de confirmação automática estiver ligada.
	 */
	public function commit(){
		try{
			$this->sender->commitTransaction($this->pointer);
			$this->receiver->processCommitTransactionResult($this->pointer);
		}
		catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
	}
	
	/**
	 * Desfaz todas as operações da transação atual e libera todos os bloqueios detidos por essa conexão.
	 * @throws CacheException Lançada se ocorrer alguma falha com o servidor, se a conexão estiver fechada ou se o
	 * modo de confirmação automática estiver ligada.
	 */
	public function rollback(){
    	try{
    		$this->sender->rollbackTransaction($this->pointer);
    		$this->receiver->processRollbackTransactionResult($this->pointer);
    	}
		catch(CacheException $e){
			throw $e;
		}
		catch(Exception $e){
			throw new CacheException(null, null, $e);
		}
	}
	
	/**
	 * Obtém o endereço do servidor.
	 * @return string Endereço do servidor.
	 */
	public function getHost(){
		return $this->host;
	}
	
	/**
	 * Obtém a porta do servidor.
	 * @return int Porta do servidor.
	 */
	public function getPort(){
		return $this->port;
	}
	
	private function startLocalTransaction() {
		if($this->isAutoCommit()){
			$this->setAutoCommit(false);
			return true;
		}
		 
		return false;
	}
	
	private function commitLocalTransaction($local){
		if($local != null && $local){
			$this->commit();
		}
	}
	
	private function rollbackLocalTransaction($local, $e){
		try{
			if($local != null && $local){
				$this->rollback();
			}
		}
		catch(CacheException $ex){
			return ex;
		}
		catch(Exception $ex){
			return new CacheException("rollback fail: " + ex.toString(), 0, e);
		}
	
		if(is_a($e, "CacheException")){
			return $e;
		}
		else{
			return new CacheException(null, null, $e);
		}
	}	
}