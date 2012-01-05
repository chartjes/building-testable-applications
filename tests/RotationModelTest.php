<?php

include 'test_bootstrap.php';

class RotationModelTest extends \PHPUnit_Framework_TestCase
{
    protected $_conn;

    public function setUp()
    {
        $this->_conn = new \PDO('pgsql:host=localhost;dbname=ibl_stats', 'stats', 'st@ts=Fun'); 
    }

    public function tearDown()
    {
        unset($this->_conn); 
    }

    public function testIdOnlySetOnce()
    {
        $rotation = new \IBL\Rotation();
        $id = 10;
        $rotation->setId($id);
        $this->assertEquals($id, $rotation->getId());
        $anotherId = 20;
        $rotation->setId($anotherId);
        $this->assertEquals($id, $rotation->getId());
    }

    public function testSaveUpdatesDatabase()
    {
        $mapper = new \IBL\RotationMapper($this->_conn);
        $rotation = new \IBL\Rotation();
        $rotation->setWeek(29);
        $rotation->setRotation('Huey, Dewey, Louie');
        $rotation->setFranchiseId(0);
        $mapper->save($rotation);
         
        $rotation2 = $mapper->findById($rotation->getId());
        $this->assertEquals($rotation->getId(), $rotation2->getId());
        $mapper->delete($rotation);

        $mapper = new \IBL\RotationMapper($this->_conn);
        $rotation = new \IBL\Rotation();
        $rotation->setWeek(30);
        $rotation->setRotation("Curly, Larry, Moe");
        $rotation->setFranchiseId(0);
        $mapper->save($rotation);

        $rotation->setRotation("Shemp, Larry, Moe");
        $mapper->save($rotation);

        $rotation2 = $mapper->findById($rotation->getId());
        $this->assertEquals("Shemp, Larry, Moe", $rotation2->getRotation());
        $mapper->delete($rotation);
    }
}
