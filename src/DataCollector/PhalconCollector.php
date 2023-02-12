<?php

namespace Nin\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Di\DiInterface;
use Phalcon\Support\Version;

class PhalconCollector extends DataCollector implements Renderable
{
    /**
     * @var \Phalcon\Di\DiInterface $container
     */
    protected $container;

    public function __construct(DiInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'phalcon';
    }

    /**
     * @return array
     */
    public function collect()
    {
        return [
            'version' => (new Version)->get(),
            'interface' => PHP_SAPI
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "phalcon_version" => [
                "icon" => "code",
                "tooltip" => "Phalcon Version",
                "map" => "phalcon.version",
                "default" => ""
            ],
        ];
    }
}
