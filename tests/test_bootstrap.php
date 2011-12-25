<?php

// set some directory names that we will need

if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/../');
}

if (!defined('LIB_ROOT')) {
    define('LIB_ROOT', APP_ROOT . 'lib/');
}

// include our autoloader
include LIB_ROOT . 'psr0.autoloader.php';

