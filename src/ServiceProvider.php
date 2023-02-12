<?php

namespace Nin\Debugbar;

use Nin\Debugbar\Middlewares\InjectDebugbar;
use Phalcon\Config\Adapter\Php;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Config\Config;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro;
use Phalcon\Events\Manager;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers the service provider.
     *
     * @param \Phalcon\Di\DiInterface $container
     * @throws \Exception
     */
    public function register(DiInterface $container): void
    {
        // Debugbar configure
        $debugbarConfig = $this->mergeConfig();
        $container->setShared('config.debugbar', function () use ($debugbarConfig) {
            return $debugbarConfig;
        });

        $this->loadRouter($container);

        $container->setShared(PhalconDebugbar::class, function () use ($container) {
            $debugbar = new PhalconDebugbar($container);
            $debugbar->setHttpDriver(new PhalconHttpDriver());
            return $debugbar;
        });

        $this->boot($container);
    }

    public function boot(DiInterface $container): void
    {
        /** @var Application|Micro $app */
        $app = $container->getShared('app');

        /** @var PhalconDebugbar $debugbar */
        $debugbar = $container->getShared(PhalconDebugbar::class);

        /** @var \Phalcon\Events\Manager $eventsManager */
        $eventsManager = $app->getEventsManager();
        if (!is_object($eventsManager)) {
            $eventsManager = new Manager();
        }

        $eventsManager->attach(
            'dispatch:beforeExecuteRoute',
            new InjectDebugbar($container)
        );

        if ($app instanceof Micro) {
            $eventsManager->attach('micro:beforeExecuteRoute', function () {
                ob_start();
            });
            $eventsManager->attach('micro:afterExecuteRoute', function ($event, Application $app) use ($debugbar) {
                $response = $app->response;
                if (null === $returned = $app->getReturnedValue()) {
                    $buffer = ob_get_clean();
                    $response->setContent($buffer);
                    $response = $debugbar->modifyResponse($response);
                    $response->send();
                } elseif (is_object($returned) && ($returned instanceof ResponseInterface)) {
                    $debugbar->modifyResponse($returned);
                }
            });
        } elseif ($app instanceof Application) {
            $eventsManager->attach('application:beforeSendResponse',
                function ($event, $app, $response) use ($debugbar) {
                    $debugbar->modifyResponse($response);
                });
        }

        $app->setEventsManager($eventsManager);
    }

    /**
     * Merge config
     *
     * @return Config
     * @throws \Phalcon\Config\Exception
     */
    protected function mergeConfig(): Config
    {
        $baseConfig = new Php(__DIR__ . '/../config/debugbar.php');
        $mergeConfigPath = $_SERVER['DOCUMENT_ROOT'] . '/../config/debugbar';

        $configClassReaders = [
            'php' => \Phalcon\Config\Adapter\Php::class,
            'php5' => \Phalcon\Config\Adapter\Php::class,
            'inc' => \Phalcon\Config\Adapter\Php::class,
            'ini' => \Phalcon\Config\Adapter\Ini::class,
            'json' => \Phalcon\Config\Adapter\Json::class,
            'yml' => \Phalcon\Config\Adapter\Yaml::class,
            'yaml' => \Phalcon\Config\Adapter\Yaml::class
        ];

        foreach ($configClassReaders as $extension => $classReader) {
            if (file_exists($mergeConfigPath . '.' . $extension)) {
                $baseConfig->merge(new $classReader($mergeConfigPath . '.' . $extension));
                return $baseConfig;
            }
        }
        return $baseConfig;
    }

    protected function loadRouter(DiInterface $container): void
    {
        (new DebugbarRoutes($container))->loadRoutes();
    }

}
