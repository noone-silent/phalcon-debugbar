<?php

namespace Nin\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Router;

class RouteCollector extends DataCollector implements Renderable
{
    /**
     * @var Router $router
     */
    protected $router;

    /**
     * @var DiInterface $container
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
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
        $result['Paths'] = $this->formatVar($paths);
        if ($params = $router->getParams()) {
            $result['Params'] = $this->formatVar($params);
        }

        $result['HttpMethods'] = $route->getHttpMethods();
        $result['RouteName'] = $route->getName();
        $result['Hostname'] = $route->getHostname();
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

        if (isset($reflector)) {
            $start = $reflector->getStartLine() - 1;
            $stop = $reflector->getEndLine();
            $filename = substr($reflector->getFileName(), mb_strlen(realpath(dirname($_SERVER['DOCUMENT_ROOT']))));
            $code = array_slice(file($reflector->getFileName()), $start, $stop - $start);
            $result['File'] = $filename . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine() . "  [CODE]: \n" . implode("",
                    $code);
        }

        return array_filter($result);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'route';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "route" => [
                "icon" => "share",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "route",
                "default" => "{}"
            ]
        ];
    }
}
