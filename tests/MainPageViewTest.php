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
        $loader = new Twig_Loader_Filesystem(APP_ROOT . 'templates');
        $this->_twig = new Twig_Environment($loader);
        $this->_twig = new \Twig_Environment($loader, array('debug' => true));
        $this->_twig->addExtension(new \Twig_Extensions_Extension_Debug());
    }

    public function tearDown()
    {
        unset($this->_twig);
    }

    public function testMainPage()
    {
        $dbConn = new PDO(
            'pgsql:host=localhost;dbname=ibl_stats', 
            'stats',
            'st@ts=Fun'
        );
        // Load data that we will need for the front page
        $gameMapper = new \IBL\GameMapper($dbConn);
        $franchiseMapper = new \IBL\FranchiseMapper($dbConn);
        $rotationMapper = new \IBL\RotationMapper($dbConn);
        $scheduleMapper = new \IBL\ScheduleMapper($dbConn);

        $games = $gameMapper->findAll();
        $franchises = $franchiseMapper->findAll();
        $standings = new \IBL\Standings($games, $franchises);
        $regularStandings = $standings->generateRegular();
        $currentWeek = $gameMapper->getCurrentWeek();
        $currentResults = $gameMapper->generateResults(
            $gameMapper->findByWeek($currentWeek), 
            $franchises
        );

        /**
         * If we don't have any rotations for the current week, make sure to grab
         * rotations for the previous week
         */
        $rotations = $rotationMapper->findByWeek($currentWeek);
        $rotationWeek = $currentWeek;

        if (count($rotations) == 0) {
            $rotations = $rotationMapper->findByWeek($currentWeek - 1);
            $rotationWeek = $currentWeek - 1;
        }

        $currentRotations = $rotationMapper->generateRotations(
            $rotations,
            $franchises
        );

        /**
         * We need to use some intelligence in deciding what schedules we need to
         * show. If we have less than half the results in, show the schedule
         * from the previous week
         */

        if (count($currentResults) < 12) {
            $scheduleWeek = $currentWeek - 1;
        } else {
            $scheduleWeek = $currentWeek;
        }

        $currentSchedules = $scheduleMapper->generateByWeek($scheduleWeek);

        // Display the data
        $response = $this->_twig->render(
            'index.html', 
            array(
                'currentWeek' => $currentWeek,
                'currentResults' => $currentResults,
                'currentRotations' => $currentRotations,
                'currentSchedules' => $currentSchedules,
                'franchises' => $franchises,
                'rotationWeek' => $rotationWeek,
                'scheduleWeek' => $scheduleWeek,
                'standings' => $regularStandings, 
            )
        );
        $standingsHeader = "Standings through week 27";
        $resultsHeader = "Results for week 27";
        $rotationsHeader = "Rotations for Week 27";
        $scheduleHeader = "Schedule for Week 27";
        
        $this->assertTrue(stripos($response, $standingsHeader) !== false);
        $this->assertTrue(stripos($response, $resultsHeader) !== false);
        $this->assertTrue(stripos($response, $rotationsHeader) !== false);
        $this->assertTrue(stripos($response, $scheduleHeader) !== false);

    }
}

