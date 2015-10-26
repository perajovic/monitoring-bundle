<?php

namespace SupportYard\MonitoringBundle\EventListener;

use Psr\Log\LoggerInterface;
use SupportYard\MonitoringBundle\Utils\ParametersToStringConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LogEnvListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $this->logRequest($request);
        $this->logGet($request);
        $this->logPost($request);
        $this->logFiles($request);
    }

    /**
     * @param Request $request
     */
    private function logRequest(Request $request)
    {
        $method = $request->getRealMethod();
        $url = $request->getPathInfo();

        $this->logger->info(
            sprintf('Request %s %s', $method, $url),
            [
                'metadata' => [
                    'http_method' => $method,
                    'url' => $url,
                    'hostname' => gethostname(),
                    'remote_address' => $request->getClientIp(),
                    'http_user_agent' => $request->headers->get('User-Agent'),
                    'headers' => $request->headers->all(),
                ],
                'description' => 'request',
            ]
        );
    }

    /**
     * @param Request $request
     */
    private function logGet(Request $request)
    {
        $data = $request->query->all();

        if (!$data) {
            return;
        }

        $this->logger->info(
            sprintf('$_GET = %s', ParametersToStringConverter::convert($data)),
            [
                'metadata' => ['data' => $data],
                'description' => 'get',
            ]
        );
    }

    /**
     * @param Request $request
     */
    private function logPost(Request $request)
    {
        $data = $request->request->all();

        if (!$data) {
            return;
        }

        $this->logger->info(
            sprintf('$_POST = %s', ParametersToStringConverter::convert($data)),
            [
                'metadata' => ['data' => $data],
                'description' => 'post',
            ]
        );
    }

    /**
     * @param Request $request
     */
    private function logFiles(Request $request)
    {
        $data = $request->files->all();

        if (!$data) {
            return;
        }

        $this->logger->info(
            sprintf('$_FILES = %s', ParametersToStringConverter::convert($data)),
            [
                'metadata' => ['data' => $data],
                'description' => 'files',
            ]
        );
    }
}
