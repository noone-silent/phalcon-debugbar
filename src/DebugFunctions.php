<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar;

use Closure;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBarException;
use Phalcon\Config\Config;
use RuntimeException;
use Throwable;

trait DebugFunctions
{
    /**
     * @throws DebugBarException
     * @return MessagesCollector
     */
    public function messageCollector(): MessagesCollector
    {
        if (!$this->hasCollector('messages')) {
            throw new RuntimeException('The Phalcon Debugbar Message Collector has not been set.');
        }

        $collector = $this->getCollector('messages');
        if ($collector instanceof MessagesCollector === false) {
            throw new RuntimeException('Collector is not of MessageCollector type');
        }

        return $collector;
    }

    public function addMessages($messages, $label): void
    {
        try {
            $collector = $this->messageCollector();
        } catch (Throwable) {
            return;
        }

        foreach ($messages as $message) {
            if ($message instanceof Config) {
                $message = $message->toArray();
            }
            $collector->addMessage($message, $label);
        }
    }

    public function addMessage(...$messages): void
    {
        $this->addMessages($messages, 'info');
    }

    public function info(...$messages): void
    {
        $this->addMessages($messages, 'info');
    }

    public function emergency(...$messages): void
    {
        $this->addMessages($messages, 'emergency');
    }

    public function alert(...$messages): void
    {
        $this->addMessages($messages, 'alert');
    }

    public function critical(...$messages): void
    {
        $this->addMessages($messages, 'critical');
    }

    public function error(...$messages): void
    {
        $this->addMessages($messages, 'error');
    }

    public function notice(...$messages): void
    {
        $this->addMessages($messages, 'notice');
    }

    public function debug(...$messages): void
    {
        $this->addMessages($messages, 'debug');
    }

    public function log(...$messages): void
    {
        $this->addMessages($messages, 'log');
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string|null $label Public name
     *
     * @throws DebugBarException
     */
    public function startMeasure(string $name, ?string $label = null): void
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->startMeasure($name, $label);
        }
    }

    /**
     * Stop a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string|null $label Public name
     *
     * @throws DebugBarException
     */
    public function stopMeasure(string $name, ?string $label = null): void
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $collector */
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
     *
     * @throws DebugBarException
     */
    public function addMeasure(string $label, float $start, float $end): void
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->addMeasure($label, $start, $end);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string $label
     * @param Closure $closure
     *
     * @throws DebugBarException
     */
    public function measure(string $label, Closure $closure): void
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->measure($label, $closure);
        }
    }
}
