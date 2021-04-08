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
	private $user = '';

	/**
	 * In case the URL contains authentication information, this
	 * will contain the username.
	 * 
	 * @var string
	 */
	private $password = '';

	/**
	 * The path requested. Please note that the path will include the 
	 * leading and ending slashes. If needed, you will have to trim these
	 * separately.
	 * 
	 * @var string
	 */
	private $path;

	/**
	 * The fragment allows to add client only information to a URL, this information
	 * will not be transmitted to the server.
	 * 
	 * @var string
	 */
	private $fragment;

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
	
	/**
	 * 
	 * @param string $protocol
	 * @param string $hostname
	 * @param int $port
	 * @param string $path
	 * @param string $querystr
	 */
	public function __construct(string $protocol, string $hostname, int $port = null, string $path, string $querystr) 
	{
		$this->protocol = $protocol;
		$this->hostname = $hostname;
		$this->port   = $port === null? ($protocol === 'http'? 80 : 443) : $port;
		$this->path = $path;
		
		parse_str($querystr, $query);
		$this->queryString = $query;
	}
	
	/**
	 * Returns the protocol this URL is using.
	 * 
	 * @return string
	 */
	public function getProtocol() : string
	{
		return $this->protocol;
	}
	
	/**
	 * Returns the query string being used (as an array).
	 * 
	 * @return mixed[]
	 */
	public function getQueryString()
	{
		return $this->queryString;
	}
	
	/**
	 * Sets the protocol to be used to communicate when invoking this URL.
	 * 
	 * @param string $protocol
	 * @return URLReflection
	 */
	public function setProtocol($protocol) : URLReflection
	{
		$this->protocol = $protocol;
		return $this;
	}
	
	/**
	 * Sets the query string data. This contains an array of elements.
	 * 
	 * @param mixed[] $queryString
	 * @return URLReflection
	 */
	public function setQueryString(array $queryString) : URLReflection
	{
		$this->queryString = $queryString;
		return $this;
	}
	
	/**
	 * Returns the hostname for the server that hosts this resource.
	 * 
	 * @deprecated
	 * @return string
	 */
	public function getServer() : string
	{
		return $this->hostname;
	}
	
	/**
	 * Returns the hostname for the server that hosts this resource.
	 * 
	 * @return string
	 */
	public function getHostname() : string
	{
		return $this->hostname;
	}
	
	/**
	 * Returns the username used to authenticate against the server that hosts
	 * this resource.
	 * 
	 * @return string
	 */
	public function getUser() : string
	{
		return $this->user;
	}
	
	/**
	 * Returns the password used to authenticate against the server that hosts
	 * this resource.
	 * 
	 * @return string
	 */
	public function getPassword() : string
	{
		return $this->password;
	}
	
	/**
	 * Returns the path that identifies the resource for this URL on the server
	 * that hosts it.
	 * 
	 * @return string
	 */
	public function getPath() : string
	{
		return $this->path;
	}
	
	
	/**
	 * Returns the path that identifies the resource for this URL on the server
	 * that hosts it.
	 * 
	 * @return int
	 */
	public function getPort() : int
	{
		return $this->port;
	}
	
	/**
	 * Sets the hostname to communicate with.
	 * 
	 * @param string $hostname
	 * @return URLReflection
	 */
	public function setHostname($hostname) : URLReflection
	{
		$this->hostname = $hostname;
		return $this;
	}
	
	
	/**
	 * Sets the hostname to communicate with.
	 * 
	 * @deprecated
	 * @param string $hostname
	 * @return URLReflection
	 */
	public function setServer(string $hostname) : URLReflection
	{
		$this->hostname = $hostname;
		return $this;
	}
	
	
	/**
	 * Sets the post to communicate with.
	 * 
	 * @param int $port
	 * @return URLReflection
	 */
	public function setPort(int $port) : URLReflection
	{
		$this->port = $port;
		return $this;
	}
	
	/**
	 * Sets the username to identify with against the remote server.
	 * 
	 * @param string $user
	 * @return URLReflection
	 */
	public function setUser(string $user) : URLReflection
	{
		$this->user = $user;
		return $this;
	}
	
	/**
	 * Sets the password to identify with against the remote server.
	 * 
	 * @param string $password
	 * @return URLReflection
	 */
	public function setPassword(string $password) : URLReflection
	{
		$this->password = $password;
		return $this;
	}
	
	/**
	 * Sets the path to identify the resource.
	 * 
	 * @param string $path
	 * @return URLReflection
	 */
	public function setPath(string $path) : URLReflection
	{
		$this->path = $path;
		return $this;
	}
	
	/**
	 * Sets the fragment of the URL (this does not include the # to separate it)
	 * 
	 * @param string $fragment
	 * @return URLReflection
	 */
	public function setFragment(string $fragment) : URLReflection
	{
		$this->fragment = ltrim($fragment, '#');
		return $this;
	}
	
	/**
	 * Returns the fragment of the url.
	 * 
	 * @return string
	 */
	public function getFragment() : string
	{
		return $this->fragment;
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
	public function stripCredentials() : URLReflection
	{
		return new self($this->protocol, $this->hostname, $this->port, $this->path, http_build_query($this->queryString));
	}
	
	/**
	 * Reads the settings from a URL. 
	 * 
	 * @todo Move to external URL parser
	 * @param URLReflection|string $url
	 * @return URLReflection
	 */
	public static function fromURL($url) : URLReflection 
	{
		/*
		 * If the parameter provided is already a settings object, it will be 
		 * returned as is.
		 */
		if ($url instanceof self) { return $url; }
		
		/**
		 * In the event of our url being a string, parse the url.
		 */
		$parsed = parse_url($url);
		
		/**
		 * If the url is not a valid url, we throw an exception, letting the 
		 * user know that the URL was not valid.
		 */
		if ($parsed === false) { 
			throw new URLReflectionException('Could not parse string as url', 21021911647); 
		}
		
		return self::fromArray($parsed);
	}
	
	/**
	 * 
	 * @param mixed[] $arr
	 * @return URLReflection
	 */
	public static function fromArray($arr) : URLReflection
	{
		$ops = $arr + self::$defaults;
		
		$_ret = new self(
			$ops['scheme'], 
			$ops['host'], 
			$ops['port'], 
			$ops['path'], 
			$ops['query']
		);
		
		if ($ops['user']) {
			$_ret->setUser($ops['user']);
			$_ret->setPassword($ops['pass']);
		}
		
		if ($ops['fragment']) {
			$_ret->setFragment($ops['fragment']);
		}
		
		return $_ret;
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
		$fragment = $this->fragment? sprintf('#%s', $this->fragment) : '';

		$t = sprintf(
			'%s://%s%s%s%s%s', 
			$protocol,
			$credentials? $credentials . '@' : '',
			$hostname,
			$path,
			$query,
			$fragment
		);
		
		return $t;
	}
	
}
