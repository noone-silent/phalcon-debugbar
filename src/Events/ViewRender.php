<?php

namespace Nin\Debugbar\Events;

use Nin\Debugbar\DataCollector\ViewCollector;
use Nin\Debugbar\Phalcon\View\Profile;
use Phalcon\Config\Config;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Mvc\ViewInterface;

class ViewRender extends Injectable
{
    protected ViewCollector $viewCollector;
    protected Config $config;

    public function __construct(DiInterface $container, $config, ViewCollector $viewCollector)
    {
        $this->container = $container;
        $this->config = $config;
        $this->viewCollector = $viewCollector;
    }

    public function beforeRenderView(Event $event, ViewInterface $view)
    {
        $viewFilePath = $view->getActiveRenderPath();
        $name = $this->viewCollector->getTemplateName($viewFilePath, $view->getViewsDir());
        $key = $this->viewCollector->getTemplateKey($name);

        $profile = new Profile($view->getActionName());
        $profile->enter();
        $profile->setPath($viewFilePath);
        $profile->setBaseDir($view->getViewsDir());
        $profile->setName($name);
        if ($this->config->path('options.views.data', false)) {
            $profile->setParams($view->getParamsToView());
        }
        $this->viewCollector->addTemplate($profile, $key);
    }

    public function afterRenderView(Event $event, ViewInterface $view)
    {
        $viewFilePath = $view->getActiveRenderPath();
        $name = $this->viewCollector->getTemplateName($viewFilePath, $view->getViewsDir());
        $key = $this->viewCollector->getTemplateKey($name);

        $profile = $this->viewCollector->getTemplate($key);
        if ($profile) {
            $profile->leave();
            $this->viewCollector->addTemplate($profile, $key);
        }
    }

}
