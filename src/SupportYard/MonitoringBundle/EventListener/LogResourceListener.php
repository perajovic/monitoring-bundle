<?php

namespace SupportYard\MonitoringBundle\EventListener;

use Psr\Log\LoggerInterface;
use SupportYard\MonitoringBundle\Monolog\QueryExecution;
use Symfony\Component\HttpKernel\KernelInterface;

class LogResourceListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QueryExecution
     */
    private $queryExecution;

    /**
     * @var float
     */
    private $startTime;

    /**
     * @param LoggerInterface $logger
     * @param QueryExecution  $queryExecution
     * @param KernelInterface $kernel
     */
    public function __construct(
        LoggerInterface $logger,
        QueryExecution $queryExecution,
        KernelInterface $kernel
    ) {
        $this->logger = $logger;
        $this->queryExecution = $queryExecution;
        $this->startTime = $kernel->getStartTime();
    }

    public function onKernelTerminate()
    {
        $requestTime = $this->getExecutionTime();
        $queryTime = $this->queryExecution->getTotalTime();
        $queryCount = $this->queryExecution->getCount();
        $memory = $this->getMemoryUsage();

        $this->logger->notice(
            sprintf(
                'Resources: request time = %sms; query time = %sms;'
                .' query count = %s; memory = %sMB',
                $requestTime,
                $queryTime,
                $queryCount,
                $memory
            ),
            [
                'metadata' => [
                    'RequestTime' => $requestTime,
                    'RequestTimeUnit' => 'ms',
                    'QueryTotalTime' => $queryTime,
                    'QueryTotalTimeUnit' => 'ms',
                    'QueryCount' => $queryCount,
                    'MemoryUsage' => $memory,
                    'MemoryUsageUnit' => 'MB',
                ],
                'description' => 'request_resources',
            ]
        );
    }

    /**
     * @return float
     */
    private function getExecutionTime()
    {
        return round((microtime(true) - $this->startTime) * 1000, 3);
    }

    /**
     * @return float
     */
    private function getMemoryUsage()
    {
        return memory_get_peak_usage(true) / 1024 / 1024;
    }
}
