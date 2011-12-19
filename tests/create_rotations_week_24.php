<?php
include 'test_bootstrap.php';

$conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
echo "Collecting rotations for week 24...\n";
$mapper = new \IBL\RotationMapper($conn);
$rotations = $mapper->findByWeek(24);
echo "Writing rotation objects into fixture file...\n";
file_put_contents('./fixtures/rotations-24.txt', serialize($rotations));
echo "Done\n";

