<?php

namespace SupportYard\MonitoringBundle\Utils;

class ParametersToStringConverter
{
    /**
     * @param array $parameters
     *
     * @return string|null
     */
    public static function convert(array $parameters)
    {
        $bits = [];

        foreach ($parameters as $key => $value) {
            $value = is_string($value) ? $value : json_encode($value);
            $bits[] = sprintf('"%s": "%s"', $key, $value);
        }

        return !empty($bits) ? implode(', ', $bits) : null;
    }
}
