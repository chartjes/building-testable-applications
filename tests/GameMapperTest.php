<?php

include '../bootstrap.php';

class GameMapperText extends \PHPUnit_Framework_TestCase
{
    protected $conn;

    public function setUp()
    {
        $this->conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
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

        $mapper = new IBL\GameMapper($this->conn);
        $mapper->save($game);
        $this->assertTrue($game->getId() !== null); 
        $newGame = $mapper->findById($game->getId());
        $this->assertInstanceOf('IBL\Game', $newGame);
        $this->assertEquals($game->getId(), $newGame->getId());
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

        $mapper = new IBL\GameMapper($this->conn);
        $mapper->save($game);
        $this->assertTrue($game->getId() !== null); 
    }


}

