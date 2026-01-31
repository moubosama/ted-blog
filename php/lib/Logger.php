<?php

declare(strict_types=1);

class Logger
{
    private string $logFile;
    private string $level;

    private const LEVELS = ['debug' => 0, 'info' => 1, 'warn' => 2, 'error' => 3];

    public function __construct(string $level = 'info')
    {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logFile = $logDir . '/cron_' . date('Y-m-d') . '.log';
        $this->level = strtolower($level);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warn(string $message, array $context = []): void
    {
        $this->log('warn', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    private function log(string $level, string $message, array $context): void
    {
        if (self::LEVELS[$level] < self::LEVELS[$this->level]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $tag = strtoupper($level);
        $line = "[$timestamp] [$tag] $message";

        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        file_put_contents($this->logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);

        // Also echo to stdout for cron visibility
        echo $line . PHP_EOL;
    }
}
