<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\Phalcon\Helper;

use Phalcon\Di\Di;
use Phalcon\Incubator\Debugbar\PhalconDebugbar;

/**
 * Class Debug
 * @method static void emergency(...$message)
 * @method static void alert(...$message)
 * @method static void critical(...$message)
 * @method static void error(...$message)
 * @method static void warning(...$message)
 * @method static void notice(...$message)
 * @method static void info(...$message)
 * @method static void debug(...$message)
 * @method static void log(...$message)
 * @method static void startMeasure($name, $label = null)
 * @method static void stopMeasure($name, $label = null)
 * @method static void addMeasure($label, $start, $end)
 * @method static void measure($label, \Closure $closure)
 * @method static void addThrowable(\Throwable $e)
 *
 * @package Phalcon\Incubator\Debugbar\Phalcon\Helper
 */
class Debugbar
{
    protected static function resolveDebugInstance()
    {
        /**
         * Get from container
         */
        $di = Di::getDefault();
        if ($di === null) {
            throw new \RuntimeException('The di has not been set.');
        }

        if (!$di->has(PhalconDebugbar::class)) {
            throw new \RuntimeException(
                'The ' . PhalconDebugbar::class . ' has not been set.'
            );
        }

        return $di->get(PhalconDebugbar::class);
    }

    public static function __callStatic(string $method, $args)
    {
        $instance = static::resolveDebugInstance();

        if (!$instance) {
            throw new \RuntimeException('The Debug root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
