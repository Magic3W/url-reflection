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
 * This class allows to retrieve URL metadata in a organized manner. This 
 * should make the transfer to environment based database settings much easier.
 * 
 * While this is a very small component, it is a piece of code that I've found
 * myself recycling very often.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class URLReflection
{
	
	/**
	 * The protocol of the URI, this will usually be HTTP or HTTPS but can also
	 * be anything like tel://, ftp:// or mailto://
	 * 
	 * The strong does not contain the schema separator (: or ://)
	 * 
	 * @var string
	 */
	private $protocol;

	/**
	 * The name of the server.
	 * 
	 * @var string
	 */
	private $hostname;

	/**
	 * The port the protocol is residing on.
	 * 
	 * @var int
	 */
	private $port;

	/**
	 * In case the URL contains authentication information, this
	 * will contain the username.
	 * 
	 * @var string
	 */
	private $user;

	/**
	 * In case the URL contains authentication information, this
	 * will contain the username.
	 * 
	 * @var string
	 */
	private $password;

	/**
	 * The path requested. Please note that the path will include the 
	 * leading and ending slashes. If needed, you will have to trim these
	 * separately.
	 * 
	 * @var string
	 */
	private $path;

	/**
	 * Information extrracted from the query string. The query string is parsed 
	 * the way PHP would parse a _GET array, this means that arrays may be included.
	 * 
	 * @var mixed[]
	 */
	private $queryString;
	
	/**
	 * Populate the defaults. Some URLs may be delivered with implicit data (like //google.com,
	 * which is a valid URL and implies that the protocol is known)
	 * 
	 * @var mixed[]
	 */
	private static $defaults = [
		'scheme'   => 'https',
		'host'     => 'localhost',
		'port'     => null,
		'user'     => '',
		'pass'     => '',
		'path'     => '/',
		'query'    => '',
		'fragment' => ''
	];
	
	public function __construct($protocol, $hostname, $port, $user, $password, $path, $querystr) {
		$this->protocol = $protocol;
		$this->hostname = $hostname;
		$this->port   = $port === null? ($protocol === 'http'? 80 : 443) : $port;
		$this->user = $user;
		$this->password = $password;
		$this->path = $path;
		
		parse_str($querystr, $query);
		$this->queryString = $query;
	}
	
	public function getProtocol() {
		return $this->protocol;
	}
	
	public function getQueryString() {
		return $this->queryString;
	}
	
	public function setProtocol($protocol) {
		$this->protocol = $protocol;
		return $this;
	}
	
	public function setQueryString($queryString) {
		$this->queryString = $queryString;
		return $this;
	}
		
	public function getServer() {
		return $this->hostname;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function setServer($hostname) {
		$this->hostname = $hostname;
		return $this;
	}
	
	public function setPort($port) {
		$this->port = $port;
		return $this;
	}
	
	public function setUser($user) {
		$this->user = $user;
		return $this;
	}
	
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}
	
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * Creates a copy of this URL reflection without including the credentials.
	 * 
	 * I personally find this extremely useful for URLs that include API credentials, like
	 * https://appid:appsecret@ssoserver. These URLs are then used by the application without
	 * the secret and appid embedded like this.
	 * 
	 * Instead, our application can do something like this:
	 * <code>
	 * $appid = $reflection->getUsername();
	 * $secret = $reflection->getPassword();
	 * $endpoint = $reflection->stripCredentials();
	 * </code>
	 * 
	 * @return URLReflection
	 */
	public function stripCredentials() {
		return new self($this->protocol, $this->hostname, $this->port, false, false, $this->path, http_build_query($this->queryString));
	}
	
	/**
	 * Reads the settings from a URL. 
	 * 
	 * @todo Move to external URL parser
	 * @param URLReflection|string $url
	 * @return URLReflection
	 */
	public static function fromURL($url) : URLReflection {
		
		/*
		 * If the parameter provided is already a settings object, it will be 
		 * returned as is.
		 */
		if ($url instanceof self) { return $url; }
		
		return self::fromArray(parse_url($url));
	}
	
	public static function fromArray($arr) {
		$ops = $arr + self::$defaults;
		
		return new self(
			$ops['scheme'], 
			$ops['host'], 
			$ops['port'], 
			$ops['user'], 
			$ops['pass'], 
			$ops['path'], 
			$ops['query'],
			$ops['fragment']
		);
	}

	/**
	 * Converts the reflection to a URL. This prevents the application from having
	 * to treat these differently from how a regular URL would be treated.
	 * 
	 * @return string
	 */
	public function __toString() {

		if ($this->password && !$this->user) {
			throw new \Exception('URL format error', 2010301458);
		}

		$protocol = $this->protocol?? 'http';
		$credentials = implode(':', array_filter([$this->user, $this->password]));
		$hostname = $this->hostname;
		$path = $this->path;
		$query = $this->queryString? sprintf('?%s', http_build_query($this->queryString)) : '';

		$t = sprintf(
			'%s://%s%s%s%s', 
			$protocol,
			$credentials? $credentials . '@' : '',
			$hostname,
			$path,
			$query
		);
		
		return $t;
	}
	
}
