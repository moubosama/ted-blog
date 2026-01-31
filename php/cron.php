<?php

/**
 * [非推奨] このファイルは後方互換のために残してある。
 *
 * 新しいワークフロー:
 *   1. php scrape.php       → TED RSSからトランスクリプトを取得しJSONで保存
 *   2. Claude Codeが記事生成  → CLAUDE_INSTRUCTIONS.md を参照
 *   3. php mark-processed.php → SQLiteに処理済み記録
 *
 * 詳細は CLAUDE_INSTRUCTIONS.md を参照。
 */

echo "=== cron.php is deprecated ===" . PHP_EOL;
echo "Use the new workflow instead:" . PHP_EOL;
echo "  1. php php/scrape.php          (scrape new TED talks)" . PHP_EOL;
echo "  2. Ask Claude Code to generate articles (see CLAUDE_INSTRUCTIONS.md)" . PHP_EOL;
echo "  3. php php/mark-processed.php  (mark as processed)" . PHP_EOL;
echo PHP_EOL;
echo "Running scrape.php for you..." . PHP_EOL;
echo PHP_EOL;

require_once __DIR__ . '/scrape.php';
