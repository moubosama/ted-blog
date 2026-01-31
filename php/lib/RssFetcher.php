<?php

declare(strict_types=1);

class RssFetcher
{
    private const RSS_URL = 'https://www.ted.com/talks/rss';
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * RSSフィードから動画情報の配列を返す
     * @return array<array{title: string, url: string, speaker: string, description: string, pubDate: string}>
     */
    public function fetch(): array
    {
        $this->logger->info('Fetching TED RSS feed...');

        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'TED-Blog-Bot/1.0',
            ],
        ]);

        $xml = @file_get_contents(self::RSS_URL, false, $context);
        if ($xml === false) {
            $this->logger->error('Failed to fetch RSS feed');
            return [];
        }

        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);
        if ($feed === false) {
            $this->logger->error('Failed to parse RSS XML');
            return [];
        }

        $talks = [];
        foreach ($feed->channel->item as $item) {
            $namespaces = $item->getNamespaces(true);

            // Extract speaker from itunes:author or dc:creator
            $speaker = '';
            if (isset($namespaces['itunes'])) {
                $itunes = $item->children($namespaces['itunes']);
                $speaker = (string)($itunes->author ?? '');
            }

            $link = (string)$item->link;
            // Normalize URL: remove query parameters
            $link = strtok($link, '?');

            $talks[] = [
                'title'       => (string)$item->title,
                'url'         => $link,
                'speaker'     => $speaker,
                'description' => (string)$item->description,
                'pubDate'     => (string)$item->pubDate,
            ];
        }

        $this->logger->info("Fetched " . count($talks) . " talks from RSS feed");
        return $talks;
    }
}
