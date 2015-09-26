<?php

namespace SupportYard\MonitoringBundle\Monolog;

use Monolog\Formatter\LineFormatter as BaseLineFormatter;

class LineFormatter extends BaseLineFormatter
{
    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function convertToString($data)
    {
        if (null === $data || is_scalar($data)) {
            return (string) $data;
        }

        if ((is_array($data) && !$data)
            || is_object($data)
            && !get_object_vars($data)
        ) {
            return '';
        }

        return $this->toJson($this->normalize($data));
    }
}
