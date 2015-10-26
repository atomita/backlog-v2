<?php

namespace atomita;

class BacklogException extends \Exception
{
	protected $response;
	
	function  __construct($message = '', $code = 0, Exception $previous = NULL, $response = '')
	{
		parent::__construct($message, $code, $previous);
		$this->response = $response;
	}

	function getResponse()
	{
		return $this->response;
	}

}
