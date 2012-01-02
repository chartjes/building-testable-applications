<?php
include 'test_bootstrap.php';

$conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
echo "Collecting rotations for week 27...\n";
$mapper = new \IBL\RotationMapper($conn);
$rotations = $mapper->findByWeek(27);
echo "Writing rotation objects into fixture file...\n";
file_put_contents('./fixtures/rotations-27.txt', serialize($rotations));
echo "Done\n";

