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
            'title' => [
                'xpath' => "//meta[@property='og:title']",
                'attribute' => 'content',
            ],
            'authors' => [
                'xpath' => "//div[contains(@class, 'gem-c-metadata')]//a[contains(@class, 'govuk-link')]",
                'multiple' => true,
            ],
        ],
    ];

    public function setUp(): void
    {
        $this->scraper = new SearchScraper('', $this->config, 'gov.uk');
    }

    public function testGetResults()
    {
        // $html = file_get_contents(dirname(__DIR__, 1) . '/Fixtures/search_results.html');
        // Not quite sure how to test this one as it is a curl only thing
        // $this->scraper->scrape([file_get_contents(dirname(__DIR__, 1) . '/Fixtures/search_results.html')]);

        // Check that the scraper url list is not empty
    }
}