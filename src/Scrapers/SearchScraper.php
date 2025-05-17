<?php

declare(strict_types=1);

namespace App\Scrapers;

use App\Interface\PageScraperInterface;
use DOMDocument;
use DOMXPath;

// Want to make this generic and use a config based scraper
class SearchScraper extends AbstractScraper implements PageScraperInterface
{
    public function __construct(string $userAgent, array $config, string $baseUrl, string $cacheLocation = 'cache/')
    {
        parent::__construct($userAgent, $config, $baseUrl, $cacheLocation);
    }
}