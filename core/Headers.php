<?php


namespace Core;


class Headers {
	static function request_header_is($header, $value) {
		return strcasecmp((apache_request_headers()[$header] ?? ""), $value) === 0;
	}

	static function get_request_header($header, $default_value = "") {
		return apache_request_headers()[$header] ?? $default_value;
	}

	static function set_response_headers($status_code = 200, $content_type = null) {
		$content_type = $content_type ?: Headers::get_request_header("Accept", "application/json");
		$content_type = str_contains($content_type, ",") ? substr($content_type, 0, strpos($content_type, ",")) : $content_type;
		header("Content-Type: $content_type");
		header("HTTP/1.1 $status_code", true, $status_code);
	}
}
