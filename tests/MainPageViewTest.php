<?php
include './test_bootstrap.php';

class MainPageViewTest extends \PHPUnit_Framework_TestCase
{
    protected $_twig;

    public function setUp()
    {
        // also include our libraries installed using Composer
        include APP_ROOT . 'vendor/.composer/autoload.php';

        // We are using Twig for templating
        $loader = new \Twig_Loader_Filesystem(APP_ROOT . 'templates');
        $this->_twig = new \Twig_Environment($loader);
        $this->_twig = new \Twig_Environment($loader, array('debug' => true));
        $this->_twig->addExtension(new \Twig_Extensions_Extension_Debug());
    }

    public function tearDown()
    {
        unset($this->_twig);
    }

    public function testMainPage()
    {
        $dbConn = new \PDO(
            'pgsql:host=localhost;dbname=ibl_stats', 
            'stats',
            'st@ts=Fun'
        );
        // Load data that we will need for the front page
        $gameMapper = new \IBL\GameMapper($dbConn);
        $franchiseMapper = new \IBL\FranchiseMapper($dbConn);
        $rotationMapper = new \IBL\RotationMapper($dbConn);
        $scheduleMapper = new \IBL\ScheduleMapper($dbConn);

        $games = unserialize(file_get_contents('./fixtures/games.txt'));
        $franchises = unserialize(
            file_get_contents('./fixtures/franchises.txt')
        );
        $standings = new \IBL\Standings($games, $franchises);
        $regularStandings = $standings->generateRegular();
        $currentWeek = 27;
        $currentGames = unserialize(
            file_get_contents('./fixtures/games-27.txt')
        );
        $currentResults = $gameMapper->generateResults(
            $currentGames,
            $franchises
        );
        $rotations = unserialize(
            file_get_contents('./fixtures/rotations-27.txt')
        );
        $currentRotations = $rotationMapper->generateRotations(
            $rotations,
            $franchises
        );
        $rawSchedules = unserialize(
            file_get_contents('./fixtures/raw-schedules-27.txt')
        );
        $franchiseMap = unserialize(
            file_get_contents('./fixtures/franchise-mappings.txt')
        );
        $currentSchedules = $scheduleMapper->generate(
            $rawSchedules,
            $franchiseMap
        );

        // Display the data
        $response = $this->_twig->render(
            'index.html', 
            array(
                'currentWeek' => $currentWeek,
                'currentResults' => $currentResults,
                'currentRotations' => $currentRotations,
                'currentSchedules' => $currentSchedules,
                'franchises' => $franchises,
                'rotationWeek' => $currentWeek,
                'scheduleWeek' => $currentWeek,
                'standings' => $regularStandings, 
            )
        );
        $standingsHeader = "Standings through week 27";
        $resultsHeader = "Results for week 27";
        $rotationsHeader = "Rotations for Week 27";
        $scheduleHeader = "Schedule for Week 27";
        $rotation = "KC Greinke, CHN Lilly -2, CHN Wells -2"; 
        $this->assertTrue(stripos($response, $standingsHeader) !== false);
        $this->assertTrue(stripos($response, $resultsHeader) !== false);
        $this->assertTrue(stripos($response, $rotationsHeader) !== false);
        $this->assertTrue(stripos($response, $scheduleHeader) !== false);

        // Look for a known team abbreviation
        $this->assertTrue(stripos($response, "MAD") !== false);

        // Look for a specific rotation to appear
        $this->assertTrue(stripos($response, $rotation) !== false);
    }
}

