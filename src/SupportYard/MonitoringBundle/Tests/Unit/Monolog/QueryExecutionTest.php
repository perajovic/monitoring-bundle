<?php

namespace SupportYard\MonitoringBundle\Tests\Unit\Monolog;

use Codecontrol\FrameworkBundle\Test\TestCase;
use SupportYard\MonitoringBundle\Monolog\QueryExecution;

class QueryExecutionTest extends TestCase
{
    /**
     * @test
     */
    public function checkInitialState()
    {
        $this->assertSame(0, $this->queryExecution->getTotalTime());
        $this->assertSame(0, $this->queryExecution->getCount());
    }

    /**
     * @test
     */
    public function timeIsRecorded()
    {
        $this->queryExecution->recordTime(1);
        $this->queryExecution->recordTime(2);

        $this->assertSame(3, $this->queryExecution->getTotalTime());
    }

    /**
     * @test
     */
    public function counterIsIncremented()
    {
        $this->queryExecution->incrementCounter();
        $this->queryExecution->incrementCounter();

        $this->assertSame(2, $this->queryExecution->getCount());
    }

    protected function setUp()
    {
        $this->queryExecution = new QueryExecution();
    }
}
