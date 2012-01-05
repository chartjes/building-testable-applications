<?php
include './test_bootstrap.php';

class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSchedulesForWeek()
    {
        // No DB connection required if we're using fixtures
        $mapper = new \IBL\ScheduleMapper();
        $rawSchedules = unserialize(
            file_get_contents('./fixtures/raw-schedules-27.txt')
        );
        $franchiseMap = unserialize(
            file_get_contents('./fixtures/franchise-mappings.txt')
        );

        $testSchedules = $mapper->generate(
            $rawSchedules,
            $franchiseMap
        );
        $this->assertEquals(
            24,
            count($testSchedules),
            "Found correct number of schedules for a week"
        );
        $this->assertEquals(
            true,
            array_key_exists('MAD', $testSchedules),
            "Found MAD in week 27 schedule"
        );
    }
}

