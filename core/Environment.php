<?php

namespace Core;

use Dotenv\Dotenv;

class Environment {
	function __construct($path = __DIR__ . DIRECTORY_SEPARATOR . "..") {
		$dotenv = Dotenv::createImmutable($path);
		$dotenv->load();
		$dotenv->required("DB_HOST")->notEmpty();
		$dotenv->required("DB_USER")->notEmpty();
		$dotenv->required("DB_PASS");
	}

	function env($string) {
		if (is_array($string)) {
			$envs = [];
			foreach ($string as $env) {
				$envs[] = $this->env($env);
			}
			return $envs;
		}
		return $_ENV[$string] ?? null;
	}
}
