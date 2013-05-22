<?php
/**
* PHPCI - Continuous Integration for PHP
*
* @copyright    Copyright 2013, Block 8 Limited.
* @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
* @link         http://www.phptesting.org/
*/

// Let PHP take a guess as to the default timezone, if the user hasn't set one:
date_default_timezone_set(@date_default_timezone_get());

// Set up a basic autoloader for PHPCI:
$autoload = function ($class) {
    $file = str_replace(array('\\', '_'), '/', $class);
    $file .= '.php';

    if (substr($file, 0, 1) == '/') {
        $file = substr($file, 1);
    }

    if (is_file(dirname(__FILE__) . '/' . $file)) {
        include(dirname(__FILE__) . '/' . $file);
        return;
    }
};

spl_autoload_register($autoload, true, true);

// Define our APPLICATION_PATH, if not already defined:
if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', dirname(__FILE__) . '/');
}

// Load Composer autoloader:
require_once(APPLICATION_PATH . 'vendor/autoload.php');

// Load configuration if present:
$config = new b8\Config();

if (file_exists(APPLICATION_PATH . 'config.php')) {
    require(APPLICATION_PATH . 'config.php');

    // Define our PHPCI_URL, if not already defined:
    if (!defined('PHPCI_URL')) {
        define('PHPCI_URL', $config->get('install_url', '') . '/');
    }
}

// Set up the registry:
$config->set('app_namespace', 'PHPCI');
$config->set('default_controller', 'Index');
$config->set('view_path', dirname(__FILE__) . '/PHPCI/View/');