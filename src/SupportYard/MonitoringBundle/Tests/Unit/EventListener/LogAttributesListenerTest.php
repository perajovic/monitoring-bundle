<?php

namespace SupportYard\MonitoringBundle\Tests\Unit\EventListener;

use SupportYard\MonitoringBundle\EventListener\LogAttributesListener;
use SupportYard\FrameworkBundle\Test\EventListenerTestCase;

class LogAttributesListenerTest extends EventListenerTestCase
{
    /**
     * @test
     */
    public function listenerIsStoppedForSubRequest()
    {
        $this->ensureIsNotMasterRequest();

        $this->listener->onKernelController($this->event);
    }

    /**
     * @test
     */
    public function attributesAreLogged()
    {
        $attributes = ['x' => 'y'];

        $this->ensureIsMasterRequest();
        $this->ensureRequest();
        $this->ensureAttributes($attributes);
        $this->ensureAttributesAreLogged($attributes);

        $this->listener->onKernelController($this->event);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->logger = $this->createLogger();
        $this->request = $this->createRequest();
        $this->attributesBag = $this->createParameterBag();
        $this->event = $this->createFilterControllerEvent();
        $this->listener = new LogAttributesListener($this->logger);
    }

    protected function createRequest()
    {
        return $this->createMockFor('Symfony\Component\HttpFoundation\Request');
    }

    private function ensureAttributes($attributes)
    {
        $this->request->attributes = $this->attributesBag;

        $this
            ->attributesBag
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue($attributes));
    }

    private function ensureAttributesAreLogged($attributes)
    {
        $this
            ->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Attributes are logged.',
                [
                    'metadata' => ['attributes' => $attributes],
                    'description' => 'attributes',
                ]
            );
    }

    private function createLogger()
    {
        return $this->createMockFor('Psr\Log\LoggerInterface');
    }

    private function createParameterBag()
    {
        return $this->createMockFor('Symfony\Component\HttpFoundation\ParameterBag');
    }
}
