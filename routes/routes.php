<?php

use Core\Routing;

Routing::post("/students", "Student::addStudent");
Routing::get("/students/{admission_number}", "Student::fetchStudent");
