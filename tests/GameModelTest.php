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
}
