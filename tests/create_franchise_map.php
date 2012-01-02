<?php
include 'test_bootstrap.php';

$conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
echo "Collecting all mappings...\n";
$sql = "SELECT * FROM teams2010";
$sth = $conn->prepare($sql);
$sth->execute();
$rows = $sth->fetchAll();

foreach ($rows as $row) {
    $franchiseMap[$row['code']] = $row['ibl']; 
}
echo "Writing franchise mappings into fixture file...\n";
file_put_contents('./fixtures/franchise-mappings.txt', serialize($franchiseMap));
echo "Done\n";

