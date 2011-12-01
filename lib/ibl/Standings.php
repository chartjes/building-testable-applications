<?php

namespace IBL;

class Standings 
{
    protected $_games;
    protected $_franchiseMapper;

    public function __construct($games, $franchiseMapper)
    {
        $this->_games = $games; 
        $this->_franchiseMapper = $franchiseMapper;
    }

    public function generateBasic()
    {
        $rawData = $this->_calculate();
        $basicStandings = array();

        // Pull out only the fields we want
        foreach ($rawData as $conference => $divisions) {
            foreach ($divisions as $division => $teams) {
                foreach ($teams as $team) {
                    $franchise = $this->_franchiseMapper->findById($team['teamId']);    
                    $basicStandings[$conference][$division][] = array(
                        'teamId' => $team['teamId'],
                        'nickname' => $franchise->getNickname(),
                        'wins' => $team['wins'],
                        'losses' => $team['losses'],
                        'pct' => $team['pct'],
                        'gb' => $team['gb']
                    );
                }
            } 
        }
        
        return $basicStandings;
    }

    public function generateBreakdown()
    {
        return array();
    }
   
    public function generateExtended()
    {
        return array();
    }

    protected function _calculate() {
        // Initialize values for standings
        $franchises = $this->_franchiseMapper->findAll();
        $wins = array();
        $losses = array();

        foreach ($franchises as $franchise) {
            $wins[$franchise->getId()] = 0;
            $losses[$franchise->getId()] = 0;
        }

        foreach ($this->_games as $game) {
            if ($game->getHomeScore() > $game->getAwayScore()) {
                $wins[$game->getHomeTeamId()] += 1;
                $losses[$game->getAwayTeamId()] += 1;
            } else {
                $wins[$game->getAwayTeamId()] += 1;
                $losses[$game->getHomeTeamId()] += 1;
                         
            }
        }

        // Next we have to group them by conference and division
        $conferences = array('AC', 'NC');
        $divisions = array('East', 'Central', 'West');
        $standingsData = array();

        foreach ($conferences as $conference) {
            foreach ($divisions as $division) {
                $rawData = array();
                $franchises = $this->_franchiseMapper->findByConferenceDivision(
                    $conference,
                    $division
                );

                foreach ($franchises as $franchise) {
                    $winCount = $wins[$franchise->getId()];
                    $lossCount = $losses[$franchise->getId()];
                    $pct = $winCount / ($winCount + $lossCount);
                    $rawData[$franchise->getId()] = array(
                        'teamId' => $franchise->getId(),
                        'wins' => $winCount,
                        'losses' => $lossCount,
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

