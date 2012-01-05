<?php

namespace IBL;

class RotationMapper
{
    protected $_conn;
    protected $_map = array();

    public function __construct($conn)
    {
        $this->_conn = $conn; 

        // Load our class mapper from the XML config file
        $fields = simplexml_load_file(LIB_ROOT . 'ibl/maps/rotation.xml');

        foreach ($fields as $field) {
            $this->_map[(string)$field->name] = $field; 
        }
    }

    public function createRotationFromRow($row)
    {
        $rotation = new \IBL\Rotation($this);

        foreach ($this->_map as $field) {
            $setProp = (string)$field->mutator;
            $value = trim($row[(string)$field->name]);

            if ($setProp && $value) {
                call_user_func(array($rotation, $setProp), $value); 
            } 
        } 

        return $rotation;
    }

    public function delete(\IBL\Rotation $rotation)
    {
        if ($rotation->getId() == null) {
            return false; 
        }

        try {
            $sql = "DELETE FROM rotations WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$rotation->getId()));
        } catch (\PDOException $e) {
            echo "DB Error: " . $e->getMessage();
        }     
    }

    public function findByFranchiseId($franchiseId)
    {
        try {
            $sql = "SELECT * FROM rotations WHERE franchise_id = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$franchiseId));
            $row = $sth->fetch();

            if ($row) {
                return $this->createRotationFromRow($row);
            }
        } catch (\PDOException $e) {
            echo "DB Error: " . $e->getMessage(); 
        }

        return false;
    }
   
    public function findById($id)
    {
        try {
            $sql = "SELECT * FROM rotations WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$id));
            $row = $sth->fetch();

            if ($row) {
                return $this->createRotationFromRow($row);
            }
        } catch (\PDOException $e) {
            echo "DB Error: " . $e->getMessage(); 
        }

        return false;
    }

    public function findByWeek($week)
    {
        try {
            $sql = "SELECT * FROM rotations WHERE week = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$week));
            $rows = $sth->fetchAll();
            $rotations = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $rotations[] = $this->createRotationFromRow($row); 
                }
            } 

            return $rotations;
        } catch (\PDOException $e) {
            echo 'DB_Error: ' . $e->getMessage(); 
        }
    }

    public function generateRotations($rotations, $franchises)
    {
        if (count($rotations) == 0) {
            return array(); 
        }
        
        $response = array();
        $franchiseInfo = array();

        foreach ($franchises as $franchise) {
            $franchiseInfo[$franchise->getId()] = $franchise->getNickname(); 
        }
        
        foreach ($rotations as $rotation) {
            $response[$franchiseInfo[$rotation->getFranchiseId()]] =
                $rotation->getRotation();
        }

        ksort($response);

        return $response;
    }

    public function save(\IBL\rotation $rotation)
    {
        if ($rotation->getId()) {
            $this->update($rotation); 
        } else {
            $this->insert($rotation); 
        }
    }

    protected function _insert(\IBL\rotation $rotation) 
    {
        try {
            // Of course, Postgres has to do things a little differently
            // and we cannot use lastInsertId() so you alter the INSERT
            // statement to return the insert ID 
            $sql = "
                INSERT INTO rotations 
                (week, rotation, franchise_id) 
                VALUES(?, ?, ?) RETURNING id";
            $sth = $this->_conn->prepare($sql);
            $binds = array(
                $rotation->getWeek(),
                $rotation->getRotation(),
                $rotation->getFranchiseId()
            );
            $response = $sth->execute($binds);
            $result = $sth->fetch(\PDO::FETCH_ASSOC);

            if ($result['id']) {
                $inserted = $this->findById($result['id']);

                if (method_exists($inserted, 'setId')) {
                    $rotation->setId($result['id']);
                }
            } else {
                throw new \Exception('Unable to create new Rotation record'); 
            }
        } catch(\PDOException $e) {
            echo "A database problem occurred: " . $e->getMessage(); 
        }

    }

    protected function _update(\IBL\rotation $rotation)
    {
        try {
            $sql = "
                UPDATE rotations 
                SET week = ?, 
                rotation = ?,
                franchise_id = ?
                WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
            $fields = array(
                'week', 
                'rotation',
                'franchise_id',
                'id'
            );
            $binds = array();

            foreach ($fields as $fieldName) {
                $field = $this->_map[$fieldName];
                $getProp = (string)$field->accessor;
                $binds[] = $rotation->{$getProp}();
            }

            $response = $sth->execute($binds);
        } catch(\PDOException $e) {
            echo "A database problem occurred: " . $e->getMessage(); 
        }
    }
}
