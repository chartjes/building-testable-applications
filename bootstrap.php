<?php

// set some directory names that we will need

if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/');
}

if (!defined('LIB_ROOT')) {
    define('LIB_ROOT', APP_ROOT . 'lib/');
}

// include our autoloader
include LIB_ROOT . 'psr0.autoloader.php';

// We are using Twig for templating
$loader = new Twig_Loader_Filesystem(APP_ROOT . 'templates');
$twig = new Twig_Environment($loader);

// Initialize our dependency injection container
if (apc_exists('di_container')) {
    $container = apc_fetch('container');
} else {
    $container = new \Pimple();
    $container['dsn'] = 'pgsql:host=localhost;dbname=ibl_stats';
    $container['dbuser'] = 'stats';
    $container['dbpasswd'] = 'st@ts=Fun';
    apc_store('di_container', $container);
}
