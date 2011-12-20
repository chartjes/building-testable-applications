<?php 

include 'bootstrap.php';

// Load data that we will need for the front page
$gameMapper = new \IBL\GameMapper($container['db_connection']);
$franchiseMapper = new \IBL\FranchiseMapper($container['db_connection']);
$rotationMapper = new \IBL\RotationMapper($container['db_connection']);

$games = $gameMapper->findAll();
$franchises = $franchiseMapper->findAll();
$standings = new \IBL\Standings($games, $franchises);
$regularStandings = $standings->generateRegular();
$currentWeek = $gameMapper->getCurrentWeek();
$currentResults = $gameMapper->generateResults(
    $gameMapper->findByWeek($currentWeek), 
    $franchises
);

/**
 * If we don't have any rotations for the current week, make sure to grab
 * rotations for the previous week
 */
$rotations = $rotationMapper->findByWeek($currentWeek);
$rotationWeek = $currentWeek;

if (count($rotations) == 0) {
    $rotations = $rotationMapper->findByWeek($currentWeek - 1);
    $rotationWeek = $currentWeek - 1;
}

$currentRotations = $rotationMapper->generateRotations(
    $rotations,
    $franchises
);

// Display the data
echo $twig->render(
    'index.html', 
    array(
        'currentWeek' => $currentWeek,
        'currentResults' => $currentResults,
        'currentRotations' => $currentRotations,
        'franchises' => $franchises,
        'rotationWeek' => $rotationWeek,
        'standings' => $regularStandings, 
    )
);
