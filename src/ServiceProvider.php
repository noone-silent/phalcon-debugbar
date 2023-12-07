<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar;

use Phalcon\Config\Adapter\Php;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Config\Config;
use Phalcon\Config\Exception;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Events\Manager;
use Phalcon\Http\ResponseInterface;
use Phalcon\Incubator\Debugbar\Middlewares\InjectDebugbar;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Micro;

class ServiceProvider implements ServiceProviderInterface
{
    private ?string $configPath;

    public function __construct(?string $configPath = null)
    {
        $this->configPath = $configPath;
        if (
            $this->configPath !== null &&
            (
                file_exists($this->configPath) === false ||
                is_readable($this->configPath) === false
            )
        ) {
            throw new \RuntimeException(
                'Config file ' . $this->configPath . ' does not exist or is not readable.'
            );
        }
    }

    /**
     * Registers the service provider.
     *
     * @param DiInterface $di
     *
     * @throws \Exception
     */
    public function register(DiInterface $di): void
    {
        // Debugbar configure
        $debugbarConfig = $this->mergeConfig();
        $di->setShared('config.debugbar', static function () use ($debugbarConfig) {
            return $debugbarConfig;
        });

        $this->loadRouter($di);

        $di->setShared(
            PhalconDebugbar::class,
            static function () use ($di) {
                return (new PhalconDebugbar($di))
                    ->setHttpDriver(new PhalconHttpDriver());
            }
        );

        $this->boot($di);
    }

    public function boot(DiInterface $di): void
    {
        /** @var Application|Micro $app */
        $app = $di->getShared('app');

        /** @var PhalconDebugbar $debugbar */
        $debugbar = $di->getShared(PhalconDebugbar::class);

        /** @var Manager $eventsManager */
        $eventsManager = $app->getEventsManager();
        if (!is_object($eventsManager)) {
            $eventsManager = new Manager();
        }

        $eventsManager->attach(
            'dispatch:beforeExecuteRoute',
            new InjectDebugbar($di)
        );

        if ($app instanceof Micro) {
            $eventsManager->attach(
                'micro:beforeExecuteRoute',
                function () {
                    ob_start();
                }
            );
            $eventsManager->attach(
                'micro:afterExecuteRoute',
                function ($event, Micro $app) use ($debugbar) {
                    $response = $app->response;
                    if (null === $returned = $app->getReturnedValue()) {
                        $buffer = ob_get_clean();
                        $response->setContent($buffer);
                        $response = $debugbar->modifyResponse($response);
                        $response->send();
                    } elseif ($returned instanceof ResponseInterface) {
                        $debugbar->modifyResponse($returned);
                    }
                }
            );
        } elseif ($app instanceof Application) {
            $eventsManager->attach(
                'application:beforeSendResponse',
                function ($event, $app, $response) use ($debugbar) {
                    $debugbar->modifyResponse($response);
                }
            );
        }

        $app->setEventsManager($eventsManager);
    }

    /**
     * Merge config
     *
     * @throws Exception
     * @return Config
     */
    protected function mergeConfig(): Config
    {
        $baseConfig = new Php(dirname(__FILE__, 2) . '/config/debugbar.php');
        if ($this->configPath === null) {
            return $baseConfig;
        }

        $parts = explode('.', $this->configPath);
        $extension = end($parts);
        if ($extension === 'php') {
            $baseConfig->merge(new Php($this->configPath));
            return $baseConfig;
        }
        if ($extension === 'yml' || $extension === 'yaml') {
            $baseConfig->merge(new Yaml($this->configPath));
            return $baseConfig;
        }

        throw new \RuntimeException('Only .php/.yml/.yaml config files are supported');
    }

    protected function loadRouter(DiInterface $di): void
    {
        (new DebugbarRoutes($di))->loadRoutes();
    }
}
