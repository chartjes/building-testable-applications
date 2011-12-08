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
            foreach ($games as $game) {
                if ($game->getWeek() <= $week) {
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

    public function generatePlayoff()
    {
        $regularStandings = $this->generateRegular();
        $leaders = array();
        $magicNumber = array();
        $wildCard = array();
        $lead = array();

        foreach ($regularStandings as $conference => $confTeams) {
            foreach ($confTeams as $division => $teams) {
                $leader = array_slice($teams, 0, 1);
                $secondTeam = array_slice($teams, 1, 1);
                $leaders[$conference][$division] = $leader[0];
                $x = $leader[0]['wins'];
                $y = $secondTeam[0]['losses'];
                $magicNumber[$conference][$division] = $secondTeam[0]['gb'];

                if ($magicNumber[$conference][$division] <= 0) {
                    $magicNumber[$conference][$division] = 'Clinched';
                }

                $lead[$conference][$division] = $secondTeam[0]['gb'];

                // Add the teams that are not division leaders to 
                // the list of wild card teams
                $chaseTeams = array_slice($teams, 1);

                foreach ($chaseTeams as $team) {
                    $wildCard[$conference][] = $team; 
                }

            } 
        }

        // Sort all our teams in order of winning percentage
        // so we can figure out who the wild card leaders are        
        foreach ($wildCard as $conference => $teamStandings) {
            $pct = array();
            $wins = array();
            $losses = array();
            $firstWins = 0;
            $firstLosses = 0;
            $secondWins = 0;
            $secondLosses = 0;
            $firstGb = array();
            $secondGb = array();
            $firstWc = "";
            $secondWc = "";

            foreach ($teamStandings as $teamStanding) {
                $pct[$teamStanding['teamId']] = $teamStanding['pct']; 
                $wins[$teamStanding['teamId']] = $teamStanding['wins'];
                $losses[$teamStanding['teamId']] = $teamStanding['losses'];
            }

            arsort($pct);

            // Build our result array of where a team sits in the
            // wild card races
            foreach ($pct as $team => $tPct) {
                if (!$firstWc) {
                    $firstWc = $team;
                    $firstGb[$team] = '--';
                    $secondGb[$team] = '--';
                    $firstWins = $wins[$team];
                    $firstLosses = $losses[$team];
                } else {
                    if (!$secondWc) {
                        $secondWc = $team;
                        $secondGb[$team] = '--';
                        $firstGb[$team] = (
                            ($firstWins - $wins[$team]) +
                            ($losses[$team] - $firstLosses))
                            / 2;
                        $secondW = $wins[$team];
                        $secondL = $wins[$team];
                    } else {
                        $firstGb[$team] = (
                            ($firstWins - $wins[$team]) +
                            ($losses[$team] - $firstLosses))
                            / 2;
                        $secondGb[$team] = (
                            ($secondWins - $wins[$team]) +
                            ($losses[$team] - $secondLosses))
                            / 2;
                    }

                    if ($firstGb[$team] == 0) {
                        $firstGb[$team] = '--'; 
                    }

                    if ($secondGb[$team] == 0) {
                        $secondGb[$team] = '--'; 
                    }
                }
            }

            $wildCardStandings[$conference] = array();

            foreach ($pct as $team => $tPct) {
                $wildCardStandings[$conference][] = array(
                    $team,
                    $wins[$team],
                    $losses[$team],
                    $pct[$team],
                    $firstGb[$team],
                    $secondGb[$team]
                ); 
            }
        }

        return array(
            'leaders' => $leaders,
            'wildCardStandings' => $wildCardStandings,
            'magicNumber' => $magicNumber,
            'lead' => $lead
        );
    }

    public function generateRegular()
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

                    $rawData[$conference][$division][$teamId] = array(
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
                }
            }
        }

        return $this->_sort($rawData);
    }

    protected function _sort($standingsData)
    {
        $newStandingsData = array();

        foreach ($standingsData as $conference => $confTeams) {
            foreach ($confTeams as $division => $teams) {
                // Sort teams by winning percentage in their division
                $sortedData = $teams;
                $column = array();

                foreach ($sortedData as $tmp) {
                    $column[] = $tmp['pct'];
                }

                array_multisort($column, SORT_DESC, $sortedData);
                $tmp = $sortedData;

                // Determine how many games teams are behind the leader
                $leader = true;

                foreach ($tmp as $idx => $info) {
                    $teamId = $info['teamId'];
                    if ($leader == true) {
                        $leader = false;
                        $sortedData[$idx]['gb'] 
                            = '--';
                        $leadW = $info['wins']; 
                        $leadL = $info['losses'];
                    } else {
                        $x = $leadW - $info['wins'];
                        $y = $info['losses'] - $leadL;
                        $sortedData[$idx]['gb'] 
                            = ($x + $y) / 2;
                    }
                }

                foreach ($sortedData as $team) {
                    $newStandingsData[$conference][$division][] = $team; 
                }
            }
        }

        return $newStandingsData;
    }
}

