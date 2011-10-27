<?php

// set some directory names that we will need
define('APP_ROOT', __DIR__ . '/');
define('LIB_ROOT', APP_ROOT . 'lib/');

// include our autoloader
include LIB_ROOT . 'psr0.autoloader.php';

// We are using Twig for templating
$loader = new Twig_Loader_Filesystem(APP_ROOT . 'templates');
$twig = new Twig_Environment($loader);

