<?php

namespace SupportYard\MonitoringBundle\EventListener;

use Psr\Log\LoggerInterface;
use SupportYard\MonitoringBundle\Utils\ParametersToStringConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

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
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
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
        $remoteAddress = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');

        $this->logger->info(
            sprintf(
                'Request: method = %s; url = %s; remote_address= %s; http_user_agent = %s',
                $method,
                $url,
                $remoteAddress,
                $userAgent
            ),
            [
                'metadata' => [
                    'http_method' => $method,
                    'url' => $url,
                    'remote_address' => $remoteAddress,
                    'http_user_agent' => $userAgent,
                ],
                'description' => 'request_info',
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
            ['description' => 'get_payload']
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
            ['description' => 'post_payload']
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
            ['description' => 'files_payload']
        );
    }
}
