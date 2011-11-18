<?php

namespace IBL;

class FranchiseMapper
{
    protected $_conn;
    protected $_map = array();

    public function __construct($_conn)
    {
        $this->_conn = $_conn; 

        // Load our class mapper from the XML config file
        $fields = simplexml_load_file(LIB_ROOT . 'ibl/maps/franchise.xml');

        foreach ($fields as $field) {
            $this->_map[(string)$field->name] = $field; 
        }
    }

    public function createFranchiseFromRow($row)
    {
        $franchise = new \IBL\Franchise($this);

        foreach ($this->_map as $field) {
            $setProp = (string)$field->mutator;
            $value = trim($row[(string)$field->name]);

            if ($setProp && $value) {
                call_user_func(array($franchise, $setProp), $value); 
            } 
        } 

        return $franchise;
    }

    public function delete(\IBL\Franchise $franchise)
    {
        if ($franchise->getId() == null) {
            return false;
        } 

        try {
            $sql = "DELETE FROM franchises WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$franchise->getId()));

            return true;
        } catch (\PDOException $e) {
            throw new \Exception("DB Error: " . $e->getMessage());
        }     
    }

    public function findByConference($conference)
    {
        try {
            $sql = "SELECT * FROM franchises WHERE conference = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((string) $conference));
            $rows = $sth->fetchAll();
            $franchises = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $franchises[] = $this->createFranchiseFromRow($row); 
                } 
            }

            return $franchises;
        } catch (\PDOException $e) {
            throw new \Exception("DB Error: " . $e->getMessage()); 
        } 
    }

    public function findByConferenceDivision($conference, $division)
    {
        try {
            $sql = "
                SELECT * 
                FROM franchises 
                WHERE conference = ? AND division = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((string) $conference, (string) $division));
            $rows = $sth->fetchAll();
            $franchises = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $franchises[] = $this->createFranchiseFromRow($row); 
                } 
            }

            return $franchises;
        } catch (\PDOException $e) {
            throw new \Exception("DB Error: " . $e->getMessage()); 
        } 
    }

    public function findByNickname($nickname)
    {
        try {
            $sql = "SELECT * FROM franchises WHERE nickname = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((string) $nickname));
            $row = $sth->fetch();

            return $this->createFranchiseFromRow($row);
        } catch (\PDOException $e) {
            throw new \Exception("DB Error: " . $e->getMessage()); 
        } 
    }

    public function findById($id)
    {
        try {
            $sql = "SELECT * FROM franchises WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
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
            $sql = "
                INSERT INTO franchises 
                (nickname, name, conference, division, ip, id) 
                VALUES(?, ?, ?, ?, ?, ?)";
            $sth = $this->_conn->prepare($sql);
            $binds = array(
                $franchise->getNickname(),
                $franchise->getName(),
                $franchise->getConference(),
                $franchise->getDivision(),
                $franchise->getIp(),
                $franchise->getId()
            );
            $sth->execute($binds);
        } catch(\PDOException $e) {
            echo "A database problem occurred: " . $e->getMessage(); 
        }
    }

    protected function update(\IBL\Franchise $franchise)
    {
        try {
            $sql = "
                UPDATE franchises 
                SET nickname = ?, 
                name = ?, 
                conference = ?, 
                division = ?, 
                ip = ? 
                WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
            $fields = array(
                'nickname', 
                'name', 
                'conference', 
                'division', 
                'ip', 
                'id'
            );
            $binds = array();

            foreach ($fields as $fieldName) {
                $field = $this->_map[$fieldName];
                $getProp = (string)$field->accessor;
                $binds[] = $franchise->$getProp();
            }

            $sth->execute($binds);
        } catch(\PDOException $e) {
            echo "A database problem occurred: " . $e->getMessage(); 
        }
    }
}

