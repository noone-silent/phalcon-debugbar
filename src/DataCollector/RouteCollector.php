<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Router;

class RouteCollector extends DataCollector implements Renderable
{
    protected DiInterface $container;

    public function __construct(DiInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(): array
    {
        $dispatcher = $this->container['dispatcher'];

        /** @var Router $router */
        $router = $this->container['router'];
        $route = $router->getMatchedRoute();

        if (!$route) {
            return [];
        }

        $uri = $route->getPattern();
        $paths = $route->getPaths();

        $result['Uri'] = $uri ?: '-';
        $result['Paths'] = $this->getDataFormatter()->formatVar($paths);
        if ($params = $router->getParams()) {
            $result['Params'] = $this->getDataFormatter()->formatVar($params);
        }

        $result['HttpMethods'] = $route->getHttpMethods();
        $result['RouteName'] = $route->getName();
        $result['Hostname'] = $route->getHostname();
        try {
            if ($this->container->has('app') && ($app = $this->container['app']) instanceof Micro) {
                if (($handler = $app->getActiveHandler()) instanceof \Closure || is_string($handler)) {
                    $reflector = new \ReflectionFunction($handler);
                } elseif (is_array($handler)) {
                    $reflector = new \ReflectionMethod($handler[0], $handler[1]);
                }
            } else {
                $result['Module'] = $router->getModuleName();
                $result['Controller'] = get_class($controllerInstance = $dispatcher->getActiveController());
                $result['Action'] = $dispatcher->getActiveMethod();
                $reflector = new \ReflectionMethod($controllerInstance, $result['Action']);
            }
        } catch (\Throwable) {
        }

        if (isset($reflector)) {
            $start = $reflector->getStartLine() - 1;
            $stop = $reflector->getEndLine();
            $filename = substr(
                $reflector->getFileName(),
                mb_strlen(realpath(dirname($_SERVER['DOCUMENT_ROOT'])))
            );
            $code = array_slice(
                file($reflector->getFileName()),
                $start,
                $stop - $start
            );
            $result['File'] = $filename .
                ':' .
                $reflector->getStartLine() .
                '-' .
                $reflector->getEndLine() .
                "  [CODE]: \n" .
                implode(
                    "",
                    $code
                );
        }

        return array_filter($result);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'route';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        return [
            'route' => [
                'icon'    => 'share',
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget',
                'map'     => 'route',
                'default' => '{}',
            ],
        ];
    }
}
