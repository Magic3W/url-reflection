<?php namespace magic3w\http\url\reflection;

/* 
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Use this class to generate a relative URL. This can then be applied to a
 * reflection, generating a new absolute URL that can be retrieved using cURL
 * or similar mechanisms.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class RelativeURL
{
	
	/**
	 * 
	 * @var string
	 */
	private $path;
	
	/**
	 * @var mixed[]
	 */
	private $query;
	
	/**
	 * 
	 * @param string $path
	 * @param mixed[] $query
	 */
	public function __construct(string $path, array $query)
	{
		$this->path = $path;
		$this->query = $query;
	}
	
	/**
	 * This method receives two paths (arrays of strings) and returns the existing
	 * path extended with the incoming path.
	 * 
	 * The incoming path must be a relative path (something like /hello/world or 
	 * ../world) that will be appended to the existing.
	 * 
	 * @param string[] $existing
	 * @param string[] $incoming
	 * @return string[]
	 */
	private function mergePaths(array $existing, array $incoming) : array
	{
		
		if (empty($incoming)) {
			return $existing;
		}
		
		#To generate a relative URL, we always assume that the last bit is not used.
		#This assumes that the URL was terminated with a / if the url was for a directory
		array_pop($existing);
		
		switch ($incoming[0]) {
			case '': 
				return $incoming;
			case '.':
				array_shift($incoming);
				return array_merge($existing, $incoming);
			case '..':
				array_shift($incoming);
				return $this->mergePaths($existing, $incoming);
			default: 
				return array_merge($existing, $incoming);
		}
	}
	
	/**
	 * 
	 * @param URLReflection $to
	 * @return URLReflection
	 */
	public function apply(URLReflection $to) : URLReflection
	{
		$pieces = $this->mergePaths(explode('/', $to->getPath()), $this->path? explode('/', $this->path) : []);
		$to->setPath(implode('/', $pieces));
		$to->setQueryString($this->query);
		return $to;
	}
	
	/**
	 * 
	 * @param string $str
	 * @return RelativeURL
	 */
	public static function fromString(string $str) : RelativeURL
	{
		$elements = parse_url($str);
		
		if ($elements === false) {
			return new self('', []);
		}
		
		parse_str($elements['query']?? '', $query);
		return new self($elements['path']?? '', $query);
	}
}
