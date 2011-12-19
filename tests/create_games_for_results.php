<?php
include 'test_bootstrap.php';

$conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
echo "Collecting all games for week 24...\n";
$gameMapper = new \IBL\GameMapper($conn);
$allGames = $gameMapper->findByWeek(24);
echo "Writing games objects into fixture file...\n";
file_put_contents('./fixtures/games-24.txt', serialize($allGames));
echo "Done\n";



