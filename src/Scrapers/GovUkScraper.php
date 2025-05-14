<?php

declare(strict_types=1);

namespace App\Scrapers;

use App\Interface\PageScraperInterface;
use DOMDocument;
use DOMXPath;

class GovUkScraper extends AbstractScraper implements PageScraperInterface
{
    public function __construct(string $userAgent, ?string $baseUrl = 'http://www.gov.uk', ?string $cacheLocation = 'cache/govuk')
    {
        parent::__construct($userAgent, $baseUrl, $cacheLocation);
    }

    protected function extractLinks(string $html): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query("//div[@id='js-results']//li/div/a");
        $links = [];

        foreach ($nodes as $node) {
            $href = trim($node->getAttribute('href'));
            if ($href) {
                $links[] = $href;
            }
        }

        return $links;
    }
}