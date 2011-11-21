<?php

include 'test_bootstrap.php';

class RotationMapperTest extends \PHPUnit_Framework_TestCase
{
    protected $_conn;

    public function setUp()
    {
        $this->_conn = new PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
    }

    public function testDelete()
    {
        $mapper = new IBL\RotationMapper($this->_conn);
        $rotation = new IBL\Rotation();
        $rotation->setWeek(29);
        $rotation->setRotation('Huey, Dewey, Louie');
        $rotation->setFranchiseId(0);
        $mapper->save($rotation);

        $deleteRotation = $mapper->findById($rotation->getId());
        $mapper->delete($deleteRotation);

        $checkRotation = $mapper->findById($rotation->getId());
        $this->assertFalse($checkRotation);
    }

    public function testFindByFranchiseId()
    {
        $mapper = new IBL\RotationMapper($this->_conn);
        $rotations = $mapper->findByFranchiseId(1);
        $this->assertTrue(count($rotations) > 0);
    }

    public function testFindByWeek()
    {
        $mapper = new IBL\RotationMapper($this->_conn);
        $rotations = $mapper->findByWeek(1);
        $this->assertTrue(count($rotations) > 0);
    }
}

