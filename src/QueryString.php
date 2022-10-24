<?php

namespace magic3w\http\url\reflection;

class QueryString
{
	
	/**
	 * 
	 * @param string $str
	 * @param string $delimiter
	 * @return array<int|string,(string|null)[]|string|null>
	 * 
	 * @see https://www.php.net/manual/en/function.parse-str.php#76792
	 */
	public static function parse(string $str, string $delimiter = '&') : array
	{
		/**
		 * We cannot deal with empty strings here.
		 */
		assert(strlen($delimiter) > 0);
		
		$arr = [];
		
		/**
		 * The outer pairs can just be split by the delimiter. Please note that this
		 * may cause issues if you use a delimiter that is not part of  PHP_QUERY_RFC3986
		 * which would break it apart at undesirable spots.
		 */
		$pairs = explode($delimiter, $str);
		
		# loop through each pair
		foreach ($pairs as $i) {
			/**
			 * We split into name and value and immediately urldecode the result.
			 */
			[$name, $value] = array_map(
				fn($e) => rawurldecode($e), 
				explode('=', $i, 2)
			) + [1 => null];
			
			# if name already exists
			if (isset($arr[$name])) {
				# stick multiple values into an array
				$arr = self::extractPhpVariable($arr, $name, $value);
			}
			# otherwise, simply stick it in a scalar
			else {
				$arr = self::extractPhpVariable($arr, $name, $value);
			}
		}
		
		# return result array
		return $arr;
	}
	
	/**
	 * 
	 * @param array<int|string,mixed> $query
	 * @param string $delimiter
	 * @return string
	 * 
	 * @see https://www.php.net/manual/en/function.parse-str.php#76792
	 */
	public static function encode(array $query, string $delimiter = '&')
	{
		return http_build_query(array_map(fn($e) => $e === null? '' : $e, $query), '', $delimiter, PHP_QUERY_RFC3986);
	}
	
	/**
	 * This is not my code! But it's modified to suit the specific needs of SF
	 * @see https://github.com/thephpleague/uri-components/blob/4b845f6c00b9088ac2cc302cfa78566ca6899202/src/QueryString.php#L352
	 * 
	 * Parses a query pair like parse_str without mangling the results array keys.
	 *
	 * <ul>
	 * <li>empty name are not saved</li>
	 * <li>If the value from name is duplicated its corresponding value will be overwritten</li>
	 * <li>if no "[" is detected the value is added to the return array with the name as index</li>
	 * <li>if no "]" is detected after detecting a "[" the value is added to the return array with the name index</li>
	 * <li>if there's a mismatch in bracket usage the remaining part is dropped</li>
	 * <li>“.” and “ ” are not converted to “_”</li>
	 * <li>If there is no “]”, then the first “[” is not converted to becomes an “_”</li>
	 * <li>no whitespace trimming is done on the key value</li>
	 * </ul>
	 *
	 * @see https://php.net/parse_str
	 * @see https://wiki.php.net/rfc/on_demand_name_mangling
	 * @see https://github.com/php/php-src/blob/master/ext/standard/tests/strings/parse_str_basic1.phpt
	 * @see https://github.com/php/php-src/blob/master/ext/standard/tests/strings/parse_str_basic2.phpt
	 * @see https://github.com/php/php-src/blob/master/ext/standard/tests/strings/parse_str_basic3.phpt
	 * @see https://github.com/php/php-src/blob/master/ext/standard/tests/strings/parse_str_basic4.phpt
	 *
	 * @param mixed[] $data  the submitted array
	 * @param string  $name  the pair key
	 * @param ?string $value the pair value
	 * @return mixed[]
	 */
	private static function extractPhpVariable(array $data, string $name, ?string $value): array
	{
		
		if ('' === $name) {
			return $data;
		}
		
		$left_bracket_pos = strpos($name, '[');
		if (false === $left_bracket_pos) {
			$data[$name] = $value;
			
			return $data;
		}
		
		$right_bracket_pos = strpos($name, ']', $left_bracket_pos);
		if (false === $right_bracket_pos) {
			$data[$name] = $value;
			
			return $data;
		}
		
		$key = substr($name, 0, $left_bracket_pos);
		if (!array_key_exists($key, $data) || !is_array($data[$key])) {
			$data[$key] = [];
		}
		
		$index = substr($name, $left_bracket_pos + 1, $right_bracket_pos - $left_bracket_pos - 1);
		if ('' === $index) {
			$data[$key][] = $value;
			
			return $data;
		}
		
		$remaining = substr($name, $right_bracket_pos + 1);
		if (!str_starts_with($remaining, '[') || false === strpos($remaining, ']', 1)) {
			$remaining = '';
		}
		
		$data[$key] = self::extractPhpVariable($data[$key], $index.$remaining, $value);
		
		return $data;
	}
}
