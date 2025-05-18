<?php

declare(strict_types=1);

namespace App\Tests\Scrapers;

use App\Scrapers\SearchScraper;
use PHPUnit\Framework\TestCase;

class SearchScraperTest extends TestCase
{
    private $scraper;

    private $config = [
        'gov.uk' => [
            'links' => [
                'xpath' => "//div[@id='js-results']//li/div/a",
                'attribute' => 'href',
            ],
        ],
    ];

    public function setUp(): void
    {
        $this->scraper = new SearchScraper('', $this->config, 'gov.uk');
    }
    // Come back to this one later
    // public function testGetResults()
    // {
    //     $html = file_get_contents(dirname(__DIR__, 1) . '/Fixtures/search_results.html');
    //     // Not quite sure how to test this one as it is a curl only thing
    //     $this->scraper->scrape([file_get_contents(dirname(__DIR__, 1) . '/Fixtures/search_results.html')], 'gov.uk');

    //     $this->assertNotNull($this->scraper->getUrls());

    //     // Check that the scraper url list is not empty
    // }
}