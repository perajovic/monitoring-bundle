<?php

namespace SupportYard\MonitoringBundle\Monolog;

use Monolog\Handler\SocketHandler as MonologSocketHandler;

class SocketHandler extends MonologSocketHandler
{
    /**
     * {@inheritdoc}
     */
    protected function generateDataStream($record)
    {
        unset($record['formatted']);

        return json_encode($record)."\n";
    }
}
