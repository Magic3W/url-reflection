<?php namespace magic3w\http\url\reflection;

use Psr\Http\Message\UriInterface;

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
class URLReflection implements UriInterface
{
	
	/**
	 * The protocol of the URI, this will usually be HTTP or HTTPS but can also
	 * be anything like tel://, ftp:// or mailto://
	 * 
	 * The strong does not contain the schema separator (: or ://)
	 * 
	 * @var string
	 */
	private $scheme;
	
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
	 * Contains a dictionary of default ports for different schemes, this is necessary to ommit
	 * the port in the authority section if it's the default.
	 * 
	 * @var array<string,int>
	 */
	private static $defaultPorts = [
		'http' => 80,
		'https' => 443,
		'ftp'   => 21
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
		$this->scheme = $protocol;
		$this->hostname = $hostname;
		$this->port   = $port === null? ($protocol === 'http'? 80 : 443) : $port;
		$this->path = $path;
		
		$this->queryString = QueryString::parse($querystr);
	}
	
	
	/**
	 * Returns the protocol this URL is using.
	 * 
	 * @return string
	 */
	public function getScheme() : string
	{
		return $this->scheme;
	}
	
	/**
	 * Returns the protocol this URL is using.
	 * 
	 * @deprecated
	 * @return string
	 */
	public function getProtocol() : string
	{
		return $this->scheme;
	}
	
	/**
	 * Returns the query string being used (as an array).
	 * 
	 * @deprecated This has become a misleading name, since it implies the return to be a string
	 * while the PSR demands that getQuery is a string.
	 * @return mixed[]
	 */
	public function getQueryString()
	{
		return $this->queryString;
	}
	
	/**
	 * Returns the query string being used (as an array).
	 * 
	 * @return mixed[]
	 */
	public function getQueryData()
	{
		return $this->queryString;
	}
	
	/**
	 * Returns the query string being used (as an array).
	 * 
	 * @return string
	 */
	public function getQuery()
	{
		return QueryString::encode($this->queryString);
	}
	
	/**
	 * The authority combines user information, hostname and port (if non-standard)
	 * @inheritdoc
	 * 
	 * @return string
	 */
	public function getAuthority() : string
	{
		$authority = $this->hostname;
		$userInfo  = $this->getUserInfo();
		
		if ($this->port !== (self::$defaultPorts[$this->scheme]?? 0)) {
			$authority.= ':' . strval($this->port);
		}
		
		if ($userInfo) {
			$authority = sprintf('%s@%s', $userInfo, $authority);
		}
		
		return $authority;
	}
	
	/**
	 * Sets the protocol to be used to communicate when invoking this URL.
	 * 
	 * @param string $scheme
	 * @return URLReflection
	 */
	public function withScheme($scheme) : UriInterface
	{
		$copy = clone $this;
		$copy->scheme = $scheme;
		return $copy;
	}
	
	/**
	 * Sets the protocol to be used to communicate when invoking this URL.
	 * 
	 * @deprecated URIs should be immutable
	 * @param string $protocol
	 * @return URLReflection
	 */
	public function setProtocol($protocol) : URLReflection
	{
		$this->scheme = $protocol;
		return $this;
	}
	
	/**
	 * Sets the query string data. This contains an array of elements.
	 * 
	 * @param string $query
	 * @return URLReflection
	 */
	public function withQuery($query) : URLReflection
	{
		$copy = clone $this;
		$copy->queryString = QueryString::parse($query);
		return $copy;
	}
	
	/**
	 * Sets the query string data. This contains an array of elements.
	 * 
	 * @deprecated
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
	 * @deprecated
	 * @return string
	 */
	public function getHostname() : string
	{
		return $this->hostname;
	}
	
	/**
	 * Returns the hostname for the server that hosts this resource.
	 * 
	 * @return string
	 */
	public function getHost() : string
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
	 * Returns the user's information.
	 * @inheritdoc
	 * 
	 * @return string
	 */
	public function getUserInfo() : string
	{
		if ($this->user && $this->password) {
			return sprintf('%s:%s', $this->user, $this->password);
		}
		
		elseif ($this->user) {
			return $this->user;
		}
		
		else {
			return '';
		}
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
	 * @param string $host
	 * @return URLReflection
	 */
	public function withHost($host) : URLReflection
	{
		assert(is_string($host));
		
		$copy = clone $this;
		$copy->hostname = $host;
		return $copy;
	}
	
	/**
	 * Sets the hostname to communicate with.
	 * 
	 * @deprecated
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
	public function withPort($port) : URLReflection
	{
		assert(intval($port) > 0);
		
		$copy = clone $this;
		$copy->port = $port;
		return $copy;
	}
	
	
	/**
	 * Sets the post to communicate with.
	 * 
	 * @deprecated
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
	 * @deprecated URIs should be immutable
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
	 * @deprecated URIs should be immutable
	 * @param string $password
	 * @return URLReflection
	 */
	public function setPassword(string $password) : URLReflection
	{
		$this->password = $password;
		return $this;
	}
	
	/**
	 * Set the user information for the URI. Please note that this will return a
	 * copy of the object.
	 * 
	 * @inheritdoc
	 */
	public function withUserInfo($user, $password = null) : UriInterface
	{
		$copy = clone $this;
		$copy->user = $user;
		$copy->password = $password;
		return $copy;
	}
	
	/**
	 * Sets the path to identify the resource.
	 * 
	 * @param string $path
	 * @return URLReflection
	 */
	public function withPath($path) : URLReflection
	{
		$copy = clone $this;
		$copy->path = $path;
		return $copy;
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
	public function withFragment($fragment) : URLReflection
	{
		assert(is_string($fragment));
		
		$copy = clone $this;
		$copy->fragment = $fragment;
		return $copy;
	}
	
	/**
	 * Sets the fragment of the URL (this does not include the # to separate it)
	 * 
	 * @deprecated
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
		return new self(
			$this->scheme,
			$this->hostname,
			$this->port,
			$this->path,
			QueryString::encode($this->queryString)
		);
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
		if ($url instanceof self) {
			return $url; 
		}
		
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
	 * Generates a new URL Reflection from the current request context. Returns
	 * null if the context is a cli environment.
	 * 
	 * @return URLReflection|null
	 */
	public static function fromGlobals():? URLReflection
	{
		if (php_sapi_name() === 'cli') {
			return null;
		}
		
		/**
		 * PHP is a bit weird about the way it handles the HTTPS key. It contains a
		 * non empty value whenever the request is HTTPS, but the key disappears if
		 * the request is not sent via HTTPS.
		 */
		if (isset($_SERVER['HTTPS'])) {
			$protocol = $_SERVER['HTTPS']? 'https' : 'http';
		}
		else {
			$protocol = 'http';
		}
		
		$hostname = $_SERVER['HTTP_HOST'];
		$port     = $_SERVER['SERVER_PORT'];
		$path     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$querystr = $_SERVER['QUERY_STRING']?? '';
		
		assert(is_string($path));
		
		$reflection = new URLReflection($protocol, $hostname, $port, $path, $querystr);
		
		if ($_SERVER['AUTH_TYPE']?? false) {
			$reflection->withUserInfo($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		}
		
		return $reflection;
	}
	
	/**
	 * Converts the reflection to a URL. This prevents the application from having
	 * to treat these differently from how a regular URL would be treated.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		
		if ($this->password && !$this->user) {
			throw new \Exception('URL format error', 2010301458);
		}
		
		$protocol = $this->scheme;
		$stdport  = ['http' => 80, 'https' => 443][$protocol];
		$credentials = implode(':', array_filter([$this->user, $this->password]));
		$hostname = $this->port !== $stdport? sprintf('%s:%d', $this->hostname, $this->port) : $this->hostname;
		$path = $this->path;
		$query = $this->queryString? sprintf('?%s', QueryString::encode($this->queryString)) : '';
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
