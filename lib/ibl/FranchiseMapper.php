<?php

namespace IBL;

class FranchiseMapper 
{
    protected $conn;
    protected $map = array();

    public function __construct($conn)
    {
        $this->conn = $conn; 

        // Load our class mapper from the XML config file
        foreach (simplexml_load_file(LIB_ROOT . 'ibl/maps/franchise.xml') as $field) {
            $this->map[(string)$field->name] = $field; 
        }
    }

    public function createFranchiseFromRow($row)
    {
        $franchise = new \IBL\Franchise($this);

        foreach ($this->map as $field) {
            $setProp = (string)$field->mutator;
            $value = $row[(string)$field->name];

            if ($setProp && $value) {
                call_user_func(array($franchise, $setProp), $value); 
            } 
        } 

        return $franchise;
    }

    public function findById($id)
    {
        try {
            $sql = "SELECT * FROM franchises WHERE id = ?";
            $sth = $this->conn->prepare($sql);
            $sth->execute(array((int)$id));
            $row = $sth->fetch();

            if ($row) {
                return $this->createFranchiseFromRow($row);
            }
        } catch (\PDOException $e) {
            echo "DB Error: " . $e->getMessage(); 
        }

        return false;
    }

    public function save(\IBL\Franchise $franchise)
    {
        if ($this->findById($franchise->getId())) {
            $this->update($franchise); 
        } else {
            $this->insert($franchise); 
        }
    }

    protected function insert(\IBL\Franchise $franchise) 
    {
        try {
            $sql = "INSERT INTO franchises (nickname, name, conference, division, ip, id) 
                VALUES(?, ?, ?, ?, ?, ?) RETURNING id";
            $sth = $this->conn->prepare($sql);
            $sth->execute(array($franchise->getNickname(), $franchise->getName(), $franchise->getConference(), $franchise->getDivision(), $franchise->getIp(), $franchise->getId()));
        } catch(\PDOException $e) {
            echo "A database problem occurred: " . $e->getMessage(); 
        }
         
    }

    protected function update(\IBL\Franchise $franchise)
    {
        try {
            $sql = "UPDATE franchises SET nickname = ?, name = ?, conference = ?, division = ?, ip = ? WHERE id = ?";
            $sth = $this->conn->prepare($sql);
            $fields = array('nickname', 'name', 'conference', 'division', 'ip', 'id');
            $binds = array();

            foreach ($fields as $fieldName) {
                $field = $this->map[$fieldName];
                $getProp = (string)$field->accessor;
                $binds[] = $franchise->$getProp();
            }

            $response = $sth->execute($binds);
        } catch(\PDOException $e) {
            echo "A database problem occurred: " . $e->getMessage(); 
        }
    }
}

