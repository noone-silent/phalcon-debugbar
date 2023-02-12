<?php

namespace Nin\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Cache\Adapter\AdapterInterface as CacheAdapterInterface;

class CacheCollector extends DataCollector implements Renderable
{
    /**
     * @var bool
     */
    protected $collectValues;

    /**
     * @var \Phalcon\Cache\Cache
     */
    protected $cache;

    public function __construct($collectValues, $cache)
    {
        $this->collectValues = $collectValues;
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $data = [
            'messages' => [],
            'count' => 0,
        ];
        $keys = $this->cache->getAdapter()->getKeys();
        foreach ($keys as $key) {
            $value = $this->getCache($key);
            $data['messages'][] = [
                'time' => time(),
                'label' => 'SAVE',
                'is_string' => true,
                'message' => "[ Key => \"$key\" Value => $value ]",
            ];
        }
        $data['count'] = count($data['messages']);

        return $data;
    }


    protected function getCache($key)
    {
        $prefix = $this->cache->getAdapter()->getPrefix();
        $key = str_replace($prefix, '', $key);
        return $this->cache->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'caches';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "caches" => [
                'icon' => 'star',
                "widget" => "PhpDebugBar.Widgets.MessagesWidget",
                "map" => "caches.messages",
                "default" => "[]"
            ],
            "caches:badge" => [
                "map" => "caches.count",
                "default" => "null"
            ]
        ];
    }
}
