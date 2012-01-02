<?php
include 'test_bootstrap.php';

$conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
echo "Collecting raw schedules for week 27...\n";
$mapper = new \IBL\ScheduleMapper($conn);
$rawSchedules = $mapper->findByWeek(27);
echo "Writing schedule objects into fixture file...\n";
file_put_contents('./fixtures/raw-schedules-27.txt', serialize($rawSchedules));
echo "Done\n";

