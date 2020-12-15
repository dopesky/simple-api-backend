<?php
require_once 'vendor/autoload.php';
require_once 'routes/routes.php';

use Core\{Response, Routing, Headers};

try {
	$action = Routing::get_action(basename(__FILE__));

	list($class, $function) = explode("::", $action['action']);
	$class = "App\\$class";
	$class = new $class;
	$response = call_user_func([$class, $function], ...array_values($action['params']));

	if (!$response instanceof Response) if (Headers::request_header_is("Accept", "application/json")) (new Response)->json($response); else (new Response)->html($response);
} catch (Exception $exception) {
	$message = ["ok" => false, "error" => $exception->getMessage()];
	if (Headers::request_header_is("Accept", "application/json")) (new Response)->json($message, $exception->getCode()); else (new Response)->html($message, $exception->getCode());
}
