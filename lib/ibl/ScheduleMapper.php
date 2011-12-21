<?php

namespace IBL;

class ScheduleMapper
{
    protected $_conn;
    protected $_scheduleTable;
    protected $_teamsTable;
    protected $_map = array();

    public function __construct($_conn)
    {
        $this->_conn = $_conn; 

        // Load our class mapper from the XML config file
        $fields = simplexml_load_file(LIB_ROOT . 'ibl/maps/schedule.xml');

        foreach ($fields as $field) {
            $this->_map[(string)$field->name] = $field; 
        }

        $this->_scheduleTable = 'sched2010';
        $this->_teamsTable = 'teams2010';
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
            $sql = "SELECT * FROM {$this->_scheduleTable} WHERE week = ?";
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

    public function generateByWeek($week)
    {
        $franchiseMap = array();
        $rawSchedules = $this->findByWeek($week);
        $schedules = array();

        /**
         * Due to other dumb mistakes we mapped things in a dumb way compared
         * to the Franchises table. So every year we have to manually alter
         * the mapping table every season. One of these days we'll come up 
         * with a better solution.
         */
        $sql = "SELECT * FROM {$this->_teamsTable}";
        $sth = $this->_conn->prepare($sql);
        $sth->execute();
        $rows = $sth->fetchAll();

        foreach ($rows as $row) {
            $franchiseMap[$row['code']] = $row['ibl']; 
        }

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

