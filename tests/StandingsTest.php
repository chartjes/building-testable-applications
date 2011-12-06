<?php
include './test_bootstrap.php';

class StandingsTest extends \PHPUnit_Framework_TestCase
{
    public $standings;

    public function testGenerateBreakdown()
    {
        $testGames = unserialize(file_get_contents('./fixtures/games.txt'));
        $testFranchises = unserialize(file_get_contents('./fixtures/franchises.txt'));
        $testStandings = new \IBL\Standings($testGames, $testFranchises);
        $testResults = $testStandings->generateBreakdown();
        $this->assertTrue(count($testResults) > 0);
        $testWins = $testResults['wins'];
        $testLosses = $testResults['losses'];
        $this->assertEquals(24, count($testWins));
        $this->assertEquals(24, count($testLosses));
    }

    public function testGenerateRegularForSeason()
    {
        $testGames = unserialize(file_get_contents('./fixtures/games.txt'));
        $testFranchises = unserialize(file_get_contents('./fixtures/franchises.txt'));
        $testStandings = new \IBL\Standings($testGames, $testFranchises);
        $testResults = $testStandings->generateRegular();
        $this->assertTrue(count($testResults) > 0);
        $this->assertEquals(4, count($testResults['AC']['East']), '4 teams in AC East');
        $this->assertEquals(4, count($testResults['AC']['Central']), '4 teams in AC Central');
        $this->assertEquals(4, count($testResults['AC']['West']), '4 teams in AC West');
        $this->assertEquals(4, count($testResults['NC']['East']), '4 teams in NC East');
        $this->assertEquals(4, count($testResults['NC']['Central']), '4 teams in NC Central');
        $this->assertEquals(4, count($testResults['NC']['West']), '4 teams in NC West');
        $testTeamResult = $testResults['AC']['East'][1];
        $this->assertEquals(1, $testTeamResult['teamId'], 'Got expected team ID');
        $this->assertEquals(97, $testTeamResult['wins'], 'Got expected win total');
        $this->assertEquals(65, $testTeamResult['losses'], 'Got expected loss total');
        $this->assertEquals('--', $testTeamResult['gb'], 'Got expected GB total');
    }
    
    public function testGenerateRegularForWeek()
    {
        $testGames = unserialize(file_get_contents('./fixtures/games.txt'));
        $testFranchises = unserialize(file_get_contents('./fixtures/franchises.txt'));
        $testStandings = new \IBL\Standings($testGames, $testFranchises, 10);
        $testResults = $testStandings->generateRegular();
        $this->assertTrue(count($testResults) > 0);
    }
}

