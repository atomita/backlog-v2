<?php

namespace atomita;

class Backlog
{
	const URL_TEMPLATE = 'https://%s.backlog.jp/api/v2/%s?%s';

	protected $api = array();

	function __construct($space_name, $api_key)
	{
		$this->space = $space_name;
		$this->apiKey = $api_key;
	}

	function param($api)
	{
		return $this->$api;
	}
	
	function __get($api)
	{
		$that = clone $this;
		$that->api[] = $api;
		return $that;
	}

	function __call($method, $args)
	{
		$this->api[] = $method;
		return call_user_func_array(array($this, 'get'), $args);
	}

	function get(array $data = array(), array $params = array(), array $option = array())
	{
		return $this->request('GET', $data, $params, $option);
	}
	
	function post(array $data = array(), array $params = array(), array $option = array())
	{
		return $this->request('POST', $data, $params, $option);
	}

	function delete(array $data = array(), array $params = array(), array $option = array())
	{
		return $this->request('DELETE', $data, $params, $option);
	}

	function request($http_method, array $data = array(), array $params = array(), array $option = array())
	{
		static $key_colon_value = null;
		if (is_null($key_colon_value)){
			$key_colon_value = function($k, $v){
				return is_int($k) ? $v : "$k: $v";
			};
		}
		
		$opt = array_merge(array(
			'space_name' => $this->space,
			'header' => array(),
		), $option);

		$query = array_merge(array(
			'apiKey' => $this->apiKey,
		), $data);

		$segments = array();
		foreach($this->api as $api){
			$segments[] = array_key_exists($api, $params) ? $params[$api] : $api;
		}
		$uri = implode('/', $segments);


		$http_method = strtoupper($http_method);
		switch ($http_method){
			case 'POST':
				$header  = array_merge(array(
					'Content-Type' => 'application/x-www-form-urlencoded', // multipart/form-data, 
				), $opt['header']);
				break;
			
			case 'GET':
				$header = array_merge(array(
				), $opt['header']);
				break;

			default:
				$header = array_merge(array(
				), $opt['header']);
		}

		if (!isset($url)){
			$url = sprintf(self::URL_TEMPLATE, $opt['space_name'], $uri, http_build_query($query, '', '&'));
		}

		$context = array(
			'http' => array(
				'method'		=> $http_method,
				'header'		=> implode("\r\n", array_map($key_colon_value, array_keys($header), array_values($header))),
				'ignore_errors' => true,
			)
		);
		// if (isset($content)){
		// 	$context['html']['content'] = $content;
		// }


		$response = file_get_contents($url, false, stream_context_create($context));

		$type = $this->responseContexType($http_response_header);
		switch ($type){
			case 'application/json':
				$res = $json = json_decode($response, true);
				$json_error = json_last_error();
				break;
			case 'application/octet-stream':
				$res = $response;
				break;
			default:
				$res = $response;
		}

		if (isset($json) and isset($json['errors'])){
			// error
			throw new BacklogException($json['errors'][0]['message'], $json['errors'][0]['code'], null, $json);
		}
		elseif ('application/json' == $type and JSON_ERROR_NONE !== $json_error){
			// error
			throw new BacklogException('json error.', $json_error, null, $response);
		}
		elseif (empty($response)){
			// error
			throw new BacklogException('Not Found Content', 404);
		}
		
		return $res;
	}

	protected function responseContexType($http_response_header)
	{
		foreach ($http_response_header as $value) {
			if (preg_match('{^Content-Type:\s*(.*?)$}iu', $value, $m)) {
				$values = array_map('trim', explode(';', $m[1]));
				return reset($values);
			}
		}
		return false;
	}

}
