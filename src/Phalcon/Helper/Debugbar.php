<?php

namespace Nin\Debugbar\Phalcon\Helper;

use Nin\Debugbar\PhalconDebugbar;
use Phalcon\Di\Di;

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
 * @package Nin\Debugbar\Phalcon\Helper
 */
class Debugbar
{
    protected static function getDebugAccessor(): string
    {
        return PhalconDebugbar::class;
    }

    protected static function resolveDebugInstance()
    {
        $instance = self::getDebugAccessor();
        if (is_object($instance)) {
            return $instance;
        }

        /**
         * Get from container
         */
        $container = Di::getDefault();
        if (!$container->has($instance)) {
            throw new \Exception('The ' . $instance . ' has not been set.');
        }
        return $container->get($instance);
    }

    public static function __callStatic(string $method, $args)
    {
        $instance = static::resolveDebugInstance();

        if (!$instance) {
            throw new \Exception('The Debug root has not been set.');
        }

        return $instance->$method(...$args);
    }

}
