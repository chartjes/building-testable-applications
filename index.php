<?php 

include 'bootstrap.php';

// Load data that we will need for the front page
$gameMapper = new \IBL\GameMapper($container['db_connection']);
$franchiseMapper = new \IBL\FranchiseMapper($container['db_connection']);
$games = $gameMapper->findAll();
$franchises = $franchiseMapper->findAll();
$standings = new \IBL\Standings($games, $franchises);
$regularStandings = $standings->generateRegular();
$currentWeek = $gameMapper->getCurrentWeek();
$currentResults = $gameMapper->generateResults(
    $gameMapper->findByWeek($currentWeek), 
    $franchises
);

// Display the data
echo $twig->render(
    'index.html', 
    array(
        'standings' => $regularStandings, 
        'franchises' => $franchises,
        'currentWeek' => $currentWeek,
        'currentResults' => $currentResults
    )
);
