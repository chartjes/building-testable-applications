<?php

// set the APP_ROOT we're going to be using
define('APP_ROOT', basename('..') . '/');
define('LIB_ROOT', APP_ROOT . '/lib/');

// include our autoloader
include LIB_ROOT . 'psr0.autoloader.php';

// We are using Twig for templating
$loader = new Twig_Loader_Filesystem(APP_ROOT . 'templates');
$twig = new Twig_Environment($loader);

// create the database connection we are going to use
//$conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun');
