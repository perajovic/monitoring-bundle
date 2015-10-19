<?php

namespace SupportYard\MonitoringBundle\Monolog;

use SupportYard\MonitoringBundle\Utils\ParametersToStringConverter;
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
        $params = ParametersToStringConverter::convert(
            is_array($query['params']) ? $query['params'] : []
        );

        $this->log(
            sprintf('%s; %sms; [%s]', $query['sql'], $queryTime, $params),
            [
                'metadata' => ['query_time' => $queryTime, 'query_time_unit' => 'ms'],
                'description' => 'single_query',
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
