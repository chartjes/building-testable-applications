<?php

// Base class for our Game model
namespace IBL;

class Game extends BaseModel
{
    protected $week;
    protected $home_score;
    protected $away_score;
    protected $home_team_id;
    protected $away_team_id;

    public function getMaxWeek()
    {
        return 0;         
    }
}
