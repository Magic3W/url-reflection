<?php namespace magic3w\http\url\reflection;

use PHPUnit\Framework\TestCase;

class QueryStringTest extends TestCase
{
	
	public function testParse()
	{
		$query = 'hello=world&test=1&test=2&hello%20world';
		$parsed = QueryString::parse($query);
		
		$this->assertEquals('world', $parsed['hello']);
		$this->assertEquals(null, $parsed['hello world']);
	}
	
	public function testParse2()
	{
		$query = 'hello=world+123';
		$parsed = QueryString::parse($query);
		
		$this->assertEquals('world+123', $parsed['hello']);
	}
	
	public function testParse3()
	{
		$query = 'hello=world&test[]=1&test[]=2&hello%20world';
		$parsed = QueryString::parse($query);
		
		$this->assertEquals([1,2], $parsed['test']);
	}
	
	public function testSymmetry()
	{
		$payload = [
			'hello' => 'world',
			'foo' => ['bar', 'baz'],
			'test' => ['success' => 'yes'],
			'impossible' => null
		];
		
		$encoded = QueryString::encode($payload);
		$decoded = QueryString::parse($encoded);
		
		$this->assertEquals($payload, $decoded);
	}
}
