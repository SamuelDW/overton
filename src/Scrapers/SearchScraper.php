<?php

declare(strict_types=1);

namespace App\Scrapers;

use App\Interface\PageScraperInterface;

/**
 * A basic scraper that uses config based searching
 * This allows any page to be scraped, rather than soething specific to govuk
 * (and stops having over 9000 scrapers)
 */
class SearchScraper extends AbstractScraper implements PageScraperInterface
{
    public function __construct(string $userAgent, array $config, string $baseUrl, string $cacheLocation = 'cache/')
    {
        parent::__construct($userAgent, $config, $baseUrl, $cacheLocation);
    }
}