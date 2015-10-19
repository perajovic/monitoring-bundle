<?php

namespace SupportYard\MonitoringBundle\Tests\Unit\Monolog;

use SupportYard\FrameworkBundle\Test\TestCase;
use SupportYard\MonitoringBundle\Monolog\DbalLogger;

class DbalLoggerTest extends TestCase
{
    /**
     * @test
     */
    public function queryIsLogged()
    {
        $sql1 = 'SELECT * FROM foo';
        $params1 = null;
        $sql2 = 'SELECT * FROM bar';
        $params2 = ['foo' => 'bar'];

        $this->ensureExecutionTimeIsRecorded();
        $this->ensureCounterIsIncremented();
        $this->ensureMessageIsLogged(0, $sql1, $params1);
        $this->ensureMessageIsLogged(1, $sql2, $params2);

        $this->dbalLogger->startQuery($sql1, $params1);
        $this->dbalLogger->stopQuery();

        $this->dbalLogger->startQuery($sql2, $params2);
        $this->dbalLogger->stopQuery();
    }

    /**
     * @test
     */
    public function queryExecutionIsOptional()
    {
        $sql1 = 'SELECT * FROM foo';
        $params1 = null;
        $sql2 = 'SELECT * FROM bar';
        $params2 = ['foo' => 'bar'];
        $dbalLogger = new DbalLogger($this->logger);

        $this->ensureExecutionTimeIsNotRecorded();
        $this->ensureCounterIsNotIncremented();
        $this->ensureMessageIsLogged(0, $sql1, $params1);
        $this->ensureMessageIsLogged(1, $sql2, $params2);

        $dbalLogger->startQuery($sql1, $params1);
        $dbalLogger->stopQuery();

        $dbalLogger->startQuery($sql2, $params2);
        $dbalLogger->stopQuery();
    }

    protected function setUp()
    {
        $this->queryExecution = $this->createQueryExecution();
        $this->logger = $this->createLogger();
        $this->dbalLogger = new DbalLogger($this->logger);
        $this->dbalLogger->setQueryExecution($this->queryExecution);
    }

    private function ensureMessageIsLogged($index, $sql, $params)
    {
        $this
            ->logger
            ->expects($this->at($index))
            ->method('info')
            ->with(
                $this->stringContains($sql),
                $this->callback(function ($data) use ($params) {
                    $this->assertArrayHasKey('query_time', $data['metadata']);
                    $this->assertSame('ms', $data['metadata']['query_time_unit']);
                    $this->assertSame('single_query', $data['description']);

                    return $data;
                })
            );
    }

    private function ensureCounterIsNotIncremented()
    {
        $this
            ->queryExecution
            ->expects($this->never())
            ->method('incrementCounter');
    }

    private function ensureExecutionTimeIsNotRecorded()
    {
        $this
            ->queryExecution
            ->expects($this->never())
            ->method('recordTime');
    }

    private function ensureCounterIsIncremented()
    {
        $this
            ->queryExecution
            ->expects($this->exactly(2))
            ->method('incrementCounter');
    }

    private function ensureExecutionTimeIsRecorded()
    {
        $this
            ->queryExecution
            ->expects($this->exactly(2))
            ->method('recordTime')
            ->with($this->greaterThan(0));
    }

    private function createLogger()
    {
        return $this->createMockFor('Psr\Log\LoggerInterface');
    }

    private function createQueryExecution()
    {
        return $this->createMockFor(
            'SupportYard\MonitoringBundle\Monolog\QueryExecution'
        );
    }
}
