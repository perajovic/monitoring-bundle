<?php

namespace SupportYard\MonitoringBundle\Tests\Unit\EventListener;

use SupportYard\FrameworkBundle\Test\EventListenerTestCase;
use Exception;
use SupportYard\MonitoringBundle\EventListener\LogExceptionListener;

class LogExceptionListenerTest extends EventListenerTestCase
{
    /**
     * @test
     */
    public function exceptionIsLogged()
    {
        $content = 'Foo template';
        $exception = new Exception('Foo');

        $this->ensureException($exception);
        $this->ensureTemplateContent($content);
        $this->ensureDataIsLogged($content);

        $this->listener->onKernelException($this->event);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->event = $this->createGetResponseForExceptionEvent();
        $this->logger = $this->createLogger();
        $this->templating = $this->createTemplating();
        $this->listener = new LogExceptionListener($this->logger, $this->templating);
    }

    private function ensureDataIsLogged($content)
    {
        $this
            ->logger
            ->expects($this->once())
            ->method('info')
            ->with($content, ['metadata' => [], 'description' => 'exception_info']);
    }

    private function ensureTemplateContent($content)
    {
        $this
            ->templating
            ->expects($this->once())
            ->method('render')
            ->with(
                'SupportYardMonitoringBundle::traceForLog.txt.twig',
                $this->callback(function ($data) {
                    $this->assertInstanceOf(
                        'Symfony\Component\Debug\Exception\FlattenException',
                        $data['exception']
                    );

                    return $data;
                })
            )
            ->will($this->returnValue($content));
    }

    private function ensureException($exception)
    {
        $this
            ->event
            ->expects($this->once())
            ->method('getException')
            ->will($this->returnValue($exception));
    }

    private function createLogger()
    {
        return $this->createMockFor('Psr\Log\LoggerInterface');
    }

    private function createTemplating()
    {
        return $this->createMockFor('Symfony\Component\Templating\EngineInterface');
    }
}
