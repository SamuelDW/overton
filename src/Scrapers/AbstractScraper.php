<?php

declare(strict_types=1);

namespace App\Scrapers;

use App\Interface\PageScraperInterface;
use DOMDocument;
use DOMXPath;
use RuntimeException;

abstract class AbstractScraper implements PageScraperInterface
{
    protected string $baseUrl;

    protected string $domain;

    protected string $cacheLocation = __DIR__ . '/cache';
    protected string $userAgent;
    protected array $urls = [];

    protected int $delay = 1;

    protected array $config;

    public function __construct(string $userAgent, array $config, ?string $baseUrl = null, ?string $cacheLocation = null, ?int $delay = 1)
    {
        $this->baseUrl = $baseUrl ?? '';
        $this->userAgent = $userAgent;
        $this->delay = $delay;
        $this->config = $config;

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
    public function scrape(array $urls, string $domain, string $baseUrl): void
    {
        if (!isset($this->config[$domain])) {
            throw new RuntimeException("No config found for domain: $domain");
        }
        $allLinks = [];

        $domainConfig = $this->config[$domain];

        foreach ($urls as $url) {
            $html = $this->getPageContent($url);
            $data = $this->extractData($html, $domainConfig);
            dd($baseUrl);
            
            // dd($this->normalizeLinks($data['links']));

            // $links = $this->extractLinks($html, $domainConfig);
            $allLinks = array_merge($allLinks, $this->normalizeLinks($data['links']));
            dd($allLinks);
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
    protected function normalizeLinks(array $links, string $baseUrl): array
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
    // abstract protected function extractLinks(string $html, array $config): array;

    public function extractData(string $html, array $config): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        // $domainConfig = $this->config[$domain];
        $data = [];

        foreach ($config as $key => $info) {
            $nodes = $xpath->query($info['xpath']);
            foreach ($nodes as $node) {
                $href = trim($node->getAttribute($info['attribute']));
                if ($href) {
                   $data[$key][] = $href; 
                }
            }
        }

        return $data;
    }
}
