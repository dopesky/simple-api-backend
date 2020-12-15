<?php

namespace Core;

use Exception;
use JetBrains\PhpStorm\ArrayShape;

class Routing {
	private static array $routes = [];

	static function post(string|array $path = '/', string $action = '') {
		if (is_array($path)) {
			foreach ($path as $item) Routing::post($item, $action);
		} else Routing::$routes[] = ["method" => "post", "path" => $path, "action" => $action];
	}

	static function get(string|array $path = '/', string $action = '') {
		if (is_array($path)) {
			foreach ($path as $item) Routing::get($item, $action);
		} else Routing::$routes[] = ["path" => $path, "action" => $action];
	}

	/**
	 * @param $filename
	 * @return array
	 * @throws Exception
	 */
	#[ArrayShape(["action" => "string", "params" => "array"])]
	static function get_action(string $filename): array {
		$request_uri = Routing::get_request_uri($filename);
		$supported_methods = [];

		foreach (Routing::$routes as $route) {
			$route['method'] = $route['method'] ?? "get";
			["method" => $method, "path" => $route_uri, "action" => $action] = $route;

			Routing::validate_route($request_uri, $action, $method);

			$route_path = array_values(array_filter(explode("/", $route_uri), fn($path) => $path));
			$request_path = array_values(array_filter(explode("/", $request_uri), fn($path) => $path));
			$continue = sizeof($request_path) === 0 && sizeof($route_path) !== 0;
			$params = [];
			foreach ($request_path as $key => $value) {
				if (!isset($route_path[$key]) || (strcasecmp($value, $route_path[$key]) !== 0 && !Routing::is_replaceable($route_path[$key]))) {
					$continue = true;
					break;
				}
				if (Routing::is_replaceable($route_path[$key])) $params[substr($route_path[$key], 1, -1)] = $value;
			}

			if ($continue) continue;

			if (strcasecmp($method, $_SERVER['REQUEST_METHOD']) !== 0) {
				if (array_search(strtoupper($method), $supported_methods) === false) $supported_methods[] = strtoupper($method);
				continue;
			}

			if (sizeof($route_path) !== sizeof($request_path)) throw new Exception("Route is Missing Some Required Parameters!", 400);
			return ["action" => $action, "params" => $params];
		}
		if ($supported_methods) throw new Exception("Route does not Support <em>" . strtoupper($_SERVER['REQUEST_METHOD']) . "</em> Method. It only Supports the Following Methods: <em>" . implode(separator: ", ", array: $supported_methods) . "</em>", 405);
		throw new Exception("Route Endpoint not Available! <em><b>" . strtoupper($_SERVER['REQUEST_METHOD']) . ":</b> " . $request_uri . "</em>", 404);
	}

	private static function get_request_uri(string $filename): string {
		$base_request_uri = str_replace(search: "/$filename", replace: "", subject: $_SERVER['SCRIPT_NAME']);
		$request_uri = str_replace(search: "/$filename", replace: "", subject: $_SERVER['REQUEST_URI']);

		return str_replace(search: $base_request_uri, replace: "", subject: $request_uri);
	}

	private static function is_replaceable(string $value): bool {
		$value = trim($value);
		return str_starts_with(haystack: $value, needle: "{") && str_ends_with(haystack: $value, needle: "}");
	}

	/**
	 * @param string $route_uri
	 * @param string $action
	 * @param string $method
	 * @throws Exception
	 */
	private static function validate_route(string $route_uri, string $action, string $method) {
		if (!trim($action)) throw new Exception("Invalid Action String Provided for Route <em><b>" . strtoupper($method) . ":</b> " . $route_uri . "</em>", 400);
		if (!trim($route_uri)) throw new Exception("Invalid Path String Provided for Route <em><b>" . strtoupper($method) . ":</b> " . $route_uri . "</em>", 400);
		if (Routing::is_duplicate_route($route_uri, $method)) throw new Exception("Duplicate Route Detected. <em><b>" . strtoupper($method) . ":</b> " . $route_uri . "</em>", 400);
	}

	private static function is_duplicate_route(string $path, string $method): bool {
		$path = trim(str_replace("/", "", $path));
		$path_to_check = strtolower($path . $method);
		$duplicate_routes = array_values(array_filter(array: Routing::$routes, callback: function ($route) use ($path_to_check) {
			$path = trim(str_replace("/", "", $route['path']));
			$method = ($route['method'] ?? "get");
			return strtolower($path . $method) === $path_to_check;
		}));
		return sizeof($duplicate_routes) > 1;
	}
}
