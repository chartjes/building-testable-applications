<?php

namespace IBL;

class ScheduleMapper
{
    public $scheduleTable;
    public $teamsTable;
    protected $_conn;
    protected $_map = array();

    public function __construct($conn = null)
    {
        if ($conn !== null) {
            $this->_conn = $conn; 
        }

        // Load our class mapper from the XML config file
        $fields = simplexml_load_file(LIB_ROOT . 'ibl/maps/schedule.xml');

        foreach ($fields as $field) {
            $this->_map[(string)$field->name] = $field; 
        }

        $this->scheduleTable = 'sched2010';
        $this->teamsTable = 'teams2010';
    }

    public function createScheduleFromRow($row)
    {
        $schedule = new \IBL\Schedule($this);

        foreach ($this->_map as $field) {
            $setProp = (string)$field->mutator;
            $value = trim($row[(string)$field->name]);

            if ($setProp && $value) {
                call_user_func(array($schedule, $setProp), $value); 
            } 
        } 

        return $schedule;
    }

    public function findByWeek($week)
    {
        try {
            $sql = "SELECT * FROM {$this->scheduleTable} WHERE week = ?";
            $sth = $this->_conn->prepare($sql);
            $sth->execute(array((int) $week));
            $rows = $sth->fetchAll();
            $schedules = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $schedules[] = $this->createScheduleFromRow($row); 
                } 
            }

            return $schedules;
        } catch (\PDOException $e) {
            throw new \Exception("DB Error: " . $e->getMessage()); 
        } 
    }

    public function generate($rawSchedules, $franchiseMap)
    {
        $schedules = array();

        foreach ($rawSchedules as $schedule) {
            $homeTeam = $franchiseMap[$schedule->getHome()];
            $awayTeam = $franchiseMap[$schedule->getAway()];

            $schedules[$homeTeam] = array(
                'home' => $homeTeam,
                'away' => $awayTeam,
            ); 
        }

        ksort($schedules);

        return $schedules;
    }
}

