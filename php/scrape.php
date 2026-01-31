<?php

/**
 * TED RSS スクレイピングバッチ
 *
 * RSSフィードから新着TED動画を取得し、トランスクリプトをJSON形式で保存する。
 * Claude Codeが記事生成に使用するデータを準備するスクリプト。
 *
 * Usage:
 *   php scrape.php
 */

declare(strict_types=1);

$libDir = __DIR__ . '/lib';
require_once $libDir . '/Config.php';
require_once $libDir . '/Logger.php';
require_once $libDir . '/Database.php';
require_once $libDir . '/RssFetcher.php';
require_once $libDir . '/TranscriptScraper.php';

// ========================================
// 1. Initialize
// ========================================
try {
    $config = new Config();
} catch (RuntimeException $e) {
    echo "[ERROR] " . $e->getMessage() . PHP_EOL;
    echo "Copy .env.example to .env and fill in the settings." . PHP_EOL;
    exit(1);
}

$logger  = new Logger($config->get('LOG_LEVEL', 'info'));
$db      = new Database($config->get('SQLITE_DB_PATH', __DIR__ . '/../data/ted_blog.sqlite'));
$rss     = new RssFetcher($logger);
$scraper = new TranscriptScraper($logger);

$maxPosts = $config->getInt('MAX_POSTS_PER_RUN', 3);

// Ensure output directories exist
$pendingDir = dirname(__DIR__) . '/data/pending';
$processedDir = dirname(__DIR__) . '/data/processed';
if (!is_dir($pendingDir)) {
    mkdir($pendingDir, 0755, true);
}
if (!is_dir($processedDir)) {
    mkdir($processedDir, 0755, true);
}

$logger->info('=== TED Scraper Started ===');
$logger->info("Max posts per run: $maxPosts");

// ========================================
// 2. Fetch RSS & Filter
// ========================================
$talks = $rss->fetch();
if (empty($talks)) {
    $logger->warn('No talks found in RSS feed');
    exit(0);
}

// Filter: DB処理済み + pending/にJSON既存のものを除外
$newTalks = [];
foreach ($talks as $talk) {
    if ($db->isProcessed($talk['url'])) {
        continue;
    }
    // Check if already scraped (JSON exists in pending)
    $slug = sanitizeSlug($talk['title']);
    $pattern = $pendingDir . '/*-' . $slug . '.json';
    if (!empty(glob($pattern))) {
        continue;
    }
    $newTalks[] = $talk;
}

$logger->info(count($newTalks) . " new talks found (out of " . count($talks) . " total)");

if (empty($newTalks)) {
    $logger->info('No new talks to scrape');
    exit(0);
}

$newTalks = array_slice($newTalks, 0, $maxPosts);

// ========================================
// 3. Scrape Each Talk
// ========================================
$scrapedCount = 0;

foreach ($newTalks as $talk) {
    $logger->info("--- Scraping: {$talk['title']} ---");

    $transcript = $scraper->getTranscript($talk['url']);
    if ($transcript === null) {
        $logger->warn("Skipping (no transcript available): {$talk['url']}");
        continue;
    }

    $slug = sanitizeSlug($talk['title']);
    $date = date('Y-m-d');
    $filename = "{$date}-{$slug}.json";

    $data = [
        'talk_url'       => $talk['url'],
        'original_title' => $talk['title'],
        'speaker'        => $talk['speaker'],
        'description'    => $talk['description'],
        'pub_date'       => $talk['pubDate'],
        'transcript'     => $transcript,
        'word_count'     => str_word_count($transcript),
        'scraped_at'     => date('c'),
    ];

    $jsonPath = $pendingDir . '/' . $filename;
    file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $logger->info("Saved: $filename (" . $data['word_count'] . " words)");
    $scrapedCount++;

    // Rate limit: avoid rapid requests to TED
    if ($scrapedCount < count($newTalks)) {
        sleep(3);
    }
}

$logger->info("=== Scraping Complete: $scrapedCount talks saved to data/pending/ ===");

// ========================================
// Helper
// ========================================
function sanitizeSlug(string $title): string
{
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}
