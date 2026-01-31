<?php

/**
 * 処理済みマーカー
 *
 * Claude Codeが記事生成後に呼び出し、SQLiteに処理済みを記録する。
 * 対応するJSONファイルを data/pending/ から data/processed/ へ移動する。
 *
 * Usage:
 *   php mark-processed.php --url="https://..." --title="記事タイトル" --speaker="Speaker" --post-path="content/posts/xxx.md"
 */

declare(strict_types=1);

$libDir = __DIR__ . '/lib';
require_once $libDir . '/Config.php';
require_once $libDir . '/Database.php';

// Parse arguments
$options = getopt('', ['url:', 'title:', 'speaker:', 'post-path:']);

if (empty($options['url']) || empty($options['title']) || empty($options['speaker']) || empty($options['post-path'])) {
    echo "Usage: php mark-processed.php --url=\"URL\" --title=\"Title\" --speaker=\"Speaker\" --post-path=\"path/to/post.md\"" . PHP_EOL;
    exit(1);
}

try {
    $config = new Config();
    $db = new Database($config->get('SQLITE_DB_PATH', __DIR__ . '/../data/ted_blog.sqlite'));

    $db->markProcessed(
        $options['url'],
        $options['title'],
        $options['speaker'],
        $options['post-path']
    );

    echo "[OK] Marked as processed: {$options['title']}" . PHP_EOL;

    // Move corresponding JSON from pending to processed
    $pendingDir = dirname(__DIR__) . '/data/pending';
    $processedDir = dirname(__DIR__) . '/data/processed';

    foreach (glob($pendingDir . '/*.json') as $jsonFile) {
        $data = json_decode(file_get_contents($jsonFile), true);
        if ($data !== null && ($data['talk_url'] ?? '') === $options['url']) {
            $dest = $processedDir . '/' . basename($jsonFile);
            rename($jsonFile, $dest);
            echo "[OK] Moved JSON to processed: " . basename($jsonFile) . PHP_EOL;
            break;
        }
    }
} catch (Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . PHP_EOL;
    exit(1);
}
