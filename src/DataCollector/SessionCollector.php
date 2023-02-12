<?php

namespace Nin\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Session\ManagerInterface;

class SessionCollector extends DataCollector implements Renderable
{
    /**
     * @var \Phalcon\Session\ManagerInterface
     */
    protected $session;

    public function __construct(ManagerInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $data = [];
        if (!empty($_SESSION)) {
            $opt = $this->session->getOptions();
            $prefix = 0;
            if (isset($opt['uniqueId'])) {
                $prefix = strlen($opt['uniqueId']);
            }
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'PHPDEBUGBAR_STACK_DATA') === false) {
                    @$data[substr_replace($key, '', 0,
                        $prefix)] = is_string($value) ? $value : $this->formatVar($value);
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'session';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "session" => [
                "icon" => "archive",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "session",
                "default" => "{}"
            ]
        ];
    }
}
