<?php

namespace Nin\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Di\DiInterface;
use Phalcon\Http\Request;
use Phalcon\Http\Response;

class RequestCollector extends DataCollector implements Renderable
{

    /**
     * @var Request $request
     */
    protected $request;
    /**
     * @var Response $response
     */
    protected $response;
    /**
     * @var DiInterface
     */
    protected $container;

    public function __construct($request, $response, $container)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $request = $this->request;
        $response = $this->response;

        $statusCode = $response->getStatusCode() ?: Response::STATUS_OK;
        $responseHeaders = $response->getHeaders()->toArray() ?: headers_list();

        $cookies = $_COOKIE;
        unset($cookies[session_name()]);
        $cookiesService = $response->getCookies();
        if ($cookiesService) {
            $useEncrypt = true;
            if ($cookiesService->isUsingEncryption() && $this->container->has('crypt') && !$this->container['crypt']->getKey()) {
                $useEncrypt = false;
            }
            if (!$cookiesService->isUsingEncryption()) {
                $useEncrypt = false;
            }
            foreach ($cookies as $key => $vlaue) {
                $cookies[$key] = $cookiesService->get($key)->useEncryption($useEncrypt)->getValue();
            }
        }
        $data = [
            'status_code' => $statusCode,
            'content_type' => $response->getHeaders()->get('Content-Type') ?: 'text/html',
            'request_query' => $request->getQuery(),
            'request_post' => $request->getPost(),
            'request_body' => $request->getRawBody(),
            'request_cookies' => $cookies,
            'request_server' => $_SERVER,
            'response_headers' => $responseHeaders,
            'response_body' => $request->isAjax() ? $response->getContent() : '',
        ];

        $data = array_filter($data);
        if (isset($data['request_query']['_url'])) {
            unset($data['request_query']['_url']);
        }
        if (empty($data['request_query'])) {
            unset($data['request_query']);
        }

        if (isset($data['request_headers']['php-auth-pw'])) {
            $data['request_headers']['php-auth-pw'] = '******';
        }

        if (isset($data['request_server']['PHP_AUTH_PW'])) {
            $data['request_server']['PHP_AUTH_PW'] = '******';
        }

        foreach ($data as $key => $var) {
            if (!is_string($data[$key])) {
                $data[$key] = $this->formatVar($var);
            }
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'request';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "request" => [
                "icon" => "tags",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "request",
                "default" => "{}"
            ]
        ];
    }
}
