<?php

declare(strict_types=1);

class Database
{
    private PDO $pdo;

    public function __construct(string $dbPath)
    {
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->pdo = new PDO("sqlite:$dbPath", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->migrate();
    }

    private function migrate(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS processed_talks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                video_url TEXT UNIQUE NOT NULL,
                title TEXT NOT NULL,
                speaker TEXT,
                post_path TEXT,
                image_path TEXT,
                processed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                status TEXT DEFAULT 'completed'
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS run_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                finished_at DATETIME,
                talks_processed INTEGER DEFAULT 0,
                errors TEXT,
                status TEXT DEFAULT 'running'
            )
        ");
    }

    public function isProcessed(string $videoUrl): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM processed_talks WHERE video_url = ?");
        $stmt->execute([$videoUrl]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function markProcessed(
        string $videoUrl,
        string $title,
        string $speaker,
        string $postPath,
        string $imagePath = ''
    ): void {
        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO processed_talks (video_url, title, speaker, post_path, image_path)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$videoUrl, $title, $speaker, $postPath, $imagePath]);
    }

    public function startRun(): int
    {
        $this->pdo->exec("INSERT INTO run_log (status) VALUES ('running')");
        return (int)$this->pdo->lastInsertId();
    }

    public function finishRun(int $runId, int $talksProcessed, string $status = 'completed', string $errors = ''): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE run_log
            SET finished_at = CURRENT_TIMESTAMP, talks_processed = ?, status = ?, errors = ?
            WHERE id = ?
        ");
        $stmt->execute([$talksProcessed, $status, $errors, $runId]);
    }

    public function getProcessedCount(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM processed_talks")->fetchColumn();
    }
}
