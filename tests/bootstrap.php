<?php
require __DIR__ . '/../vendor/autoload.php';

class MockRequests{
	private $response;
	public $args;

	public function __construct($body, $status_code = 200, $headers = []){
		$this->response = new MockResponse($status_code, $body, $headers);
	}

	public function __call($method, $args){
		$this->args = $args;

		return $this->response;
	}
}

class MockResponse{
	public $status_code;
	public $body;
	public $headers;
	public $success;

	public function __construct($status_code, $body, $headers){
		$this->status_code = $status_code;
		$this->body = $body;
		$this->headers = $headers;

		$this->success = is_numeric($status_code) && $status_code >= 200 && $status_code < 300;
	}
		
}