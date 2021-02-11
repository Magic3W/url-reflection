<?php namespace magic3w\http\url\reflection;

use PHPUnit\Framework\TestCase;

class RelativeURLTest extends TestCase
{
	
	public function testTwoDotsPath() 
	{
		$full = URLReflection::fromURL('https://magic3w.com/about/us/');
		$relative = RelativeURL::fromString('../me/');
		
		$this->assertEquals('https://magic3w.com/about/me/', strval($relative->apply($full)));
	}
	
	public function testOneDotPath() 
	{
		$full = URLReflection::fromURL('https://magic3w.com/about/us/');
		$relative = RelativeURL::fromString('./me/');
		
		$this->assertEquals('https://magic3w.com/about/us/me/', strval($relative->apply($full)));
	}
	
	public function testonlyAppend() 
	{
		$full = URLReflection::fromURL('https://magic3w.com/about/us');
		$relative = RelativeURL::fromString('me');
		
		$this->assertEquals('https://magic3w.com/about/me', strval($relative->apply($full)));
	}
	
	public function testFullPath() 
	{
		$full = URLReflection::fromURL('https://magic3w.com/about/us/');
		$relative = RelativeURL::fromString('/services/development/');
		
		$this->assertEquals('https://magic3w.com/services/development/', strval($relative->apply($full)));
	}
	
	public function testQueryOnly() 
	{
		$full = URLReflection::fromURL('https://magic3w.com/about/us/');
		$relative = RelativeURL::fromString('?section=team');
		
		$this->assertEquals('https://magic3w.com/about/us/?section=team', strval($relative->apply($full)));
	}
	
	public function testFullPathWithQuery() 
	{
		$full = URLReflection::fromURL('https://magic3w.com/about/us/');
		$relative = RelativeURL::fromString('/services/development/?section=team');
		
		$this->assertEquals('https://magic3w.com/services/development/?section=team', strval($relative->apply($full)));
	}
	
}
