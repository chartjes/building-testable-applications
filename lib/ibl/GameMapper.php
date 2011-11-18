<?php

namespace IBL;

class GameMapper
{
    protected $_conn;
    protected $_map = array();

    public function __construct($conn)
    {
        $this->_conn = $conn; 

        // Load our class mapper from the XML config file
        $fields = simplexml_load_file(LIB_ROOT . 'ibl/maps/game.xml');

        foreach ($fields as $field) {
            $this->_map[(string)$field->name] = $field; 
        }
    }

    public function createGameFromRow($row)
    {
        $game = new Game($this);

        foreach ($this->_map as $field) {
            $setProp = (string)$field->mutator;
            $value = trim($row[(string)$field->name]);

            if ($setProp && $value) {
                call_user_func(array($game, $setProp), $value); 
            } 
        } 

        return $game;
    }

    public function delete(\IBL\Game $game)
    {
        if ($game->getId() == null) {
            return false; 
        }

        try {
            $sql = "DELETE FROM games WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$game->getId()));
        } catch (\PDOException $e) {
            echo "DB Error: " . $e->getMessage();
        }     
    }

    public function findByAwayTeamId($awayTeamId)
    {
        try {
            $sql = "SELECT * FROM games WHERE away_team_id = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$awayTeamId));
            $rows = $sth->fetchAll();
            $games = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $games[] = $this->createGameFromRow($row); 
                }
            } 

            return $games;
        } catch (\PDOException $e) {
            echo 'DB_Error: ' . $e->getMessage(); 
        }
    }

    public function findById($id)
    {
        try {
            $sql = "SELECT * FROM games WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$id));
            $row = $sth->fetch();

            if ($row) {
                return $this->createGameFromRow($row);
            }
        } catch (\PDOException $e) {
            echo "DB Error: " . $e->getMessage(); 
        }

        return false;
    }

    public function findByHomeTeamId($homeTeamId)
    {
        try {
            $sql = "SELECT * FROM games WHERE home_team_id = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$homeTeamId));
            $rows = $sth->fetchAll();
            $games = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $games[] = $this->createGameFromRow($row); 
                }
            } 

            return $games;
        } catch (\PDOException $e) {
            echo 'DB_Error: ' . $e->getMessage(); 
        }
    }

    public function findByWeek($week)
    {
        try {
            $sql = "SELECT * FROM games WHERE week = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int)$week));
            $rows = $sth->fetchAll();
            $games = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $games[] = $this->createGameFromRow($row); 
                }
            } 

            return $games;
        } catch (\PDOException $e) {
            echo 'DB_Error: ' . $e->getMessage(); 
        }
    }

    public function save(\IBL\Game $game)
    {
        if ($game->getId()) {
            $this->update($game); 
        } else {
            $this->insert($game); 
        }
    }

    protected function insert(\IBL\Game $game) 
    {
        try {
            // Of course, Postgres has to do things a little differently
            // and we cannot use lastInsertId() so you alter the INSERT
            // statement to return the insert ID 
            $sql = "
                INSERT INTO games 
                (week, home_score, away_score, home_team_id, away_team_id) 
                VALUES(?, ?, ?, ?, ?) RETURNING id";
            $sth = $this->_conn->prepare($sql);
            $binds = array(
                $game->getWeek(),
                $game->getHomeScore(),
                $game->getAwayScore(),
                $game->getHomeTeamId(),
                $game->getAwayTeamId() 
            );
            $response = $sth->execute($binds);
            $result = $sth->fetch(\PDO::FETCH_ASSOC);

            if ($result['id']) {
                $inserted = $this->findById($result['id']);

                if (method_exists($inserted, 'setId')) {
                    $game->setId($result['id']);
                }
            } else {
                throw new \Exception('Unable to create new Game record'); 
            }
        } catch(\PDOException $e) {
            echo "A database problem occurred: " . $e->getMessage(); 
        }

    }

    protected function update(\IBL\Game $game)
    {
        try {
            $sql = "
                UPDATE games 
                SET week = ?, 
                home_score = ?, 
                away_score = ?, 
                home_team_id = ?, 
                away_team_id = ? 
                WHERE id = ?";
            $sth = $this->_conn->prepare($sql);
            $fields = array(
                'week', 
                'home_score', 
                'away_score', 
                'home_team_id', 
                'away_team_id', 
                'id'
            );
            $binds = array();

            foreach ($fields as $fieldName) {
                $field = $this->_map[$fieldName];
                $getProp = (string)$field->accessor;
                $binds[] = $game->$getProp();
            }

            $response = $sth->execute($binds);
        } catch(\PDOException $e) {
            echo "A database problem occurred: " . $e->getMessage(); 
        }
    }
}
