<?php


namespace Core;


class Response {
	function json(string|array $response, $status_code = 200): Response {
		Headers::set_response_headers($status_code);
		echo json_encode($response);
		return $this;
	}

	function html(string|array $response, $status_code = 200): Response {
		Headers::set_response_headers($status_code);
		echo "<pre>";
		print_r($response);
		echo "</pre>";
		return $this;
	}
}
