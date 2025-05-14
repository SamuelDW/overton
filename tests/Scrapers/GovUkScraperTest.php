<?php

declare(strict_types=1);

namespace App\Tests\Scrapers;

use App\Scrapers\GovUkScraper;
use PHPUnit\Framework\TestCase;

class GovUkScraperTest extends TestCase
{
    private $scraper;

    public function setUp(): void
    {
        $this->scraper = new GovUkScraper('OvertonBot/1.0 (+https://www.overton.io)');
    }

    public function testGetResults()
    {
        // $html = file_get_contents(dirname(__DIR__, 1) . '/Fixtures/search_results.html');
        // Not quite sure how to test this one as it is a curl only thing
        // $this->scraper->scrape([file_get_contents(dirname(__DIR__, 1) . '/Fixtures/search_results.html')]);

        // Check that the scraper url list is not empty
    }
}