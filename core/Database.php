<?php

namespace Core;

use Exception;
use mysqli;

class Database {
	/**
	 * @param array $student
	 * @return bool
	 * @throws Exception
	 */
	function insert_student(array $student) {
		$conn = $this->get_connection();
		$prepared_statement = $conn->prepare("INSERT INTO `tbl_student`(admission_number, first_name, last_name, email, phone_number, address, dob, entry_points) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$prepared_statement->bind_param("issssssi", $student['admission_number'], $student['first_name'], $student['last_name'], $student['email'], $student['phone_number'], $student['address'], $student['dob'], $student['entry_points']);
		$successful = $prepared_statement->execute();
		$prepared_statement->close();
		$conn->close();
		return $successful;
	}

	/**
	 * @return int|mixed
	 * @throws Exception
	 */
	function get_unique_admission_number() {
		$admission_number = random_int(100000, 999999);
		$this->create_db();
		$this->create_table();
		$conn = $this->get_connection();

		$prepared_statement = $conn->prepare("SELECT * FROM tbl_student where admission_number=?");
		$prepared_statement->bind_param("i", $admission_number);
		$prepared_statement->execute();
		$result = $prepared_statement->get_result();

		$conn->close();
		return $result->num_rows > 0 ? $this->get_unique_admission_number() : $admission_number;
	}

	/**
	 * @param $admission_number
	 * @return array|null
	 * @throws Exception
	 */
	function get_student($admission_number) {
		$this->create_db();
		$this->create_table();
		$conn = $this->get_connection();

		$prepared_statement = $conn->prepare("SELECT * FROM tbl_student where admission_number=?");
		$prepared_statement->bind_param("i", $admission_number);
		$prepared_statement->execute();
		$student = $prepared_statement->get_result()->fetch_assoc();

		$conn->close();
		return $student;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	private function create_table() {
		$conn = $this->get_connection();
		$db_create = $conn->query("CREATE TABLE IF NOT EXISTS `tbl_student`(student_id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, admission_number BIGINT NOT NULL UNIQUE, first_name VARCHAR(60) NOT NULL, last_name VARCHAR(60) NOT NULL, email VARCHAR(60) NOT NULL, phone_number VARCHAR(60) NOT NULL, address VARCHAR(60) NOT NULL, dob DATE NOT NULL DEFAULT CURRENT_TIMESTAMP, entry_points INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, suspended BOOLEAN DEFAULT FALSE)");
		if (!$db_create) throw new Exception("Unable to Create Table `tbl_student`.\nError: {$conn->error}");
		$conn->close();
		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	private function create_db() {
		$conn = $this->get_connection(null);
		$db_create = $conn->query('CREATE DATABASE IF NOT EXISTS `soap_db`');
		if (!$db_create) throw new Exception("Unable to Create Database `soap_db`.\nError: {$conn->error}");
		$conn->close();
		return true;
	}

	/**
	 * @param string $db_name
	 * @return mysqli
	 * @throws Exception
	 */
	private function get_connection($db_name = 'soap_db') {
		list($host, $user, $pass) = (new Environment)->env(["DB_HOST", "DB_USER", "DB_PASS"]);
		if ($db_name) $connection = mysqli_connect($host, $user, $pass, $db_name); else $connection = mysqli_connect($host, $user, $pass);
		if (!$connection) throw new Exception("Unable to Connect to the Database with the Supplied Settings.\nError: " . mysqli_connect_error());
		return $connection;
	}
}
