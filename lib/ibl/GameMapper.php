<?php

class GameMapper 
{
    protected $conn;
    protected $map = array();

    public function __construct($conn)
    {
        $this->conn = $conn; 

        // Load our class mapper from the XML config file
        foreach (simplexml_load_file(APP_LIB . 'ibl/maps/game.xml') as $field) {
            $this->map[(string)$field->name] = $field; 
        }
    }

    public function save(\IBL\Game $game)
    {
        $sql = "INSERT INTO games (week, home_score, away_score, home_team_id, away_team_id) 
            VALUES(?, ?, ?, ?,?)";
        $sth = $this->conn->prepare($sql);
        $sth->execute(array($game->week, $game->home_score, $game->away_score, $game->home_team_id, $game->away_team_id));

        $newId = $this->conn->lastInsertId();

        if ($newId) {
            $game->setId($newId);
        } else {
            throw new Exception('DB Error: ' . $this->conn->errorMsg()); 
        }
    }
}
