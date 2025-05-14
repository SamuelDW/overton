<?php

declare(strict_types=1);

namespace App\Scrapers;

use App\Interface\PageScraperInterface;
use RuntimeException;

abstract class AbstractScraper implements PageScraperInterface
{
    protected string $baseUrl;
    protected string $cacheLocation = __DIR__ . '/cache';
    protected string $userAgent;
    protected array $urls = [];

    protected int $delay = 1;

    public function __construct(string $userAgent, ?string $baseUrl = null, ?string $cacheLocation = null, ?int $delay = 1)
    {
        $this->baseUrl = $baseUrl ?? '';
        $this->userAgent = $userAgent;
        $this->delay = $delay;

        $this->cacheLocation = $cacheLocation ?? dirname(__DIR__, 2) . '/cache';

        if (!is_dir($this->cacheLocation)) {
            mkdir($this->cacheLocation, 0777, true);
        }
    }

    /**
     * Gets the links from the pages passed in
     * @param array $urls
     * @return void
     */
    public function scrape(array $urls): void
    {
        $allLinks = [];

        foreach ($urls as $url) {
            $html = $this->getPageContent($url);
            $links = $this->extractLinks($html);
            $allLinks = array_merge($allLinks, $this->normalizeLinks($links));
            sleep($this->delay);
        }

        $this->urls = $allLinks;
    }

    /**
     * Either fetches the html from the cache, or sends a request to get the page, and then storing it in the cache
     * @param string $url url of the page
     * @throws \RuntimeException
     * @return bool|string
     */
    public function getPageContent(string $url): string
    {
        $cacheKey = $this->cacheFilename($url);

        if (file_exists($cacheKey)) {
            return file_get_contents($cacheKey);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $this->userAgent,
        ]);
        $html = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RuntimeException('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);
        file_put_contents($cacheKey, $html);

        return $html;
    }

    protected function cacheFilename(string $url): string
    {
        return $this->cacheLocation . '/' . md5($url) . '.html';
    }

    /**
     * Formats any relative urls to absolute urls
     * @param array $links
     * @return array
     */
    protected function normalizeLinks(array $links): array
    {
        return array_map(
            fn($link) => str_starts_with($link, 'http') ? $link : rtrim($this->baseUrl, '/') . '/' . ltrim($link, '/'),
            $links
        );
    }

    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * Subclasses must implement this to define how links are extracted.
     */
    abstract protected function extractLinks(string $html): array;
}
