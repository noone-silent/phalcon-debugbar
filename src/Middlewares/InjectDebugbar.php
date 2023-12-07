<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\Middlewares;

use Exception;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Http\Request;
use Phalcon\Incubator\Debugbar\PhalconDebugbar;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Support\Version;

class InjectDebugbar extends Injectable
{
    /**
     * The Route Prefix for debugbar
     *
     * @var string
     */
    protected string $routePrefix;

    /**
     * The URIs that should be excluded.
     *
     * @var array
     */
    protected array $except;

    public function __construct(DiInterface $di)
    {
        $this->container = $di;
        $config = $di->getShared('config.debugbar');

        $this->routePrefix = $config->get('route_prefix');
        $this->except = $config->get('except')->toArray();
    }

    /**
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @param $data
     *
     * @throws Exception
     * @return bool
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher, $data): bool
    {
        /** @var PhalconDebugbar $debugbar */
        $debugbar = $this->container->getShared(PhalconDebugbar::class);

        /** @var Application $app */
        $app = $this->container->get('app');

        if (!$debugbar->isEnabled()) {
            return true;
        }

        if ((new Version())->getId() < 5000000) {
            $debugbar->disable();
            return true;
        }

        /** @var Router $router */
        $router = $this->container->getShared('router');

        /** @var Request $request */
        $request = $this->container->getShared('request');

        $router->handle($request->getURI());

        $current = $router->getMatchedRoute();
        if ($current === null || $current->getName() === null) {
            $debugbar->boot();
            return true;
        }

        $currentName = $current->getName();
        if (str_contains($currentName, $this->routePrefix)) {
            if (method_exists($app, 'useImplicitView')) {
                $app->useImplicitView(false);
            }

            $debugbar->disable();
        }

        if ($this->inExceptArray($currentName)) {
            $debugbar->disable();
        }

        $debugbar->boot();

        return true;
    }

    /**
     * Determine if the request has a URI that should be ignored.
     *
     * @param string $routeName
     *
     * @return bool
     */
    protected function inExceptArray(string $routeName): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if (str_contains($routeName, $except)) {
                return true;
            }
        }

        return false;
    }
}
