<?php

declare(strict_types=1);

class TranscriptScraper
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * TED動画ページからトランスクリプト（英語）を取得する
     */
    public function getTranscript(string $talkUrl): ?string
    {
        $this->logger->info("Scraping transcript for: $talkUrl");

        // TED transcript page URL
        $transcriptUrl = rtrim($talkUrl, '/') . '/transcript';

        $html = $this->fetchPage($transcriptUrl);
        if ($html === null) {
            // Fallback: try the main talk page
            $html = $this->fetchPage($talkUrl);
            if ($html === null) {
                return null;
            }
        }

        // Method 1: Extract from __NEXT_DATA__ JSON (modern TED pages)
        $transcript = $this->extractFromNextData($html);
        if ($transcript !== null) {
            return $transcript;
        }

        // Method 2: Extract from ld+json structured data
        $transcript = $this->extractFromLdJson($html);
        if ($transcript !== null) {
            return $transcript;
        }

        // Method 3: Extract from HTML transcript blocks
        $transcript = $this->extractFromHtmlBlocks($html);
        if ($transcript !== null) {
            return $transcript;
        }

        $this->logger->warn("Could not extract transcript for: $talkUrl");
        return null;
    }

    /**
     * 動画ページからメタデータを取得する
     * @return array{title: string, speaker: string, duration: int, thumbnail: string, description: string}|null
     */
    public function getMetadata(string $talkUrl): ?array
    {
        $html = $this->fetchPage($talkUrl);
        if ($html === null) {
            return null;
        }

        // Extract from ld+json
        if (preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $matches)) {
            $data = json_decode($matches[1], true);
            if ($data !== null) {
                return [
                    'title'       => $data['name'] ?? '',
                    'speaker'     => $data['author']['name'] ?? '',
                    'duration'    => $this->parseDuration($data['duration'] ?? ''),
                    'thumbnail'   => $data['thumbnailUrl'] ?? '',
                    'description' => $data['description'] ?? '',
                ];
            }
        }

        return null;
    }

    private function fetchPage(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (compatible; TED-Blog-Bot/1.0)',
                'header' => "Accept-Language: en-US,en;q=0.9\r\n",
            ],
        ]);

        $html = @file_get_contents($url, false, $context);
        if ($html === false) {
            $this->logger->error("Failed to fetch page: $url");
            return null;
        }

        return $html;
    }

    private function extractFromNextData(string $html): ?string
    {
        if (!preg_match('/<script[^>]*id=["\']__NEXT_DATA__["\'][^>]*>(.*?)<\/script>/si', $html, $matches)) {
            return null;
        }

        $data = json_decode($matches[1], true);
        if ($data === null) {
            return null;
        }

        // Navigate through the Next.js data structure to find transcript
        $paragraphs = $data['props']['pageProps']['transcriptData']['translation']['paragraphs'] ?? null;
        if ($paragraphs === null) {
            return null;
        }

        $text = '';
        foreach ($paragraphs as $paragraph) {
            foreach ($paragraph['cues'] as $cue) {
                $text .= $cue['text'] . ' ';
            }
            $text .= "\n\n";
        }

        $transcript = trim($text);
        if ($transcript === '') {
            return null;
        }

        $this->logger->info("Extracted transcript via __NEXT_DATA__ (" . strlen($transcript) . " chars)");
        return $transcript;
    }

    private function extractFromLdJson(string $html): ?string
    {
        if (!preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $matches)) {
            return null;
        }

        $data = json_decode($matches[1], true);
        if ($data === null) {
            return null;
        }

        $transcript = $data['transcript'] ?? null;
        if ($transcript !== null && is_string($transcript)) {
            $this->logger->info("Extracted transcript via ld+json (" . strlen($transcript) . " chars)");
            return $transcript;
        }

        return null;
    }

    private function extractFromHtmlBlocks(string $html): ?string
    {
        // Look for transcript paragraph blocks
        if (preg_match_all('/<p[^>]*class=["\'][^"\']*transcript__paragraph[^"\']*["\'][^>]*>(.*?)<\/p>/si', $html, $matches)) {
            $text = implode("\n\n", array_map('strip_tags', $matches[1]));
            $text = trim($text);
            if ($text !== '') {
                $this->logger->info("Extracted transcript via HTML blocks (" . strlen($text) . " chars)");
                return $text;
            }
        }

        return null;
    }

    /**
     * ISO 8601 duration (e.g., "PT15M30S") to seconds
     */
    private function parseDuration(string $duration): int
    {
        if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $m)) {
            return ((int)($m[1] ?? 0)) * 3600 + ((int)($m[2] ?? 0)) * 60 + ((int)($m[3] ?? 0));
        }
        return 0;
    }
}
