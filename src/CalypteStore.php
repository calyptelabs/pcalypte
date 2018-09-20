<?php

namespace calypte;

use Exception;
use calypte\pcalypte\CalypteConnection;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Cache\TaggableStore;

class CalypteStore extends TaggableStore implements Store {

	/**
	 *
	 * @var calypte\pcalypte\CalypteConnection
	 */
	protected $calypte;

	protected $prefix;

	/**
	 *
	 * @param calypte\pcalypte\CalypteConnection $calypte
	 * @param string $prefix
	 */
	public function __construct($calypte, $prefix = ''){
		$this->setPrefix($prefix);
		$this->calypte = $calypte;
	}

	/**
	 * Retrieve an item from the cache by key.
	 *
	 * @param  string|array  $key
	 * @return mixed
	 */
	public function get($key){
		return $this->calypte->get($key);
	}

	/**
	 * Retrieve multiple items from the cache by key.
	 *
	 * Items not found in the cache will have a null value.
	 *
	 * @param  array  $keys
	 * @return array
	 */
	public function many(array $keys){
		//versão 1.0 não suporta multi
		$prefixedKeys =
		array_map(
				function ($key) {
					return $this->prefix.$key;
				},
				$keys
				);

			
		$values = array();
			
		foreach($prefixedKeys as $key){
			$value = $this->get($key);
			$values[$key] = $value;
		}

		return $values;
	}

	/**
	 * Store an item in the cache for a given number of minutes.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  float|int  $minutes
	 * @return void
	 */
	public function put($key, $value, $minutes){
		$this->calypte->put($key, $value, $this->toTime($minutes));
	}

	/**
	 * Store multiple items in the cache for a given number of minutes.
	 *
	 * @param  array  $values
	 * @param  float|int  $minutes
	 * @return void
	 */
	public function putMany(array $values, $minutes){

		$time = $this->toTime($minutes);

		foreach ($values as $key => $value) {
			$this->calypte->put($this->prefix.$key, $value, $time);
		}

	}

	/**
	 * Store an item in the cache if the key doesn't exist.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  float|int  $minutes
	 * @return bool
	 */
	public function add($key, $value, $minutes){
		return $this->calypte->putIfAbsent($this->prefix.$key, $value, $this->toTime($minutes));
	}

	/**
	 * Increment the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return int|bool
	 */
	public function increment($key, $value = 1){
		//versão 1.0 não suporta increment
		$this->calypte->setAutoCommit(false);
		try{
			$v = $this->calypte->get($key);
			if(empty($v)){
				$v = $value;
			}
			else{
				$v += $value;
			}
			$v = $this->calypte->put($key, $v);
			$this->calypte->commit();
		}
		catch(\Exception $e){
			$this->calypte->rollback();
			throw $e;
		}
			
		return $v;
	}

	/**
	 * Decrement the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return int|bool
	 */
	public function decrement($key, $value = 1){
		//versão 1.0 não suporta decrement
		$this->calypte->setAutoCommit(false);
		try{
			$v = $this->calypte->get($key);
			if(empty($v)){
				$v = -$value;
			}
			else{
				$v -= $value;
			}
			$v = $this->calypte->put($key, $v);
			$this->calypte->commit();
		}
		catch(\Exception $e){
			$this->calypte->rollback();
			throw $e;
		}

		return $v;
	}

	/**
	 * Store an item in the cache indefinitely.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function forever($key, $value){
		$this->put($key, $value, 0);
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function forget($key){
		return $this->calypte->remove($this->prefix.$key);
	}

	/**
	 * Remove all items from the cache.
	 *
	 * @return bool
	 */
	public function flush(){
		return $this->calypte->flush();
	}

	/**
	 * Get the UNIX timestamp for the given number of minutes.
	 *
	 * @param  int  $minutes
	 * @return int
	 */
	protected function toTime($minutes){
		return $minutes > 0 ? $minutes*60*1000 : 0;
	}

	/**
	 * Get the underlying Calypte connection.
	 *
	 * @return \CalypteConnection
	 */
	public function getConnection(){
		return $this->calypte;
	}

	/**
	 * Get the cache key prefix.
	 *
	 * @return string
	 */
	public function getPrefix(){
		return $this->prefix;
	}

	/**
	 * Set the cache key prefix.
	 *
	 * @param  string  $prefix
	 * @return void
	 */
	public function setPrefix($prefix){
		$this->prefix = ! empty($prefix) ? $prefix.':' : '';
	}
}
