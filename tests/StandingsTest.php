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
        $this->assertEquals(
            2,
            count($testResults),
            'Have wins and losses'
        );
        $testWins = $testResults['wins'];
        $testLosses = $testResults['losses'];
        $this->assertEquals(
            24, 
            count($testWins),
            '24 teams worth of wins'
        );
        $this->assertEquals(
            24, 
            count($testLosses),
            '24 teams of losses'
        );
    }

    public function testGeneratePlayoff()
    {
        $testGames = unserialize(file_get_contents('./fixtures/games.txt'));
        $testFranchises = unserialize(file_get_contents('./fixtures/franchises.txt'));
        $testStandings = new \IBL\Standings($testGames, $testFranchises);
        $testResults = $testStandings->generatePlayoff();
        $this->assertEquals(
            4,
            count($testResults),
            '4 components of playoff standings'
        );
        $this->assertTrue(count($testResults) > 0, 'More than 1 result');
        $this->assertEquals(
            3, 
            count($testResults['leaders']['AC']), 
            '3 AC Leaders'
        );
        $this->assertEquals(
            3, 
            count($testResults['leaders']['NC']),
            '3 NC Leaders'
        );
        $this->assertEquals(
            9, 
            count($testResults['wildCardStandings']['AC']),
            '9 AC wild card teams'
        );
        $this->assertEquals(
            9, 
            count($testResults['wildCardStandings']['NC']),
            '9 NC wild card teams'
        );
        $this->assertEquals(
            3, 
            count($testResults['magicNumber']['AC']),
            '3 AC magic numbers'
        );
        $this->assertEquals(
            3, 
            count($testResults['magicNumber']['NC']),
            '3 NC magic numbers'
        );
    }

    public function testGenerateRegularForSeason()
    {
        $testGames = unserialize(file_get_contents('./fixtures/games.txt'));
        $testFranchises = unserialize(file_get_contents('./fixtures/franchises.txt'));
        $testStandings = new \IBL\Standings($testGames, $testFranchises);
        $testResults = $testStandings->generateRegular();
        $this->assertEquals(
            4, 
            count($testResults['AC']['East']), 
            '4 teams in AC East'
        );
        $this->assertEquals(
            4, 
            count($testResults['AC']['Central']), 
            '4 teams in AC Central'
        );
        $this->assertEquals(
            4, 
            count($testResults['AC']['West']), 
            '4 teams in AC West'
        );
        $this->assertEquals(
            4, 
            count($testResults['NC']['East']), 
            '4 teams in NC East'
        );
        $this->assertEquals(
            4, 
            count($testResults['NC']['Central']), 
            '4 teams in NC Central'
        );
        $this->assertEquals(
            4, 
            count($testResults['NC']['West']), 
            '4 teams in NC West'
        );
        $testTeamResult = $testResults['AC']['East'][0];
        $this->assertEquals(
            1, 
            $testTeamResult['teamId'], 
            'Got expected team ID'
        );
        $this->assertEquals(
            97, 
            $testTeamResult['wins'], 
            'Got expected win total'
        );
        $this->assertEquals(
            65, 
            $testTeamResult['losses'], 
            'Got expected loss total'
        );
        $this->assertEquals(
            '--', 
            $testTeamResult['gb'], 
            'Got expected GB total'
        );
    }
    
    public function testGenerateRegularForWeek()
    {
        $testGames = unserialize(file_get_contents('./fixtures/games.txt'));
        $testFranchises = unserialize(file_get_contents('./fixtures/franchises.txt'));
        $testStandings = new \IBL\Standings($testGames, $testFranchises, 10);
        $testResults = $testStandings->generateRegular();
        $this->assertEquals(
            2,
            count($testResults),
            'Got 2 conferences for regular standings'
        );
        $this->assertEquals(
            3,
            count($testResults['AC']),
            '3 divisions in AC'
        );
        $this->assertEquals(
            3,
            count($testResults['NC']),
            '3 divisions in NC'
        );
        $this->assertEquals(
            4,
            count($testResults['AC']['East']),
            '4 teams in AC East'
        );
        $this->assertEquals(
            4,
            count($testResults['AC']['Central']),
            '4 teams in AC Central'
        );
        $this->assertEquals(
            4,
            count($testResults['AC']['West']),
            '4 teams in AC West'
        );
        $this->assertEquals(
            4,
            count($testResults['NC']['East']),
            '4 teams in NC East'
        );
        $this->assertEquals(
            4,
            count($testResults['NC']['Central']),
            '4 teams in NC Central'
        );
        $this->assertEquals(
            4,
            count($testResults['NC']['West']),
            '4 teams in NC West'
        );
    }
}

