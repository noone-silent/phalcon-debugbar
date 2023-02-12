<?php

namespace Nin\Debugbar;

use DebugBar\DataCollector\MessagesCollector;
use PHPUnit\Util\Exception;

trait DebugFunctions
{
    public function messageCollector(): MessagesCollector
    {
        if (!$this->hasCollector('messages')) {
            throw new Exception('The Phalcon Debugbar Message Collector has not been set.');
        }

        return $this->getCollector('messages');
    }

    public function addMessages($messages, $label)
    {
        foreach ($messages as $message) {
            if ($message instanceof \Phalcon\Config\Config) {
                $message = $message->toArray();
            }
            $this->messageCollector()->addMessage($message, $label);
        }
    }

    public function addMessage(...$messages)
    {
        $this->messageCollector()->addMessages($messages, 'info');
    }

    public function info(...$messages)
    {
        $this->addMessages($messages, 'info');
    }

    public function emergency(...$messages)
    {
        $this->addMessages($messages, 'emergency');
    }

    public function alert(...$messages)
    {
        $this->addMessages($messages, 'alert');
    }

    public function critical(...$messages)
    {
        $this->addMessages($messages, 'critical');
    }

    public function error(...$messages)
    {
        $this->addMessages($messages, 'error');
    }

    public function notice(...$messages)
    {
        $this->addMessages($messages, 'notice');
    }

    public function debug(...$messages)
    {
        $this->addMessages($messages, 'debug');
    }

    public function log(...$messages)
    {
        $this->addMessages($messages, 'log');
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string $label Public name
     * @throws \DebugBar\DebugBarException
     */
    public function startMeasure($name, $label = null): void
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->startMeasure($name, $label);
        }
    }

    /**
     * Stop a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string $label Public name
     * @throws \DebugBar\DebugBarException
     */
    public function stopMeasure($name, $label = null): void
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->stopMeasure($name, $label);
        }
    }

    /**
     * Add a measure
     *
     * @param string $label
     * @param float $start
     * @param float $end
     * @throws \DebugBar\DebugBarException
     */
    public function addMeasure($label, $start, $end): void
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->addMeasure($label, $start, $end);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param $label
     * @param \Closure $closure
     * @throws \DebugBar\DebugBarException
     */
    public function measure($label, \Closure $closure): void
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->measure($label, $closure);
        }
    }
}
