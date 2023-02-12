<?php

namespace Nin\Debugbar\DataCollector;

use DebugBar\DataCollector\MessagesCollector;

class LogsCollector extends MessagesCollector
{
    protected $lines = 124;

    public function __construct($path = null, $name = 'logs')
    {
        parent::__construct($name);

        $this->getStorageLogs($path);
    }

    /**
     * get logs apache in app/storage/logs
     *
     * @param string $path
     *
     * @return array
     */
    public function getStorageLogs($path)
    {
        if (!file_exists($path)) {
            return;
        }

        // Load the latest lines, guessing about 15x the number of log entries (for stack traces etc)
        $logs = $this->tailFile($path, $this->lines);

        foreach ($this->getLogs($logs) as $log) {
            $this->addMessage($log['header'] . $log['stack'], $log['level'], false);
        }
    }

    /**
     * By Ain Tohvri (ain)
     * http://tekkie.flashbit.net/php/tail-functionality-in-php
     * @param string $file
     * @param int $lines
     * @return array
     */
    protected function tailFile($file, $lines)
    {
        $handle = fopen($file, "r");
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];
        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) {
                break;
            }
        }
        fclose($handle);
        return array_reverse($text);
    }

    /**
     * Search a string for log entries
     *
     * @param array $lines
     * @return array
     */
    public function getLogs($lines)
    {
        $pattern = "/\[(\S+)\]\[(\S+)\]+/";
        $log = [];
        $tmpL = 0;
        foreach ($lines as $key => $line) {
            preg_match_all($pattern, $line, $matches);
            if (isset($matches[2][0])) {
                $level = strtolower($matches[2][0]);
                $header = $matches[1][0];
                $stack = str_replace('[' . $header . '][' . $matches[2][0] . ']', '', $line);
                $log[$key] = ['level' => $level, 'header' => $header, 'stack' => $stack];

                $tmpL = $key;
            } else {
                $log[$tmpL]['stack'] .= $line;
            }
        }
        $log = array_reverse($log);
        return $log;
    }

}
