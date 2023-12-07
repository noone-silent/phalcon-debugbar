<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\Phalcon\View;

class Profile implements \IteratorAggregate, \Serializable
{
    public const ROOT = '';
    public const BLOCK = 'block';
    public const TEMPLATE = 'template';
    public const MACRO = 'macro';

    private $baseDir;

    private $path;

    private $template;

    private $name;

    private $type;

    private $starts = [];

    private $ends = [];

    private $profiles = [];

    private $params = [];

    public function __construct(string $template = 'main', string $type = self::ROOT, string $name = 'main')
    {
        $this->template = $template;
        $this->type = $type;
        $this->name = str_starts_with($name, '__internal_') ? 'INTERNAL' : $name;
        $this->enter();
    }

    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setBaseDir($dir)
    {
        $this->baseDir = $dir;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function isRoot(): bool
    {
        return self::ROOT === $this->type;
    }

    public function isTemplate(): bool
    {
        return self::TEMPLATE === $this->type;
    }

    public function isBlock(): bool
    {
        return self::BLOCK === $this->type;
    }

    public function isMacro(): bool
    {
        return self::MACRO === $this->type;
    }

    /**
     * Returns the duration in microseconds.
     */
    public function getDuration(): float
    {
        return isset($this->ends['wt'], $this->starts['wt']) ? $this->ends['wt'] - $this->starts['wt'] : 0;
    }

    /**
     * Returns the memory usage in bytes.
     */
    public function getMemoryUsage(): int
    {
        return isset($this->ends['mu'], $this->starts['mu']) ? $this->ends['mu'] - $this->starts['mu'] : 0;
    }

    /**
     * Returns the peak memory usage in bytes.
     */
    public function getPeakMemoryUsage(): int
    {
        return isset($this->ends['pmu'], $this->starts['pmu']) ? $this->ends['pmu'] - $this->starts['pmu'] : 0;
    }

    /**
     * Starts the profiling.
     */
    public function enter(): void
    {
        $this->starts = [
            'wt'  => microtime(true),
            'mu'  => memory_get_usage(),
            'pmu' => memory_get_peak_usage(),
        ];
    }

    /**
     * Stops the profiling.
     */
    public function leave(): void
    {
        $this->ends = [
            'wt'  => microtime(true),
            'mu'  => memory_get_usage(),
            'pmu' => memory_get_peak_usage(),
        ];
    }

    public function reset(): void
    {
        $this->starts = $this->ends = $this->profiles = [];
        $this->enter();
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->profiles);
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function unserialize($data): void
    {
        $this->__unserialize(unserialize($data, ['allowed_classes' => null]));
    }

    /**
     * @internal
     */
    public function __serialize(): array
    {
        return [$this->template, $this->name, $this->type, $this->starts, $this->ends, $this->profiles];
    }

    /**
     * @internal
     */
    public function __unserialize(array $data): void
    {
        [$this->template, $this->name, $this->type, $this->starts, $this->ends, $this->profiles] = $data;
    }
}
