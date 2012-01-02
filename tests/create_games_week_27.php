<?php
include 'test_bootstrap.php';

$conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
echo "Collecting all games for week 27...\n";
$gameMapper = new \IBL\GameMapper($conn);
$allGames = $gameMapper->findByWeek(27);
echo "Writing games objects into fixture file...\n";
file_put_contents('./fixtures/games-27.txt', serialize($allGames));
echo "Done\n";



