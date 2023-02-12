<?php

namespace Nin\Debugbar;

use DebugBar\Bridge\SwiftMailer\SwiftLogCollector;
use DebugBar\Bridge\SwiftMailer\SwiftMailCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use Nin\Debugbar\DataCollector\CacheCollector;
use Nin\Debugbar\DataCollector\ConfigCollector;
use Nin\Debugbar\DataCollector\LogsCollector;
use Nin\Debugbar\DataCollector\PhalconCollector;
use Nin\Debugbar\DataCollector\QueryCollector;
use Nin\Debugbar\DataCollector\RequestCollector;
use Nin\Debugbar\DataCollector\RouteCollector;
use Nin\Debugbar\DataCollector\SessionCollector;
use Nin\Debugbar\DataCollector\ViewCollector;
use Nin\Debugbar\Events\DBQuery;
use Nin\Debugbar\Events\ViewRender;
use Phalcon\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Phalcon\Config\Config;
use Phalcon\Di\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Http\ResponseInterface;
use Phalcon\Logger\Logger;
use Phalcon\Support\Version;
use Phalcon\Http\Request;
use Exception;
use Throwable;

class PhalconDebugbar extends DebugBar
{
    use DebugFunctions;

    /**
     * @var \Phalcon\Di\DiInterface $container
     */
    protected DiInterface $container;

    /**
     * @var \Phalcon\Config\Config $config
     */
    public Config $config;

    /**
     * Normalized Phalcon Version
     *
     * @var string
     */
    protected string $version;

    /**
     * @var JsRenderer $jsRenderer
     */
    protected $jsRenderer;

    protected $enabled;

    public bool $isDebugbarRequest = false;

    /**
     * True when booted.
     *
     * @var bool
     */
    protected bool $booted = false;

    public function __construct(DiInterface $container)
    {
        $this->container = $container;
        $this->version = (new Version)->get();
        $this->config = $container->getShared('config.debugbar');
    }

    /**
     * Enable the Debugbar and boot, if not already booted.
     */
    public function enable(): void
    {
        $this->enabled = true;

        if (!$this->booted) {
            $this->boot();
        }
    }

    /**
     * Disable the Debugbar
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        $this->initCollectors();

        $renderer = $this->getJavascriptRenderer();
        $renderer->setIncludeVendors($this->config->path('debugbar.include_vendors', true));
        $renderer->setBindAjaxHandlerToFetch($this->config->path('debugbar.capture_ajax', true));
        $renderer->setBindAjaxHandlerToXHR($this->config->path('debugbar.capture_ajax', true));

        $this->booted = true;
    }

    /**
     * @throws \DebugBar\DebugBarException
     */
    public function initCollectors()
    {
        /** @var \Nin\Debugbar\PhalconDebugbar $debugBar */
        $debugBar = $this;

        if ($this->shouldCollect('phpinfo', true)) {
            $this->addCollector(new PhpInfoCollector());
        }

        $this->addCollector(new PhalconCollector($this->container));

        if ($this->shouldCollect('messages', true)) {
            $this->addCollector(new MessagesCollector());
        }

        if ($this->shouldCollect('time', true)) {
            $request = new Request();
            $startTime = $request->getServer('REQUEST_TIME_FLOAT');
            $this->addCollector(new TimeDataCollector($startTime));
            $debugBar->startMeasure('application', 'Application');
        }

        if ($this->shouldCollect('memory', true)) {
            $this->addCollector(new MemoryCollector());
        }

        if ($this->shouldCollect('exceptions', true)) {
            try {
                $exceptionCollector = new ExceptionsCollector();
                $exceptionCollector->setChainExceptions(
                    $this->config->path('options.exceptions.chain', true)
                );
                $this->addCollector($exceptionCollector);
            } catch (\Exception $e) {
            }
        }

        if ($this->shouldCollect('default_request', false)) {
            $this->addCollector(new RequestDataCollector());
        }

        if ($this->shouldCollect('route')) {
            try {
                $routeCollector = new RouteCollector($this->container);
                if (!$this->hasCollector($routeCollector->getName())) {
                    $this->addCollector($routeCollector);
                }
            } catch (\Exception $e) {
                $this->addThrowable(
                    new Exception(
                        'Cannot add RouteCollector to Debugbar: ' . $e->getMessage(),
                        $e->getCode(),
                        $e
                    )
                );
            }
        }

        if ($this->shouldCollect('log', true)) {
            try {
                /** @var Logger $logger */
                $logger = null;

                /** Get Logger */
                if ($this->container->has('logger')) {
                    $logger = $this->container->get('logger');
                } elseif ($this->container->has('log')) {
                    $logger = $this->container->get('log');
                }
                if ($logger) {
                    /** Get log adapters */
                    $adapters = $logger->getAdapters();
                    foreach ($adapters as $adapterName => $adapter) {
                        $this->addCollector(new LogsCollector($adapter->getName(), 'logs(' . $adapterName . ')'));
                    }
                }
            } catch (\Exception $e) {
                $this->addThrowable(
                    new Exception(
                        'Cannot add LogsCollector to Debugbar: ' . $e->getMessage(),
                        $e->getCode(),
                        $e
                    )
                );
            }
        }

        $this->attachServices();
    }

    /**
     * @throws \DebugBar\DebugBarException
     */
    public function attachServices()
    {
        if (!$this->isEnabled()) {
            return;
        }

        if ($this->shouldCollect('cache', false) && $this->container->has('cache')) {
            try {
                /** @var CacheAdapterInterface $cache */
                $cache = $this->container->get('cache');

                $collectValues = $this->config->get('options.cache.values', true);
                $cacheCollector = new CacheCollector($collectValues, $cache);
                $this->addCollector($cacheCollector);
            } catch (\Exception $e) {
                $this->addThrowable(
                    new Exception(
                        'Cannot add CacheCollector to Debugbar: ' . $e->getMessage(),
                        $e->getCode(),
                        $e
                    )
                );
            }
        }

        if ($this->shouldCollect('db', true) && $this->container->has('db')) {
            $this->attachDb($this->container->get('db'));
        }

        if ($this->shouldCollect('mail', true) && $this->container->has('mail')) {
            $mailer = $this->container->get('mail');
            $this->attachMailer($mailer);
        }

        if ($this->shouldCollect('view', true) && $this->container->has('view')) {
            $this->attachView($this->container['view']);
        }

    }

    public function shouldCollect($name, $default = false)
    {
        return $this->config->path('collectors.' . $name, $default);
    }

    /**
     * Adds a data collector
     *
     * @param DataCollectorInterface $collector
     * @return $this|PhalconDebugbar
     * @throws \DebugBar\DebugBarException
     */
    public function addCollector(DataCollectorInterface $collector): PhalconDebugbar
    {
        parent::addCollector($collector);

        if (method_exists($collector, 'useHtmlVarDumper')) {
            $collector->useHtmlVarDumper();
        }

        return $this;
    }

    /**
     * Returns a JavascriptRenderer for this instance
     *
     * @param string $baseUrl
     * @param string $basePath
     * @return JsRenderer
     */
    public function getJavascriptRenderer($baseUrl = null, $basePath = null): JsRenderer
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JsRenderer($this, $baseUrl, $basePath);
            $this->jsRenderer->setUrlGenerator($this->container->get('url'));
        }
        return $this->jsRenderer;
    }

    /**
     * Check if the Debugbar is enabled
     * @return boolean
     */
    public function isEnabled(): bool
    {
        if ($this->enabled === null) {
            $configEnabled = $this->config->get('enabled');

            if ($configEnabled === null) {
                $configEnabled = $this->config->get('app.debug');
            }

            $this->enabled = $configEnabled;
        }

        return $this->enabled;
    }

    public function modifyResponse(ResponseInterface $response): ResponseInterface
    {
        if (!$this->isEnabled()) {
            return $response;
        }

        if ($this->shouldCollect('config', false) && $this->container->has('config')) {
            try {
                $config = $this->container->get('config');
                $configData = $config->toArray();
                $configCollector = new ConfigCollector($configData);
                $this->addCollector($configCollector);
            } catch (Exception $e) {
                $this->addThrowable(
                    new Exception(
                        'Cannot add ConfigCollector to Phalcon Debugbar: ' . $e->getMessage(),
                        $e->getCode(),
                        $e
                    )
                );
            }
        }

        if ($this->shouldCollect('session') && $this->container->has('session')) {
            try {
                $this->addCollector(new SessionCollector($this->container->get('session')));
            } catch (\Exception $e) {
                $this->addThrowable(
                    new Exception(
                        'Cannot add SessionCollector to Phalcon Debugbar: ' . $e->getMessage(),
                        $e->getCode(),
                        $e
                    )
                );
            }
        }

        if ($this->shouldCollect('request', true)) {
            try {
                $requestCollector = new RequestCollector($this->container->get('request'), $response,
                    $this->container);
                if (!$this->hasCollector($requestCollector->getName())) {
                    $this->addCollector($requestCollector);
                }
            } catch (\Exception $e) {
                $this->addThrowable(
                    new Exception(
                        'Cannot add PhalconRequestCollector to Phalcon Debugbar: ' . $e->getMessage(),
                        $e->getCode(),
                        $e
                    )
                );
            }
        }

        $this->injectDebugbar($response);

        return $response;
    }

    /**
     * @param $db
     * @throws \DebugBar\DebugBarException
     */
    public function attachDb($db)
    {
        try {
            if ($this->hasCollector('time') && $this->config->path('options.db.timeline', false)) {
                $timeCollector = $this->getCollector('time');
            } else {
                $timeCollector = null;
            }
            $queryCollector = new QueryCollector($timeCollector);
            $queryCollector->setDataFormatter(new \Nin\Debugbar\DataFormatter\QueryFormatter());

            if ($this->config->path('options.db.with_params')) {
                $queryCollector->setRenderSqlWithParams(true);
            }

            if ($this->config->path('options.db.backtrace')) {
                $middleware = [];
                $queryCollector->setFindSource(true, $middleware);
            }

            if ($this->config->path('options.db.backtrace_exclude_paths')) {
                $excludePaths = $this->config->path('options.db.backtrace_exclude_paths');
                $queryCollector->mergeBacktraceExcludePaths($excludePaths->toArray());
            }

            $queryCollector->setDurationBackground($this->config->path('options.db.duration_background'));

            if ($this->config->path('options.db.explain.enabled')) {
                $types = $this->config->path('options.db.explain.types');
                $queryCollector->setExplainSource(true, $types);
            }

            if ($this->config->path('options.db.hints', true)) {
                $queryCollector->setShowHints(true);
            }

            if ($this->config->path('options.db.show_copy', false)) {
                $queryCollector->setShowCopyButton(true);
            }

            $this->addCollector($queryCollector);

            $eventsManager = $db->getEventsManager();
            if (!$eventsManager) {
                $eventsManager = new Manager();
            }

            $eventsManager->attach('db', new DBQuery($this->container, $queryCollector));
        } catch (\Exception $e) {
            $this->addThrowable(
                new Exception(
                    'Cannot add listen to Queries for Debugbar: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                )
            );
        }
    }

    /**
     * @param $mailer
     * @throws \DebugBar\DebugBarException
     */
    public function attachMailer($mailer)
    {
        if (class_exists('\Swift_Mailer') && ($mailer instanceof \Swift_Mailer)) {
            $this->addCollector(new SwiftMailCollector($mailer));
            if ($this->config->path('options.mail.full_log') && $this->hasCollector('messages')) {
                $this['messages']->aggregate(new SwiftLogCollector($mailer));
            }
        }
    }

    /**
     * @param $view
     * @throws \DebugBar\DebugBarException
     */
    public function attachView($view)
    {
        $config = $this->config;
        $eventsManager = $view->getEventsManager();
        if (!is_object($eventsManager)) {
            $eventsManager = new Manager();
        }

        $collector = new ViewCollector(null, $config);
        $eventsManager->attach('view', new ViewRender($this->container, $config, $collector));
        $view->setEventsManager($eventsManager);

        $this->addCollector($collector);
    }

    /**
     * Adds an exception to be profiled in the debug bar
     *
     * @param Throwable $e
     * @throws \DebugBar\DebugBarException
     */
    public function addThrowable(Throwable $e): void
    {
        if ($this->hasCollector('exceptions')) {
            /** @var \DebugBar\DataCollector\ExceptionsCollector $collector */
            $collector = $this->getCollector('exceptions');
            $collector->addThrowable($e);
        }
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param ResponseInterface $response
     */
    public function injectDebugbar(ResponseInterface $response): void
    {
        $content = $response->getContent();

        $renderer = $this->getJavascriptRenderer();
        if ($this->getStorage()) {
            $url = $this->container->getShared('url');
            $openHandlerUrl = $url->getStatic(array('for' => 'debugbar.openhandler'));
            $renderer->setOpenHandlerUrl($openHandlerUrl);
        }

        $head = $renderer->renderHead();
        $widget = $renderer->render();

        // Try to put the js/css directly before the </head>
        $pos = strripos($content, '</head>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $head . substr($content, $pos);
        } else {
            // Append the head before the widget
            $widget = $head . $widget;
        }

        // Try to put the widget at the end, directly before the </body>
        $pos = strripos($content, '</body>');

        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $widget . substr($content, $pos);
        } else {
            $content = $content . $widget;
        }

        // Update the new content and reset the content length
        $response->setContent($content);
        $response->getHeaders()->remove('Content-Length');
    }

}
