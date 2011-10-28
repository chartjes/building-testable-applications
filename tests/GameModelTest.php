<?php

include '../bootstrap.php';

class GameModelTest extends \PHPUnit_Framework_TestCase
{
    protected $conn;

    public function setUp()
    {
        $this->conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
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

    public function testSave()
    {
        $game = new IBL\Game();
        $game->setWeek(28);
        $game->setHome_Score(1);
        $game->setAway_Score(0);
        $game->setHome_Team_Id(1);
        $game->setAway_Team_Id(0);
        $this->assertNull($game->getId());

        $mapper = new IBL\GameMapper($this->conn);
        $mapper->save($game);
        $this->assertTrue($game->getId() !== null); 
    }

    public function testFindById()
    {
        $game = new IBL\Game();
        $game->setWeek(28);
        $game->setHome_Score(1);
        $game->setAway_Score(0);
        $game->setHome_Team_Id(1);
        $game->setAway_Team_Id(0);
        $this->assertNull($game->getId());

        $mapper = new IBL\GameMapper($this->conn);
        $mapper->save($game);
        $this->assertTrue($game->getId() !== null); 
        $newGame = $mapper->findById($game->getId());
        $this->assertInstanceOf('IBL\Game', $newGame);
        $this->assertEquals($game->getId(), $newGame->getId());
    }
}
