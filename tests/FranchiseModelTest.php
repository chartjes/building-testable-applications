<?php

include 'test_bootstrap.php';

class FranchiseModelTest extends \PHPUnit_Framework_TestCase
{
    protected $conn;

    public function setUp()
    {
        $this->conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
    }
    
    public function testIdOnlySetOnce()
    {
        $franchise = new IBL\Franchise();
        $id = 25;
        $franchise->setId($id);
        $this->assertEquals($id, $franchise->getId());
        $anotherId = 26;
        $franchise->setId($anotherId);
        $this->assertEquals($id, $franchise->getId());
    }
    
    public function testSaveUpdatesDatabase()
    {
        $mapper = new IBL\FranchiseMapper($this->conn);
        $franchise = new IBL\Franchise();
        $franchise->setId(25);
        $franchise->setNickname('TST');
        $franchise->setName('Test Team');
        $franchise->setConference('Conference');
        $franchise->setDivision('Division');
        $franchise->setIp(0);
        $mapper->save($franchise);

        // Update existing model
        $franchise->setIp(35);
        $mapper->save($franchise);
        
        // Reload Franchise record and compare them
        $franchise2 = $mapper->findById($franchise->getId());
        $this->assertEquals(35, $franchise2->getIp());
    }

}

