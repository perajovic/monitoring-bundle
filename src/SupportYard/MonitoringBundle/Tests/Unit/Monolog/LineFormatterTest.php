<?php

namespace SupportYard\MonitoringBundle\Tests\Unit\Monolog;

use SupportYard\FrameworkBundle\Test\TestCase;
use DateTime;
use stdClass;
use SupportYard\MonitoringBundle\Monolog\LineFormatter;

class LineFormatterTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideData
     */
    public function extrasAreFormattedRegardingToContent($extra, $expected)
    {
        $data = [
            'level_name' => 'WARNING',
            'channel' => 'log',
            'context' => [],
            'message' => 'msg',
            'datetime' => new DateTime(),
            'extra' => $extra,
        ];

        $message = $this->formatter->format($data);

        $this->assertStringEndsWith($expected, $message);
    }

    public function provideData()
    {
        return [
            [['null'], "[\"null\"]\n"],
            [['baz'], "[\"baz\"]\n"],
            [['foo' => 'bar'], "{\"foo\":\"bar\"}\n"],
            [[new stdClass()], "[\"[object] (stdClass: {})\"]\n"],
            [['bar' => new stdClass()], "{\"bar\":\"[object] (stdClass: {})\"}\n"],
            [[], "\n"],
        ];
    }

    protected function setUp()
    {
        $this->formatter = new LineFormatter();
    }
}
