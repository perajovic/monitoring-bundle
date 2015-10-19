<?php

namespace SupportYard\MonitoringBundle\Tests\Unit\EventListener;

use SupportYard\FrameworkBundle\Test\EventListenerTestCase;
use stdClass;
use SupportYard\MonitoringBundle\EventListener\LogEnvListener;

class LogEnvListenerTest extends EventListenerTestCase
{
    /**
     * @test
     */
    public function loggingEmptyPostAndGetAndFilesArraysAreSkipped()
    {
        $httpMethod = 'GET';
        $url = '/_test';
        $userAgent = 'Test User Agent';
        $ip = '127.0.0.1';
        $getData = [];
        $postData = [];
        $filesData = [];

        $this->ensureRequest();

        $this->ensureHttpMethod($httpMethod);
        $this->ensureUrl($url);
        $this->ensureUserAgent($userAgent);
        $this->ensureIp($ip);
        $this->ensureGetData($getData);
        $this->ensurePostData($postData);
        $this->ensureFilesData($filesData);

        $this->ensureLoggerIsCalledExactly(2);

        $this->listener->onKernelTerminate($this->event);
    }

    /**
     * @test
     */
    public function envDataIsLogged()
    {
        $httpMethod = 'GET';
        $url = '/_test';
        $userAgent = 'Test User Agent';
        $ip = '127.0.0.1';
        $getData = ['foo' => 'bar'];
        $postData = ['foo' => new stdClass()];
        $filesData = ['bar' => 'baz'];

        $this->ensureRequest();

        $this->ensureHttpMethod($httpMethod);
        $this->ensureUrl($url);
        $this->ensureUserAgent($userAgent);
        $this->ensureIp($ip);
        $this->ensureGetData($getData);
        $this->ensurePostData($postData);
        $this->ensureFilesData($filesData);

        $this->ensureRequestIsLogged($httpMethod, $url);
        $this->ensureClientInfoIsLogged($userAgent, $ip);
        $this->ensureGetIsLogged();
        $this->ensurePostIsLogged();
        $this->ensureFilesIsLogged();

        $this->listener->onKernelTerminate($this->event);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->headerBag = $this->createHeaderBag();
        $this->queryBag = $this->createParameterBag();
        $this->requestBag = $this->createParameterBag();
        $this->fileBag = $this->createFileBag();
        $this->request = $this->createRequest();
        $this->event = $this->createPostResponseEvent();
        $this->logger = $this->createLogger();
        $this->listener = new LogEnvListener($this->logger);
    }

    protected function createRequest()
    {
        return $this->createMockFor('Symfony\Component\HttpFoundation\Request');
    }

    private function ensureHttpMethod($method)
    {
        $this
            ->request
            ->expects($this->once())
            ->method('getRealMethod')
            ->will($this->returnValue($method));
    }

    private function ensureUrl($url)
    {
        $this
            ->request
            ->expects($this->once())
            ->method('getPathInfo')
            ->will($this->returnValue($url));
    }

    private function ensureIp($ip)
    {
        $this
            ->request
            ->expects($this->once())
            ->method('getClientIp')
            ->will($this->returnValue($ip));
    }

    private function ensureUserAgent($userAgent)
    {
        $this->request->headers = $this->headerBag;

        $this
            ->headerBag
            ->expects($this->once())
            ->method('get')
            ->with('User-Agent')
            ->will($this->returnValue($userAgent));
    }

    private function ensureRequestIsLogged($method, $url)
    {
        $message = sprintf('Request: method = %s; url = %s', $method, $url);
        $context = [
            'metadata' => ['http_method' => $method, 'url' => $url],
            'description' => 'request_info',
        ];

        $this
            ->logger
            ->expects($this->at(0))
            ->method('info')
            ->with($message, $context);
    }

    private function ensureClientInfoIsLogged($userAgent, $ip)
    {
        $message = sprintf('Client info: ip = %s; user agent = %s', $ip, $userAgent);
        $context = [
            'metadata' => ['remote_address' => $ip, 'http_user_agent' => $userAgent],
            'description' => 'client_info',
        ];

        $this
            ->logger
            ->expects($this->at(1))
            ->method('info')
            ->with($message, $context);
    }

    private function ensureGetIsLogged()
    {
        $message = '$_GET = "foo": "bar"';
        $context = ['description' => 'get_payload'];

        $this
            ->logger
            ->expects($this->at(2))
            ->method('info')
            ->with($message, $context);
    }

    private function ensurePostIsLogged()
    {
        $message = '$_POST = "foo": "{}"';
        $context = ['description' => 'post_payload'];

        $this
            ->logger
            ->expects($this->at(3))
            ->method('info')
            ->with($message, $context);
    }

    private function ensureFilesIsLogged()
    {
        $message = '$_FILES = "bar": "baz"';
        $context = ['description' => 'files_payload'];

        $this
            ->logger
            ->expects($this->at(4))
            ->method('info')
            ->with($message, $context);
    }

    private function ensureLoggerIsCalledExactly($times)
    {
        $this
            ->logger
            ->expects($this->exactly($times))
            ->method('info');
    }

    private function ensureGetData($data)
    {
        $this->request->query = $this->queryBag;

        $this
            ->queryBag
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue($data));
    }

    private function ensurePostData($data)
    {
        $this->request->request = $this->requestBag;

        $this
            ->requestBag
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue($data));
    }

    private function ensureFilesData($data)
    {
        $this->request->files = $this->fileBag;

        $this
            ->fileBag
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue($data));
    }

    private function createHeaderBag()
    {
        return $this->createMockFor('Symfony\Component\HttpFoundation\HeaderBag');
    }

    private function createParameterBag()
    {
        return $this->createMockFor('Symfony\Component\HttpFoundation\ParameterBag');
    }

    private function createFileBag()
    {
        return $this->createMockFor('Symfony\Component\HttpFoundation\FileBag');
    }

    private function createLogger()
    {
        return $this->createMockFor('Psr\Log\LoggerInterface');
    }
}
