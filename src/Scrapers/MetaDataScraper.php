<?php

namespace App\Scrapers;

use App\MetaData\MetaData;
use DOMDocument;
use DOMXPath;

/**
 * This will scrape a fetched resource for a very specific xpath, not the best, could use a better way as this is highly specific for the GOVUK
 * We do need the html, which could be a simple thing, and then we pass in a metadata object, which gets the title, author etc
 */
class MetaDataScraper
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function scrape(string $html, string $domain): array
    {
        if (!isset($this->config[$domain])) {
            throw new \RuntimeException("No config found for domain: $domain");
        }

        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $domainConfig = $this->config[$domain];

        $data = [];

        foreach ($domainConfig as $key => $info) {
            $nodes = $xpath->query($info['xpath']);

            if (!$nodes || $nodes->length === 0) {
                $data[$key] = ($info['multiple'] ?? false) ? [] : null;
                continue;
            }

            if (isset($info['attribute'])) {
                // Single node, return attribute value (e.g. og:title)
                $data[$key] = $nodes->item(0)?->getAttribute($info['attribute']) ?? null;
            } else {
                // Multiple text nodes (e.g. authors)
                $values = [];
                foreach ($nodes as $node) {
                    $values[] = trim($node->textContent);
                }
                $data[$key] = $values;
            }
        }

        return $data;
    }

    /**
     * Loop through the keys so that every field doesn't need to be called 
     * @param array $scrapedData
     * @param \App\MetaData\MetaData $metaData
     * @param string $url
     * @return MetaData
     */
    public function createMetaData(array $scrapedData, MetaData $metaData, string $url): MetaData
    {
        foreach (['title', 'authors'] as $key) {
            if (property_exists($metaData, $key) && array_key_exists($key, $scrapedData)) {
                $metaData->$key = $scrapedData[$key];
            }
        }

        $metaData->url = $url;

        return $metaData;
    }
}
