<?php
include './test_bootstrap.php';

class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    protected $_conn;

    public function setUp()
    {
        $this->_conn = new PDO(
            'pgsql:host=localhost;dbname=ibl_stats', 
            'stats', 
            'st@ts=Fun'
        ); 
    }

    public function tearDown()
    {
        unset($this->_conn);
    }

    public function testGetAllSchedules()
    {
        /**
         * Normally a schedule should contain 24 * 27 games, or 648 games but
         * there was a one-game playoff in our test season so it should be 
         * 649
         */
        $mapper = new \IBL\ScheduleMapper($this->_conn);
        $testSchedules = $mapper->findAll();
        $this->assertEquals(
            649, 
            count($testSchedules),
            "Found correct number of schedules for entire season"
        );
    }

    public function testGetSchedulesForWeek()
    {
        $mapper = new \IBL\ScheduleMapper($this->_conn);
        $testSchedules = $mapper->findByWeek(27);
        $this->assertEquals(
            24,
            count($testSchedules),
            "Found correct number of schedules for a week"
        );
    }
}

