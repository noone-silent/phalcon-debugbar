<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar;

use Phalcon\Config\Config;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Router;

class DebugbarRoutes
{
    protected DiInterface $container;

    protected Config $config;

    public function __construct(DiInterface $container)
    {
        $this->container = $container;
        $this->config = $container->getShared('config.debugbar');
    }

    protected function getRouter(): Router
    {
        return $this->container->getShared('router');
    }

    public function loadRoutes(): void
    {
        $routes = $this->getRouter();
        $routePrefix = $this->config->get('route_prefix');
        $urlPrefix = '/' . $routePrefix;

        $routes->addGet('open', [
            'namespace'  => 'Phalcon\Incubator\Debugbar\Controllers',
            'controller' => 'open_handle',
            'action'     => 'handle',
        ])->setName($routePrefix . '.open_handle.handle');

        $routes->addGet('clockwork/{id}', [
            'namespace'  => 'Phalcon\Incubator\Debugbar\Controllers',
            'controller' => 'OpenHandle',
            'action'     => 'clockwork',
        ])->setName($routePrefix . '.open_handle.clockwork');

        $routes->addGet($urlPrefix . '/assets/stylesheets', [
            'namespace'  => 'Phalcon\Incubator\Debugbar\Controllers',
            'controller' => 'Asset',
            'action'     => 'css',
        ])->setName($routePrefix . '.assets.css');

        $routes->addGet($urlPrefix . '/assets/javascript', [
            'namespace'  => 'Phalcon\Incubator\Debugbar\Controllers',
            'controller' => 'Asset',
            'action'     => 'js',
        ])->setName($routePrefix . '.assets.js');

        $routes->addDelete('cache/{key}/{tags?}', [
            'namespace'  => 'Phalcon\Incubator\Debugbar\Controllers',
            'controller' => 'Cache',
            'action'     => 'delete',
        ])->setName($routePrefix . '.cache.delete');
    }
}
