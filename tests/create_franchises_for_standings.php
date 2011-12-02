<?php
include 'test_bootstrap.php';

$conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
echo "Collecting all games in our season...\n";
$mapper = new \IBL\FranchiseMapper($conn);
$allFranchises = $mapper->findAll();
echo "Writing franchise objects into fixture file...\n";
file_put_contents('./fixtures/franchises.txt', serialize($allFranchises));
echo "Done\n";

