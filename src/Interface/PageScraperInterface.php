<?php

declare(strict_types=1);

namespace App\Interface;

interface PageScraperInterface
{
    /**
     * Scrape the urls
     * @param array $urls
     * @return void
     */
    public function scrape(array $urls);

    public function getPageContent(string $url);
}