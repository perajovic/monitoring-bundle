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
        $this->logClientInfo($request);
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
            sprintf('Request: method = %s; url = %s', $method, $url),
            [
                'metadata' => ['HttpMethod' => $method, 'Url' => $url],
                'description' => 'request_info',
            ]
        );
    }

    /**
     * @param Request $request
     */
    private function logClientInfo(Request $request)
    {
        $userAgent = $request->headers->get('User-Agent');
        $ip = $request->getClientIp();

        $this->logger->info(
            sprintf('Client info: ip = %s; user agent = %s', $ip, $userAgent),
            [
                'metadata' => ['RemoteAddress' => $ip, 'HttpUserAgent' => $userAgent],
                'description' => 'client_info',
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