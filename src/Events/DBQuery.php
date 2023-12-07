<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\Events;

use Phalcon\Db\Adapter\Pdo\AbstractPdo;
use Phalcon\Db\Profiler;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Incubator\Debugbar\DataCollector\QueryCollector;

class DBQuery extends Injectable
{
    protected QueryCollector $queryCollector;

    public function __construct(DiInterface $container, QueryCollector $queryCollector)
    {
        $this->container = $container;
        $this->queryCollector = $queryCollector;
    }

    public function beforeQuery(Event $event, AbstractPdo $db, $params): void
    {
        /** @var Profiler|null $profiler */
        $profiler = $this->container->get('profiler');
        if ($profiler === null) {
            $profiler = new Profiler();
        }

        $bindings = $db->getSQLVariables();
        $query = $db->getSQLStatement();
        $time = $profiler->getTotalElapsedSeconds();
        $this->queryCollector->addQuery($query, $bindings, $time, $db);
    }

    public function afterQuery(Event $event, AbstractPdo $db, $params): void
    {
        /** @var Profiler|null $profiler */
        $profiler = $this->container->get('profiler');
        $profiler?->stopProfile();
    }
}
