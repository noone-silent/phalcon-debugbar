<?php

namespace Nin\Debugbar\Events;

use Nin\Debugbar\DataCollector\QueryCollector;
use Phalcon\Db\Profiler;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Db\Adapter\Pdo\AbstractPdo;

class DBQuery extends Injectable
{
    protected QueryCollector $queryCollector;

    public function __construct(DiInterface $container, QueryCollector $queryCollector)
    {
        $this->container = $container;
        $this->queryCollector = $queryCollector;
    }

    public function beforeQuery(
        Event $event,
        AbstractPdo $db,
        $params
    ) {
        /** @var Profiler $profiler */
        $profiler = $this->container->getProfiler();
        if (!$profiler) {
            $profiler = new Profiler();
        }

        $bindings = $db->getSQLVariables();
        $query = $db->getSQLStatement();
        $time = $profiler->getTotalElapsedSeconds();
        $this->queryCollector->addQuery($query, $bindings, $time, $db);
    }

    public function afterQuery(
        Event $event,
        AbstractPdo $db,
        $params
    ) {
        $profiler = $this->container->getProfiler();
        if ($profiler) {
            $profiler->stopProfile();
        }

    }
}
