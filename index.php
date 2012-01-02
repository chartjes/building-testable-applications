<?php 

include 'bootstrap.php';

// Load data that we will need for the front page
$gameMapper = new \IBL\GameMapper($container['db_connection']);
$franchiseMapper = new \IBL\FranchiseMapper($container['db_connection']);
$rotationMapper = new \IBL\RotationMapper($container['db_connection']);
$scheduleMapper = new \IBL\ScheduleMapper($container['db_connection']);

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

/**
 * We need to use some intelligence in deciding what schedules we need to
 * show. If we have less than half the results in, show the schedule
 * from the previous week
 */

if (count($currentResults) < 12) {
    $scheduleWeek = $currentWeek - 1;
} else {
    $scheduleWeek = $currentWeek;
}

$franchiseMap = $franchiseMapper->generateMap(
    $scheduleMapper->teamsTable
);
$rawSchedule = $scheduleMapper->findByWeek($scheduleWeek);
$currentSchedules = $scheduleMapper->generate(
    $rawSchedule,
    $franchiseMap 
);

// Display the data
echo $twig->render(
    'index.html', 
    array(
        'currentWeek' => $currentWeek,
        'currentResults' => $currentResults,
        'currentRotations' => $currentRotations,
        'currentSchedules' => $currentSchedules,
        'franchises' => $franchises,
        'rotationWeek' => $rotationWeek,
        'scheduleWeek' => $scheduleWeek,
        'standings' => $regularStandings, 
    )
);
