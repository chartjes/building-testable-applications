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

$container = new \Pimple();
$container['db_connection'] = function ($c) {
    return new PDO(
        'pgsql:host=localhost;dbname=ibl_stats', 
        'stats',
        'st@ts=Fun'
    );
};
$container['franchise_mapper'] = function ($c) {
    return new \IBL\FranchiseMapper($c['db_connection']);
};

$mapper = $container['franchise_mapper'];

