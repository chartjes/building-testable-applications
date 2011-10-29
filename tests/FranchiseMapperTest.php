<?php

include '../bootstrap.php';

class FranchiseMapperTest extends \PHPUnit_Framework_TestCase
{
    protected $conn;

    public function setUp()
    {
        $this->conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
    }

    public function testDelete()
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

        // Grab a fresh copy of the object and delete it
        $deleteFranchise = $mapper->findById($franchise->getId());
        $this->assertTrue($mapper->delete($deleteFranchise), "Deleted Franchise record");

        // Check to see if we actually deleted this record
        $checkFranchise = $mapper->findById($franchise->getId());
        $this->assertFalse($checkFranchise, "Verified Franchise record deleted");
    }

    public function testFindByConference()
    {
        $mapper = new IBL\FranchiseMapper($this->conn);
        $results = $mapper->findByConference('AC');
        $this->assertEquals(12, count($results));
    }   

    public function testFindByConferenceDivision()
    {
        $mapper = new IBL\FranchiseMapper($this->conn);
        $results = $mapper->findByConferenceDivision('AC', 'West');
        $this->assertEquals(4, count($results));
    } 

    public function testFindByNickname()
    {
        $mapper = new IBL\FranchiseMapper($this->conn);
        $result = $mapper->findByNickname('MAD');
        $this->assertNotNull($result);
        $this->assertEquals('Monrovia Madness', $result->getName());
    }
}


