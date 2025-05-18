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

    protected array $data;

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
     * Extracts data from the urls given, data is grabbed from the config of the domain passed in.
     * It then sets the data of the object to an aggregated version of each page
     * @param array $urls
     * @return void
     */
    public function scrape(array $urls, string $domain): void
    {
        if (!isset($this->config[$domain])) {
            throw new RuntimeException("No config found for domain: $domain");
        }

        $aggregatedData = [];
        $domainConfig = $this->config[$domain];

        foreach ($urls as $url) {
            $html = $this->getPageContent($url);
            $data = $this->extractData($html, $domainConfig);

            foreach ($data as $key => $value) {
                $aggregatedData[$key] = array_merge($aggregatedData[$key] ?? [], $value);
            }

            sleep($this->delay);
        }

        $this->data = $aggregatedData;
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
     * Formats any relative urls to absolute urls with the base url passed in
     * i.e /government/policies/this-is-a-test with baseUrl https://www.gov.uk
     * becomes https://www.gov.uk/government
     * @param array $links
     * @return array
     */
    public function normalizeLinks(array $links, string $baseUrl): array
    {
        return array_map(function ($link) use ($baseUrl) {
            $link = trim($link);

            // Already an absolute URL
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                return $link;
            }

            // Normalize base and relative path
            return rtrim($baseUrl, '/') . '/' . ltrim($link, '/');
        }, $links);
    }

    public function getUrls(): array
    {
        return $this->data['links'];
    }

    /**
     * Gets all the data according to the config passed in
     * @param string $html the html page
     * @param array $config the domain conig
     * @return string[][]
     */
    public function extractData(string $html, array $config): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $data = [];

        foreach ($config as $key => $info) {
            $nodes = $xpath->query($info['xpath']);
            $values = [];
            foreach ($nodes as $node) {
                $values[] = trim($node->getAttribute($info['attribute']));
            }
            $data[$key] = $values;
        }

        return $data;
    }
}
