<?php

include 'test_bootstrap.php';

class GameMapperTest extends \PHPUnit_Framework_TestCase
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

    public function testDelete()
    {
        $mapper = new IBL\GameMapper($this->_conn);
        $game = new IBL\Game();
        $game->setWeek(30);
        $game->setHomeScore(10);
        $game->setAwayScore(0);
        $game->setHomeTeamId(1);
        $game->setAwayTeamId(2);
        $this->assertNull($game->getId());
        $mapper->save($game);

        $deleteGame = $mapper->findById($game->getId());
        $mapper->delete($deleteGame);

        $checkGame = $mapper->findById($game->getId());
        $this->assertFalse($checkGame);
    }

    public function testFindByAwayTeamId()
    {
        $mapper = new IBL\GameMapper($this->_conn);
        $results = $mapper->findByAwayTeamId(24);
        $this->assertEquals(count($results), 81);  
    }
    
    public function testFindByHomeTeamId()
    {
        $mapper = new IBL\GameMapper($this->_conn);
        $results = $mapper->findByHomeTeamId(24);
        $this->assertEquals(count($results), 81);  
    }

    public function testFindById()
    {
        $game = new IBL\Game();
        $game->setWeek(28);
        $game->setHomeScore(1);
        $game->setAwayScore(0);
        $game->setHomeTeamId(1);
        $game->setAwayTeamId(0);
        $this->assertNull($game->getId());

        $mapper = new IBL\GameMapper($this->_conn);
        $mapper->save($game);
        $this->assertTrue($game->getId() !== null); 
        $newGame = $mapper->findById($game->getId());
        $this->assertInstanceOf('IBL\Game', $newGame);
        $this->assertEquals($game->getId(), $newGame->getId());
    }
    
    public function testFindByWeek()
    {
        $mapper = new IBL\GameMapper($this->_conn);
        $results = $mapper->findByWeek(10);
        $this->assertEquals(count($results), 72);  
    }
   
    public function testSave()
    {
        $game = new IBL\Game();
        $game->setWeek(28);
        $game->setHomeScore(1);
        $game->setAwayScore(0);
        $game->setHomeTeamId(1);
        $game->setAwayTeamId(0);
        $this->assertNull($game->getId());

        $mapper = new IBL\GameMapper($this->_conn);
        $mapper->save($game);
        $this->assertTrue($game->getId() !== null); 
    }
}

