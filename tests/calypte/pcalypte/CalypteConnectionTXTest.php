<?php

use calypte\pcalypte\CacheException;
use calypte\pcalypte\CalypteConnection;

require_once 'pcalypte/CalypteConnection.php';

class CalypteConnectionTXTest extends PHPUnit_Framework_TestCase{
	
	private $SERVER_HOST	= "localhost";
	
	private $SERVER_PORT	= 1044;
	
	private $KEY			= "teste";
	
	private $VALUE			= "value";
	
	private $VALUE2			= "val";
	
	protected function setUp(){
	}
	
	protected function tearDown(){
	}

	public function testCommitFail(){
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT);
		try{
			$con->commit();
			$this->fail("expected CacheException");
		}
		catch(CacheException $e){
		}
	}
	
	public function testCommit(){
		$prefixKEY = "testCommit:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT);
		$con->remove($prefixKEY . $this->KEY);
		$con->setAutoCommit(false);
		$this->assertFalse($con->put($prefixKEY . $this->KEY, $this->VALUE));
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$con->commit();
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}

	public function testRollbackFail(){
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT);
		try{
			$con->rollback();
			$this->fail("expected CacheException");
		}
		catch(CacheException $e){
		}
	}
	
	public function testRollback(){
		$prefixKEY = "testRollback:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT);
		$con->remove($prefixKEY . $this->KEY);
		$con->setAutoCommit(false);
		$this->assertFalse($con->put($prefixKEY . $this->KEY, $this->VALUE));
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$con->rollback();
		$this->assertNull($con->get($prefixKEY . $this->KEY));
	}
	
	public function testReplace(){

		$prefixKEY = "testReplace:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
		$this->assertFalse($con->replace($prefixKEY . $this->KEY, $this->VALUE, 0, 0));
	}
	
	public function testReplaceSuccess(){

		$prefixKEY = "testReplaceSuccess:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$this->assertTrue($con->replace($prefixKEY . $this->KEY, $this->VALUE2, 0, 0));
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
	}
	
	public function testReplaceExact(){

		$prefixKEY = "testReplaceExact:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$this->assertFalse($con->replaceValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			$this->VALUE2, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}, 0, 0));
	}
	
	public function testReplaceExactSuccess(){

		$prefixKEY = "testReplaceExactSuccess:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$this->assertTrue($con->replaceValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			$this->VALUE2, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}, 0, 0));
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
	}
	
	/* putIfAbsent */
	
	public function testputIfAbsent(){

		$prefixKEY = "testputIfAbsent:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$this->assertNull($con->putIfAbsent($prefixKEY . $this->KEY, $this->VALUE, 0, 0));
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}
	
	public function testputIfAbsentExistValue(){

		$prefixKEY = "testputIfAbsentExistValue:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->putIfAbsent($prefixKEY . $this->KEY, $this->VALUE2, 0, 0));
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}
	
	/* put */
	
	public function testPut(){

		$prefixKEY = "testPut:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}
	
	/* get */
	
	public function testGet(){

		$prefixKEY = "testGet:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}
	
	public function testGetOverride(){

		$prefixKEY = "testGetOverride:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$con->put($prefixKEY . $this->KEY, $this->VALUE2, 0, 0);
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
	}
	
	/* remove */
	
	public function testRemoveExact(){

		$prefixKEY = "testRemoveExact:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$this->assertFalse($con->removeValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}));
			
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
	
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	
		$this->assertFalse($con->removeValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE2, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}));
			
		$this->assertTrue($con->removeValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}));
			
		$this->assertNull($con->get($prefixKEY . $this->KEY));
	}
	
	public function testRemove(){

		$prefixKEY = "testRemove:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$this->assertFalse($con->remove($prefixKEY . $this->KEY));
	
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
	
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	
		$this->assertTrue($con->remove($prefixKEY . $this->KEY));
		$this->assertNull($con->get($prefixKEY . $this->KEY));
	}
	
	/* with explicit transaction */
	
	/* replace */
	
	public function testExplicitTransactionReplace(){

		$prefixKEY = "testExplicitTransactionReplace:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->setAutoCommit(false);
		$this->assertFalse($con->replace($prefixKEY . $this->KEY, $this->VALUE, 0, 0));
		$con->commit();
		$this->assertFalse($con->replace($prefixKEY . $this->KEY, $this->VALUE, 0, 0));
	}
	
	public function testExplicitTransactionReplaceSuccess(){

		$prefixKEY = "testExplicitTransactionReplaceSuccess:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->setAutoCommit(false);
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$this->assertTrue($con->replace($prefixKEY . $this->KEY, $this->VALUE2, 0, 0));
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
		$con->commit();
	
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
	}
	
	public function testExplicitTransactionReplaceExact(){

		$prefixKEY = "testExplicitTransactionReplaceExact:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->setAutoCommit(false);
		$this->assertFalse($con->replaceValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			$this->VALUE2, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}, 0, 0));
		$con->commit();
		$this->assertFalse($con->replaceValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			$this->VALUE2, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}, 0, 0));
	}
	
	public function testExplicitTransactionReplaceExactSuccess(){

		$prefixKEY = "testExplicitTransactionReplaceExactSuccess:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->setAutoCommit(false);
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		
		$this->assertTrue($con->replaceValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			$this->VALUE2, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}, 0, 0));
			
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
		$con->commit();
	
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
	}
	
	/* putIfAbsent */
	
	public function testExplicitTransactionPutIfAbsent(){

		$prefixKEY = "testExplicitTransactionPutIfAbsent:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$con->setAutoCommit(false);
		$this->assertNull($con->putIfAbsent($prefixKEY . $this->KEY, $this->VALUE, 0, 0));
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$con->commit();
	
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}
	
	public function testExplicitTransactionPutIfAbsentExistValue(){

		$prefixKEY = "testExplicitTransactionPutIfAbsentExist$this->VALUE:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->setAutoCommit(false);
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->putIfAbsent($prefixKEY . $this->KEY, $this->VALUE2, 0, 0));
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$con->commit();
	
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}
	
	/* put */
	
	public function testExplicitTransactionPut(){

		$prefixKEY = "testExplicitTransactionPut:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$con->setAutoCommit(false);
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$con->commit();
	
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}
	
	/* get */
	
	public function testExplicitTransactionGet(){

		$prefixKEY = "testExplicitTransactionGet:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$con->setAutoCommit(false);
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$con->commit();
	
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	}
	
	public function testExplicitTransactionGetOverride(){

		$prefixKEY = "testExplicitTransactionGetOverride:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->remove($prefixKEY . $this->KEY);
		$con->setAutoCommit(false);
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
		$con->put($prefixKEY . $this->KEY, $this->VALUE2, 0, 0);
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
		$con->commit();
	
		$this->assertEquals($this->VALUE2, $con->get($prefixKEY . $this->KEY));
	}
	
	/* remove */
	
	public function testExplicitTransactionRemoveExact(){

		$prefixKEY = "testExplicitTransactionRemoveExact:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->setAutoCommit(false);
	
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$this->assertFalse($con->removeValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}));
	
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
	
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	
		$this->assertFalse($con->removeValue(
			$prefixKEY . $this->KEY, 
			$this->VALUE2, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}));
			
		$this->assertTrue($con->remove(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}));
			
		$con->commit();
	
		$this->assertFalse($con->remove(
			$prefixKEY . $this->KEY, 
			$this->VALUE, 
			function($a, $b){
				return strcmp($a,$b) == 0;
			}));
	}
	
	public function testExplicitTransactionRemove(){

		$prefixKEY = "testExplicitTransactionRemove:";
		$con = new CalypteConnection($this->SERVER_HOST, $this->SERVER_PORT, false);
	
		$con->setAutoCommit(false);
	
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$this->assertFalse($con->remove($prefixKEY . $this->KEY));
	
		$con->put($prefixKEY . $this->KEY, $this->VALUE, 0, 0);
	
		$this->assertEquals($this->VALUE, $con->get($prefixKEY . $this->KEY));
	
		$this->assertTrue($con->remove($prefixKEY . $this->KEY));
		$this->assertNull($con->get($prefixKEY . $this->KEY));
		$con->commit();
	
		$this->assertNull($con->get($prefixKEY . $this->KEY));
	}
	
}
