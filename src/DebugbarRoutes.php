<?php

namespace Nin\Debugbar;

use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Router;
use Phalcon\Config\Config;

class DebugbarRoutes
{
    /**
     * @var \Phalcon\Di\DiInterface $container
     */
    protected DiInterface $container;

    /**
     * @var \Phalcon\Config\Config $config
     */
    protected Config $config;

    public function __construct(DiInterface $container)
    {
        $this->container = $container;
        $this->config = $container->getShared('config.debugbar');
    }

    /**
     * @return \Phalcon\Mvc\Router
     */
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
            'namespace' => 'Nin\Debugbar\Controllers',
            'controller' => 'open_handle',
            'action' => 'handle'
        ])->setName($routePrefix . '.open_handle.handle');

        $routes->addGet('clockwork/{id}', [
            'namespace' => 'Nin\Debugbar\Controllers',
            'controller' => 'OpenHandle',
            'action' => 'clockwork'
        ])->setName($routePrefix . '.open_handle.clockwork');

        $routes->addGet($urlPrefix . '/assets/stylesheets', [
            'namespace' => 'Nin\Debugbar\Controllers',
            'controller' => 'Asset',
            'action' => 'css'
        ])->setName($routePrefix . '.assets.css');

        $routes->addGet($urlPrefix . '/assets/javascript', [
            'namespace' => 'Nin\Debugbar\Controllers',
            'controller' => 'Asset',
            'action' => 'js'
        ])->setName($routePrefix . '.assets.js');

        $routes->addDelete('cache/{key}/{tags?}', [
            'namespace' => 'Nin\Debugbar\Controllers',
            'controller' => 'Cache',
            'action' => 'delete'
        ])->setName($routePrefix . '.cache.delete');
    }

}
