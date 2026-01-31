<?php

declare(strict_types=1);

class MarkdownBuilder
{
    /**
     * Hugo用のMarkdownファイル（Front Matter付き）を生成する
     */
    public function build(array $article, string $speaker, string $originalUrl, string $imagePath = ''): string
    {
        $date = date('Y-m-d\TH:i:sP');
        $tags = implode("\n", array_map(fn($t) => "  - \"$t\"", $article['tags']));
        $lessonsBlock = $this->buildLessonsBlock($article['lessons']);

        $coverSection = '';
        if ($imagePath !== '') {
            $coverSection = <<<YAML
cover:
    image: "$imagePath"
    alt: "{$article['title']}"
    caption: "AIが生成したイメージ画像"
    relative: false
YAML;
        }

        $frontMatter = <<<YAML
---
title: "{$this->escapeYaml($article['title'])}"
date: $date
draft: false
description: "{$this->escapeYaml($article['summary'])}"
summary: "{$this->escapeYaml($article['summary'])}"
author: "TED要約ラボ"
speaker: "{$this->escapeYaml($speaker)}"
original_url: "$originalUrl"
tags:
$tags
categories:
  - "TED要約"
$coverSection
ShowToc: true
TocOpen: false
---
YAML;

        $body = $article['body'];

        // Append lessons section if not already in body
        $lessonsSection = <<<MD

---

## 💡 ビジネスへの3つの教訓

$lessonsBlock

---

*この記事は [{$this->escapeYaml($speaker)}のTEDトーク]($originalUrl) をAIが要約・翻案したものである。*
MD;

        return $frontMatter . "\n\n" . $body . "\n" . $lessonsSection . "\n";
    }

    /**
     * ファイルパスを生成する (content/posts/yyyy-mm-dd-slug.md)
     */
    public function buildFilePath(string $slug): string
    {
        $date = date('Y-m-d');
        $slug = $this->sanitizeSlug($slug);
        return "content/posts/{$date}-{$slug}.md";
    }

    /**
     * 画像パスを生成する (static/images/yyyy-mm-dd-slug.png)
     */
    public function buildImagePath(string $slug): string
    {
        $date = date('Y-m-d');
        $slug = $this->sanitizeSlug($slug);
        return "static/images/{$date}-{$slug}.png";
    }

    /**
     * Hugo上での画像参照パスを返す
     */
    public function buildImageUrl(string $slug): string
    {
        $date = date('Y-m-d');
        $slug = $this->sanitizeSlug($slug);
        return "/images/{$date}-{$slug}.png";
    }

    private function buildLessonsBlock(array $lessons): string
    {
        $lines = [];
        $num = 1;
        foreach ($lessons as $lesson) {
            $lines[] = "**{$num}.** {$lesson}";
            $num++;
        }
        return implode("\n\n", $lines);
    }

    private function escapeYaml(string $value): string
    {
        return str_replace(['"', "\n"], ['\\"', ' '], $value);
    }

    private function sanitizeSlug(string $slug): string
    {
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
