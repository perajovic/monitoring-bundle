<?php

namespace SupportYard\MonitoringBundle\Monolog;

use Symfony\Bridge\Doctrine\Logger\DbalLogger as DoctrineDbalLogger;

class DbalLogger extends DoctrineDbalLogger
{
    /**
     * @var QueryExecution
     */
    private $queryExecution;

    /**
     * @var array
     */
    private $queries = [];

    /**
     * @var float|null
     */
    private $start = null;

    /**
     * @var int
     */
    private $currentQuery = 0;

    /**
     * @param QueryExecution $queryExecution
     */
    public function setQueryExecution(QueryExecution $queryExecution)
    {
        $this->queryExecution = $queryExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->start = microtime(true);
        $this->queries[++$this->currentQuery] = ['sql' => $sql, 'params' => $params];
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $queryTime = $this->getQueryTime();

        if ($this->queryExecution) {
            $this->queryExecution->recordTime($queryTime);
            $this->queryExecution->incrementCounter();
        }

        $query = $this->queries[$this->currentQuery];

        $this->log(
            sprintf('%s; %sms', $query['sql'], $queryTime),
            [
                'metadata' => [
                    'query_time' => $queryTime,
                    'query_time_unit' => 'ms',
                    'params' => is_array($query['params']) ? $query['params'] : [],
                ],
                'description' => 'query',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function log($message, array $context)
    {
        $this->logger->info($message, $context);
    }

    /**
     * @return float
     */
    private function getQueryTime()
    {
        return round((microtime(true) - $this->start) * 1000, 3);
    }
}
