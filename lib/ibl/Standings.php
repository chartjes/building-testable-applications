<?php
/**
 * Model for generating standards
 */
namespace IBL;

class Standings
{
    protected $_conferences = array();
    protected $_divisions = array();
    protected $_franchises = array();
    protected $_games = array();
    protected $_names = array();
    protected $_nicknames = array();

    public function __construct($games, $franchises, $week = null)
    {
        if ($week === null) {
            $this->_games = $games; 
        } else {
            foreach ($this->_games as $game) {
                if ($game->week <= $week) {
                    $this->_games[] = $game; 
                }
            } 
        }

        $this->_franchises = $franchises;

        foreach ($franchises as $franchise) {
            $id = $franchise->getId();
            $this->_conferences[$id] = $franchise->getConference();
            $this->_divisions[$id] = $franchise->getDivision();
            $this->_names[$id] = $franchise->getName();
            $this->_nicknames[$id] = $franchise->getNickname();
        }
    }

    public function generateBreakdown()
    {
        $wins = array();
        $losses = array();
        $standingsData = array();

        // Initialize all our variables, except we don't need to set
        // any values where the home team and road team are equal
        foreach ($this->_franchises as $awayTeam) {
            foreach ($this->_franchises as $homeTeam) {
                if ($awayTeam->getId() !== $homeTeam->getId()) {
                    $wins[$awayTeam->getId()][$homeTeam->getId()] = 0;
                    $losses[$awayTeam->getId()][$homeTeam->getId()] = 0; 
                }
            } 
        }

        // Calculate the breakdown wins and losses
        foreach ($this->_games as $game) {
            $homeTeamId = $game->getHomeTeamId();
            $awayTeamId = $game->getAwayTeamId();

            if ($game->getHomeScore() > $game->getAwayScore()) {
                $wins[$homeTeamId][$awayTeamId]++;
                $losses[$awayTeamId][$homeTeamId]++;
            } else {
                $wins[$awayTeamId][$homeTeamId]++;
                $losses[$homeTeamId][$awayTeamId]++;
            } 
        }        

        // Sort the arrays so that they match up nicely
        arsort($wins);
        arsort($losses);

        return array(
            'wins' => $wins,
            'losses' => $losses 
        );
    }

    public function generatePlayoff($week = null)
    {
        return array(); 
    }

    public function generateRegular($week = null)
    {
        $wins = array();
        $losses = array();
        $homeWins = array();
        $homeLosses = array();
        $awayWins = array();
        $awayLosses = array();
        $confWins = array();
        $confLosses = array();
        $divWins = array();
        $divLosses = array();

        // Initialize all our standings variables
        foreach ($this->_conferences as $teamId => $value) {
            $wins[$teamId] = 0;
            $losses[$teamId] = 0;
            $homeWins[$teamId] = 0;
            $homeLosses[$teamId] = 0;
            $awayWins[$teamId] = 0;
            $awayLosses[$teamId] = 0;
            $confWins[$teamId] = 0;
            $confLosses[$teamId] = 0;
            $divWins[$teamId] = 0;
            $divLosses[$teamId] = 0; 
        }

        // Now loop through each game
        foreach ($this->_games as $game) {
            $homeTeamId = $game->getHomeTeamId();
            $awayTeamId = $game->getAwayTeamId();

            if ($game->getHomeScore() > $game->getAwayScore()) {
                $wins[$homeTeamId]++;
                $losses[$awayTeamId]++;
                $homeWins[$homeTeamId]++;
                $awayLosses[$awayTeamId]++;

                if ($this->_conferences[$homeTeamId] == $this->_conferences[$awayTeamId]) { 
                    $confWins[$homeTeamId]++;
                    $confLosses[$awayTeamId]++;
                }

                if ($this->_divisions[$homeTeamId] == $this->_divisions[$awayTeamId]) {
                    $divWins[$homeTeamId]++;
                    $divLosses[$awayTeamId]++; 
                }
            } else {
                $wins[$awayTeamId] += 1;
                $losses[$homeTeamId] += 1;
                $awayWins[$awayTeamId] += 1;
                $homeLosses[$homeTeamId] += 1;

                if ($this->_conferences[$homeTeamId] == $this->_conferences[$awayTeamId]) {
                    $confWins[$awayTeamId]++;
                    $confLosses[$homeTeamId]++;
                }

                if ($this->_divisions[$homeTeamId] == $this->_divisions[$awayTeamId]) {
                    $divWins[$awayTeamId]++;
                    $divLosses[$homeTeamId]++; 
                }
            }
        }

        // Build our standings result, grouping teams by conference
        // and division
        $standingsData = array();
        $conferences = array('AC', 'NC');
        $divisions = array('East', 'Central', 'West');

        foreach ($conferences as $conference) {
            foreach ($divisions as $division) {
                // Figure out what teams belong to this combo of conference
                // and division
                $confTeams = array_keys($this->_conferences, $conference);
                $divisionTeams = array_keys($this->_divisions, $division);
                $teams = array_intersect($confTeams, $divisionTeams);

                foreach ($teams as $teamId) {
                    $winCount = $wins[$teamId];
                    $lossCount = $losses[$teamId];

                    if (($winCount + $lossCount) != 0) {
                        $pct = $winCount / ($winCount + $lossCount);
                    } else {
                        $pct = 0; 
                    }
                    
                    $rawData[$teamId] = array(
                        'teamId' => $teamId,
                        'nickname' => $this->_nicknames[$teamId],
                        'name' => $this->_names[$teamId],
                        'wins' => $winCount,
                        'losses' => $lossCount,
                        'homeWins' => $homeWins[$teamId],
                        'homeLosses' => $homeLosses[$teamId],
                        'awayWins' => $awayWins[$teamId],
                        'awayLosses' => $awayLosses[$teamId],
                        'confWins' => $confWins[$teamId],
                        'confLosses' => $confLosses[$teamId],
                        'divWins' => $divWins[$teamId],
                        'divLosses' => $divLosses[$teamId],
                        'pct' => $pct
                    );
                    $sortedData = $this->_sort($rawData);
                    $standingsData[$conference][$division] = $sortedData;
                }
            }
        }

        return $standingsData;
    }

    protected function _sort($standingsData)
    {
        // Sort by winning percentage
        $column = array();

        foreach ($standingsData as $tmp) {
            $column[] = $tmp['pct']; 
        }

        array_multisort($column, SORT_DESC, $standingsData);

        // Determine how many games teams are behind the leader
        $leader = true;

        foreach ($standingsData as $teamId => $info) {
            if ($leader == true) {
                $leader = false;
                $standingsData[$teamId]['gb'] = '--';
                $leadW = $info['wins']; 
                $leadL = $info['losses'];
            } else {
                $factor1 = $leadW - $info['wins'];
                $factor2 = $info['losses'] - $leadL;
                $standingsData[$teamId]['gb'] = ($factor1 + $factor2) / 2;
            }
        }

        return $standingsData;
    }
}

