<?php

namespace App\Scrapers;

use DOMDocument;
use DOMXPath;

/**
 * This will scrape a fetched resource for a very specific xpath, not the best, could use a better way. 
 * We do need the html, which could be a simple thing, and then we pass in a metadata object, which gets the title, author etc
 */
class MetaDataScraper
{
    public function scrape(string $html): array
    {
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $xpath = new DOMXPath($doc);

        // Title
        $titleNode = $xpath->query("//meta[@property='og:title']")->item(0);
        $title = $titleNode?->getAttribute('content') ?? '';

        // Authors
        $authorNodes = $xpath->query("//div[contains(@class, 'gem-c-metadata')]//a[contains(@class, 'govuk-link')]");
        $authors = [];

        foreach ($authorNodes as $node) {
            $authors[] = trim($node->textContent);
        }

        return [
            'title' => $title,
            'authors' => $authors,
        ];
    }
}
