<?php

namespace SupportYard\MonitoringBundle\Tests\Unit\Utils;

use SupportYard\FrameworkBundle\Test\TestCase;
use stdClass;
use SupportYard\MonitoringBundle\Utils\ParametersToStringConverter;

class ParametersToStringConverterTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideData
     */
    public function parametersAreConvertedToString($parameters, $expected)
    {
        $actual = ParametersToStringConverter::convert($parameters);

        $this->assertSame($expected, $actual);
    }

    public function provideData()
    {
        return [
            [[], null],
            [['foo' => new stdClass()], '"foo": "{}"'],
            [['foo' => 'bar'], '"foo": "bar"'],
        ];
    }
}
