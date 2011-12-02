<?php
include './test_bootstrap.php';

class StandingsTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateRegular()
    {
        $testGames = unserialize(file_get_contents('./fixtures/games.txt'));
        $testFranchises = unserialize(file_get_contents('./fixtures/franchises.txt'));
        $testStandings = new \IBL\Standings($testGames, $testFranchises);;
        $testResults = $testStandings->generateRegular();
        $this->assertTrue(count($testResults) > 0);
        $testTeamResult = $testResults['AC']['East'][0];
        $this->assertEquals(1, $testTeamResult['teamId'], 'Got expected team ID');
        $this->assertEquals(97, $testTeamResult['wins'], 'Got expected win total');
        $this->assertEquals(65, $testTeamResult['losses'], 'Got expected loss total');
        $this->assertEquals('--', $testTeamResult['gb'], 'Got expected GB total');
    }
}

