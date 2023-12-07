<?php

declare(strict_types=1);

namespace Phalcon\Incubator\Debugbar\DataCollector;

use DebugBar\Bridge\NamespacedTwigProfileCollector;
use Phalcon\Config\Config;

class ViewCollector extends NamespacedTwigProfileCollector
{
    use Formatter;

    /**
     * @var int
     */
    private $templateCount;

    /**
     * @var int
     */
    private $blockCount;

    /**
     * @var int
     */
    private $macroCount;

    private $templates = [];

    /**
     * @var Config
     */
    private $config;

    private $renderTime = 0;

    private $memoryUsage = 0;

    /**
     * A list of known editor strings.
     *
     * @var array
     */
    protected array $editors = [
        'sublime'                => 'subl://open?url=file://%file&line=%line',
        'textmate'               => 'txmt://open?url=file://%file&line=%line',
        'emacs'                  => 'emacs://open?url=file://%file&line=%line',
        'macvim'                 => 'mvim://open/?url=file://%file&line=%line',
        'phpstorm'               => 'phpstorm://open?file=%file&line=%line',
        'idea'                   => 'idea://open?file=%file&line=%line',
        'vscode'                 => 'vscode://file/%file:%line',
        'vscode-insiders'        => 'vscode-insiders://file/%file:%line',
        'vscode-remote'          => 'vscode://vscode-remote/%file:%line',
        'vscode-insiders-remote' => 'vscode-insiders://vscode-remote/%file:%line',
        'vscodium'               => 'vscodium://file/%file:%line',
        'nova'                   => 'nova://core/open/file?filename=%file&line=%line',
        'xdebug'                 => 'xdebug://%file@%line',
        'atom'                   => 'atom://core/open/file?filename=%file&line=%line',
        'espresso'               => 'x-espresso://open?filepath=%file&lines=%line',
        'netbeans'               => 'netbeans://open/?f=%file:%line',
    ];

    public function __construct($profile = null, $config = null)
    {
        if ($profile) {
            $this->addTemplate($profile);
        }
        $this->config = $config;
    }

    public function addTemplate($profile, $key): void
    {
        $this->templates[$key] = $profile;
    }

    public function getTemplateKey($key)
    {
        return str_replace(DIRECTORY_SEPARATOR, '_', $key);
    }

    public function getTemplateName($path, $baseDir)
    {
        if (!$baseDir) {
            return $path;
        }
        return str_replace($baseDir, '', $path);
    }

    public function getTemplate($key)
    {
        if ($key && isset($this->templates[$key])) {
            return $this->templates[$key];
        }
        return null;
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    public function collect(): array
    {
        $this->templateCount = $this->blockCount = $this->macroCount = 0;
        $templatesCount = count($this->templates);

        return [
            'nb_templates'                => $templatesCount,
            'templates'                   => $this->formatTemplate(),
            'accumulated_render_time'     => $this->renderTime,
            'accumulated_render_time_str' => $this->formatString($this->getDataFormatter()->formatDuration($this->renderTime)),
            'memory_usage_str'            => $this->formatString($this->getDataFormatter()->formatBytes($this->memoryUsage)),
            'badge'                       => implode(
                '/',
                [
                    $templatesCount,
                ]
            ),
        ];
    }

    public function formatTemplate(): array
    {
        $templates = [];
        $renderTime = 0;
        $memoryUsage = 0;
        foreach ($this->templates as $key => $profile) {
            $templates[] = [
                'name'            => $profile->getName(),
                'param_count'     => count($profile->getParams()),
                'params'          => $this->formatParams($profile->getParams()),
                'type'            => $profile->getType(),
                'render_time'     => $profile->getDuration(),
                'render_time_str' => $this->formatString($this->getDataFormatter()->formatDuration($profile->getDuration())),
                'memory_str'      => $this->formatString($this->getDataFormatter()->formatBytes($profile->getMemoryUsage())),
                'editorLink'      => $this->getEditorHref($profile->getPath(), 0),
            ];
            $renderTime += $profile->getDuration();
            $memoryUsage += $profile->getMemoryUsage();
        }
        $this->renderTime = $renderTime;
        $this->memoryUsage = $memoryUsage;
        return $templates;
    }

    protected function formatParams(array $params): array
    {
        foreach ($params as $key => $param) {
            if ($param instanceof Config) {
                $params[$key] = $this->formatVar($param->toArray());
            } elseif (!is_string($param)) {
                $params[$key] = $this->formatVar($param);
            }
        }
        return $params;
    }

    /**
     * Get the editor href for a given file and line, if available.
     *
     * @param string $filePath
     * @param int $line
     *
     * @throws \InvalidArgumentException If editor resolver does not return a string
     * @return null|string
     */
    protected function getEditorHref(string $filePath, int $line): ?string
    {
        if (!$this->config->get('editor')) {
            return null;
        }

        if (empty($this->editors[$this->config->get('editor')])) {
            throw new \InvalidArgumentException(
                'Unknown editor identifier: ' . $this->config->get('editor') . '. Known editors:' .
                implode(', ', array_keys($this->editors))
            );
        }

        $filePath = $this->replaceSitesPath($filePath);

        return str_replace(
            ['%file', '%line'],
            [$filePath, $line],
            $this->editors[$this->config->get('editor')]
        );
    }

    /**
     * Replace remote path
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function replaceSitesPath(string $filePath): string
    {
        return str_replace(
            $this->config->get('remote_sites_path'),
            $this->config->get('local_sites_path'),
            $filePath
        );
    }

    public function getName(): string
    {
        return 'views';
    }

    public function getWidgets(): array
    {
        return [
            'views'       => [
                'icon'    => 'leaf',
                'widget'  => 'PhpDebugBar.Widgets.LaravelViewTemplatesWidget',
                'map'     => 'views',
                'default' => '{"templates":[]}',
            ],
            'views:badge' => [
                'map'     => 'views.badge',
                'default' => 0,
            ],
        ];
    }
}
