<?php

declare(strict_types=1);

class Config
{
    private array $config = [];

    public function __construct(string $envPath = null)
    {
        $envFile = $envPath ?? dirname(__DIR__, 2) . '/.env';
        if (!file_exists($envFile)) {
            throw new RuntimeException(".env file not found at: $envFile");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $this->config[trim($key)] = trim($value);
            }
        }
    }

    public function get(string $key, string $default = ''): string
    {
        return $this->config[$key] ?? $default;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $val = $this->config[$key] ?? ($default ? 'true' : 'false');
        return in_array(strtolower($val), ['true', '1', 'yes'], true);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int)($this->config[$key] ?? $default);
    }
}
