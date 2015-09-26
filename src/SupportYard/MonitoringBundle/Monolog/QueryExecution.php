<?php

namespace SupportYard\MonitoringBundle\Monolog;

class QueryExecution
{
    /**
     * @var float
     */
    private $time;

    /**
     * @var int
     */
    private $count;

    public function __construct()
    {
        $this->time = 0;
        $this->count = 0;
    }

    /**
     * @return float
     */
    public function getTotalTime()
    {
        return $this->time;
    }

    /**
     * @param float $time
     */
    public function recordTime($time)
    {
        $this->time += $time;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    public function incrementCounter()
    {
        ++$this->count;
    }
}
