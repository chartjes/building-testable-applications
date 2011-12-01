<?php

include 'test_bootstrap.php';

class StandingsTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateAll()
    {
        // Load our game objects that have been stored as fixtures
        $testGames = unserialize(file_get_contents('./fixtures/games_for_standings.txt'));
        $conn = new \PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
        $testFranchiseMapper = new \IBL\FranchiseMapper($conn);
        $testStandings = new \IBL\Standings($testGames, $testFranchiseMapper);;
        $testResults = $testStandings->generateBasic();
        $this->assertTrue(count($testResults) > 0);
        $testTeamResult = $testResults['AC']['East'][0];
        $this->assertEquals(1, $testTeamResult['teamId'], 'Got expected team ID');
        $this->assertEquals(97, $testTeamResult['wins'], 'Got expected win total');
        $this->assertEquals(65, $testTeamResult['losses'], 'Got expected loss total');
        $this->assertEquals('--', $testTeamResult['gb'], 'Got expected GB total');
    }
}


