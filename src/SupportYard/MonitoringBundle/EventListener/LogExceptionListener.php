<?php

namespace SupportYard\MonitoringBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Templating\EngineInterface;

class LogExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param LoggerInterface $logger
     * @param EngineInterface $templating
     */
    public function __construct(LoggerInterface $logger, EngineInterface $templating)
    {
        $this->logger = $logger;
        $this->templating = $templating;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = FlattenException::create($event->getException());

        $content = $this->templating->render(
            'SupportYardMonitoringBundle::traceForLog.txt.twig',
            ['exception' => $exception]
        );

        $this->logger->info(
            htmlspecialchars_decode($content, ENT_QUOTES),
            [
                'metadata' => [],
                'description' => 'exception',
            ]
        );
    }
}
