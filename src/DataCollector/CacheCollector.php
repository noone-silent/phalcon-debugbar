<?php

namespace Phalcon\Incubator\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Phalcon\Cache\CacheInterface;
use Throwable;

class CacheCollector extends DataCollector implements Renderable
{
    protected bool $collectValues;

    protected CacheInterface $cache;

    public function __construct(bool $collectValues, CacheInterface $cache)
    {
        $this->collectValues = $collectValues;
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(): array
    {
        $data = [
            'messages' => [],
        ];

        $keys = $this->cache->getAdapter()->getKeys();
        foreach ($keys as $key) {
            $value = $this->getCache($key);
            $data['messages'][] = [
                'time'      => time(),
                'label'     => 'SAVE',
                'is_string' => true,
                'message'   => "[ Key => \"$key\" Value => $value ]",
            ];
        }
        $data['count'] = count($data['messages']);

        return $data;
    }

    protected function getCache($key): mixed
    {
        $prefix = $this->cache->getAdapter()->getPrefix();
        $key = str_replace($prefix, '', $key);
        try {
            return $this->cache->get($key);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'caches';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        return [
            'caches'       => [
                'icon'    => 'star',
                'widget'  => 'PhpDebugBar.Widgets.MessagesWidget',
                'map'     => 'caches.messages',
                'default' => '[]',
            ],
            'caches:badge' => [
                'map'     => 'caches.count',
                'default' => 'null',
            ],
        ];
    }
}
