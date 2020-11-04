<?php namespace magic3w\http\url\reflection;

use PHPUnit\Framework\TestCase;

class ToStringTest extends TestCase
{

	public function testWithoutCredentials() {
		$meta = URLReflection::fromURL('http://magic3w.com/about');
		$this->assertEquals('http://magic3w.com/about', strval($meta));
	}

	public function testWithCredentials() {
		$meta = URLReflection::fromURL('http://hello:world@magic3w.com/about');
		$this->assertEquals('http://hello:world@magic3w.com/about', strval($meta));
	}

	public function testWithStrippedCredentials() {
		$meta = URLReflection::fromURL('http://hello:world@magic3w.com/about');
		$meta = $meta->stripCredentials();
		$this->assertEquals('http://magic3w.com/about', strval($meta));
	}

	public function testWithoutProtocol() {
		$meta = URLReflection::fromURL('//magic3w.com/about');
		$this->assertEquals('https://magic3w.com/about', strval($meta));
	}
}