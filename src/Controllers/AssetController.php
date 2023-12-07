<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\Controllers;

use Phalcon\Incubator\Debugbar\JsRenderer;
use Phalcon\Incubator\Debugbar\PhalconDebugbar;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

class AssetController extends Controller
{
    /**
     * Return the javascript for the Debugbar
     *
     * @return Response
     * @throws \Exception
     */
    public function jsAction(): Response
    {
        /** @var JsRenderer $renderer */
        $renderer = $this->container->getShared(PhalconDebugbar::class)->getJavascriptRenderer();

        $content = $renderer->dumpAssetsToString('js');
        $response = new Response($content, 200);

        $response->setHeader('Content-Type', 'text/javascript');

        return $this->cacheResponse($response);
    }

    /**
     * Return the stylesheets for the Debugbar
     *
     * @return Response
     * @throws \Exception
     */
    public function cssAction(): Response
    {
        /** @var JsRenderer $renderer */
        $renderer = $this->container->getShared(PhalconDebugbar::class)->getJavascriptRenderer();

        $content = $renderer->dumpAssetsToString('css');

        $response = new Response($content, 200);
        $response->setHeader('Content-Type', 'text/css');

        return $this->cacheResponse($response);
    }

    /**
     * Cache the response 1 year (31536000 sec)
     *
     * @param Response $response
     * @return Response
     * @throws \Exception
     */
    protected function cacheResponse(Response $response): Response
    {
        $response->setExpires(new \DateTime('+1 year'));

        return $response;
    }
}
