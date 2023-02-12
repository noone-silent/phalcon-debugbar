<?php

namespace Nin\Debugbar;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer;

class JsRenderer extends JavascriptRenderer
{
    // Use XHR handler by default, instead of jQuery
    protected $ajaxHandlerBindToJquery = false;
    protected $ajaxHandlerBindToXHR = true;

    /**
     * @var \Phalcon\Mvc\Url $url
     */
    protected $url;

    public function __construct(DebugBar $debugBar, $baseUrl = null, $basePath = null)
    {
        parent::__construct($debugBar, $baseUrl, $basePath);

        $this->cssFiles['laravel'] = __DIR__ . '/Resources/laravel-debugbar.css';
        $this->cssVendors['fontawesome'] = __DIR__ . '/Resources/vendor/font-awesome/style.css';
        $this->jsFiles['laravel-sql'] = __DIR__ . '/Resources/sqlqueries/widget.js';
        $this->jsFiles['laravel-cache'] = __DIR__ . '/Resources/cache/widget.js';
        $this->jsFiles['laravel-view'] = __DIR__ . '/Resources/templates/widget.js';

        /*$theme = config('debugbar.theme', 'auto');
        switch ($theme) {
            case 'dark':
                $this->cssFiles['laravel-dark'] = __DIR__ . '/Resources/laravel-debugbar-dark-mode.css';
                break;
            case 'auto':
                $this->cssFiles['laravel-dark-0'] = __DIR__ . '/Resources/laravel-debugbar-dark-mode-media-start.css';
                $this->cssFiles['laravel-dark-1'] = __DIR__ . '/Resources/laravel-debugbar-dark-mode.css';
                $this->cssFiles['laravel-dark-2'] = __DIR__ . '/Resources/laravel-debugbar-dark-mode-media-end.css';
        }*/
    }

    /**
     * Set the URL Generator
     *
     * @param \Phalcon\Mvc\Url $url
     */
    public function setUrlGenerator($url)
    {
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function renderHead(): string
    {
        if (!$this->url) {
            return parent::renderHead();
        }

        $prefixRoute = $this->debugBar->config->get('route_prefix');

        $url = $this->url;
        $baseUri = $url->getBaseUri();
        $cssRoute = $baseUri . ltrim($url->getStatic(['for' => $prefixRoute . '.assets.css']),
                '/') . '?v=' . $this->getModifiedTime('css');

        $jsRoute = $baseUri . ltrim($url->getStatic(['for' => $prefixRoute . '.assets.js']),
                '/') . '?v=' . $this->getModifiedTime('js');

        $html = '<link rel="stylesheet" type="text/css" property="stylesheet" href="' . $cssRoute . '" data-turbolinks-eval="false" data-turbo-eval="false" />';
        $html .= '<script src="' . $jsRoute . '" data-turbolinks-eval="false" data-turbo-eval="false"></script>';

        if ($this->isJqueryNoConflictEnabled()) {
            $html .= '<script data-turbo-eval="false">jQuery.noConflict(true);</script>' . "\n";
        }
        $html .= $this->getInlineHtml();

        return $html;
    }

    protected function getInlineHtml(): string
    {
        $html = '';

        foreach (['head', 'css', 'js'] as $asset) {
            foreach ($this->getAssets('inline_' . $asset) as $item) {
                $html .= $item . "\n";
            }
        }

        return $html;
    }

    /**
     * Get the last modified time of any assets.
     *
     * @param string $type 'js' or 'css'
     * @return int
     */
    protected function getModifiedTime($type): int
    {
        $files = $this->getAssets($type);

        $latest = 0;
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latest) {
                $latest = $mtime;
            }
        }
        return $latest;
    }

    /**
     * Return assets as a string
     *
     * @param string $type 'js' or 'css'
     * @return string
     */
    public function dumpAssetsToString($type): string
    {
        $files = $this->getAssets($type);
        $content = '';

        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        return $content;
    }

    /**
     * Makes a URI relative to another
     *
     * @param string|array $uri
     * @param string $root
     * @return string|array
     */
    protected function makeUriRelativeTo($uri, $root)
    {
        if (!$root) {
            return $uri;
        }

        if (is_array($uri)) {
            $uris = [];
            foreach ($uri as $u) {
                $uris[] = $this->makeUriRelativeTo($u, $root);
            }
            return $uris;
        }

        if (substr($uri ?? '', 0, 1) === '/' || preg_match('/^([a-zA-Z]+:\/\/|[a-zA-Z]:\/|[a-zA-Z]:\\\)/',
                $uri ?? '')) {
            return $uri;
        }
        return rtrim($root, '/') . "/$uri";
    }
}
