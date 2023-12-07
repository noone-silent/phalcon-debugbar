<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Di\DiInterface;
use Phalcon\Support\Version;

use const PHP_SAPI;

class PhalconCollector extends DataCollector implements Renderable
{
    protected DiInterface $container;

    public function __construct(DiInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'phalcon';
    }

    /**
     * @return array
     */
    public function collect(): array
    {
        return [
            'version'   => (new Version())->get(),
            'interface' => PHP_SAPI,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        return [
            'phalcon_version' => [
                'icon'    => 'code',
                'tooltip' => 'Phalcon Version',
                'map'     => 'phalcon.version',
                'default' => '',
            ],
        ];
    }
}
