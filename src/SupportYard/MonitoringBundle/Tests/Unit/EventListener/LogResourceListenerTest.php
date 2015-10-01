<?php

namespace SupportYard\MonitoringBundle\Tests\Unit\EventListener;

use SupportYard\FrameworkBundle\Test\EventListenerTestCase;
use SupportYard\MonitoringBundle\EventListener\LogResourceListener;

class LogResourceListenerTest extends EventListenerTestCase
{
    /**
     * @test
     */
    public function resourceDataIsLogged()
    {
        $queryTotalTime = 123;
        $queryCount = 5;

        $this->ensureQueryTotalTime($queryTotalTime);
        $this->ensureQueryCount($queryCount);
        $this->ensureResourceDataIsLogged($queryTotalTime, $queryCount);

        $this->listener->onKernelTerminate();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->logger = $this->createLogger();
        $this->queryExecution = $this->createQueryExecution();
        $this->kernel = $this->createKernel();
        $this->listener = new LogResourceListener(
            $this->logger,
            $this->queryExecution,
            $this->kernel
        );
    }

    private function ensureResourceDataIsLogged($queryTotalTime, $queryCount)
    {
        $this
            ->logger
            ->expects($this->once())
            ->method('notice')
            ->with(
                $this->stringContains('Resources:'),
                $this->callback(function ($data) use ($queryTotalTime, $queryCount) {
                    $metadata = $data['metadata'];

                    $this->assertArrayHasKey('RequestTime', $metadata);
                    $this->assertSame('ms', $metadata['RequestTimeUnit']);
                    $this->assertSame($queryTotalTime, $metadata['QueryTotalTime']);
                    $this->assertSame('ms', $metadata['QueryTotalTimeUnit']);
                    $this->assertSame($queryCount, $metadata['QueryCount']);
                    $this->assertArrayHasKey('MemoryUsage', $metadata);
                    $this->assertSame('MB', $metadata['MemoryUsageUnit']);
                    $this->assertSame('request_resources', $data['description']);

                    return $data;
                })
            );
    }

    private function ensureQueryTotalTime($time)
    {
        $this
            ->queryExecution
            ->expects($this->once())
            ->method('getTotalTime')
            ->will($this->returnValue($time));
    }

    private function ensureQueryCount($count)
    {
        $this
            ->queryExecution
            ->expects($this->once())
            ->method('getCount')
            ->will($this->returnValue($count));
    }

    private function createKernel()
    {
        return $this->createMockFor('Symfony\Component\HttpKernel\KernelInterface');
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
