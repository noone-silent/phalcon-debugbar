<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Session\ManagerInterface;

class SessionCollector extends DataCollector implements Renderable
{
    protected ManagerInterface $session;

    public function __construct(ManagerInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(): array
    {
        $data = [];
        if (!empty($_SESSION)) {
            $opt = $this->session->getOptions();
            $prefix = 0;
            if (isset($opt['uniqueId'])) {
                $prefix = strlen($opt['uniqueId']);
            }
            foreach ($_SESSION as $key => $value) {
                if (!str_contains($key, 'PHPDEBUGBAR_STACK_DATA')) {
                    @$data[substr_replace(
                        $key,
                        '',
                        0,
                        $prefix
                    )] = is_string($value) ? $value : $this->formatVar($value);
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'session';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        return [
            'session' => [
                'icon'    => 'archive',
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget',
                'map'     => 'session',
                'default' => '{}',
            ],
        ];
    }
}
