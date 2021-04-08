<?php namespace magic3w\http\url\reflection;

use PHPUnit\Framework\TestCase;

class HTTPParseTest extends TestCase
{

	public function testParse() {
		$meta = URLReflection::fromURL('http://magic3w.com/about');
		$this->assertEquals('magic3w.com', $meta->getServer());
		$this->assertEquals('/about', $meta->getPath());
		$this->assertEquals('http', $meta->getProtocol());
	}
	
	public function testParseWeirdQueryString() 
	{
		$meta = URLReflection::fromURL('http://magic3w.com/about?thisisaquerystringwithnoequals%20oranything');
		$this->assertEquals('magic3w.com', $meta->getHostname());
		$this->assertEquals('/about', $meta->getPath());
		$this->assertEquals('http', $meta->getProtocol());
		$this->assertArrayHasKey('thisisaquerystringwithnoequals_oranything', $meta->getQueryString());
	}
}
