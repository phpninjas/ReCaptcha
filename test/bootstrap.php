<?php

/**
 * Include configuration from the public directory.
 * This contains the vendor autoload pieces and
 * configuration parameters for environments
 */
defined("APPLICATION_ENV")||define("APPLICATION_ENV", "test");

echo "Test bootstrap as '".APPLICATION_ENV."' environment\n";

// Get the config from the main app.
require_once __DIR__ . "/../vendor/autoload.php";

// redefine the include path to include the tests directory.
set_include_path(implode(PATH_SEPARATOR, [
    __DIR__, // test directory assets should always take priority.
    get_include_path(),
]));