<?php

include 'test_bootstrap.php';

class GameModelTest extends \PHPUnit_Framework_TestCase
{
    protected $_conn;

    public function setUp()
    {
        $this->_conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
    }

    public function tearDown()
    {
        unset($this->_conn); 
    }

    public function testIdOnlySetOnce()
    {
        $game = new IBL\Game();
        $id = 10;
        $game->setId($id);
        $this->assertEquals($id, $game->getId());
        $anotherId = 20;
        $game->setId($anotherId);
        $this->assertEquals($id, $game->getId());
    }

    public function testSaveUpdatesDatabase()
    {
        $mapper = new IBL\GameMapper($this->_conn);
        $game = new IBL\Game();
        $game->setWeek(30);
        $game->setHomeTeamId(25);
        $game->setAwayTeamId(26);
        $game->setHomeScore(1);
        $game->setAwayScore(2);
        $mapper->save($game);

        $game->setAwayScore(1);
        $game->setHomeScore(2);
        $mapper->save($game);
        
        $game2 = $mapper->findById($game->getId());
        $this->assertEquals(2, $game2->getHomeScore());

        $mapper->delete($game);
    }
}
