<?php

declare(strict_types=1);

namespace App\Tests\Scrapers;

use App\MetaData\MetaData;
use App\Scrapers\MetaDataScraper;
use PHPUnit\Framework\TestCase;

class MetaDataScraperTest extends TestCase
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
        $this->scraper = new MetaDataScraper($this->config);
    }

    /**
     * Test that when passed a html document with one author, it grabs the one author along with the title
     * @return void
     */
    public function testScrapesTitleAndSingleAuthorCorrectly()
    {
        $html = file_get_contents(dirname(__DIR__, 1) . '/Fixtures/one_author.html');

        $result = $this->scraper->scrape($html, 'gov.uk');

        $this->assertSame('Online Safety Bill: supporting documents', $result['title']);
        $this->assertSame(['Department for Digital, Culture, Media & Sport'], $result['authors']);
        $this->assertEquals(1, count($result['authors']));
    }

    public function testTwoAuthors()
    {
        $html = file_get_contents(dirname(__DIR__, 1) . '/Fixtures/two_authors.html');


        $result = $this->scraper->scrape($html, 'gov.uk');
        $expectedAuthors = [
            'HM Revenue & Customs',
            'Academy for Justice Commissioning'
        ];

        $this->assertSame('The Excise Duties (Northern Ireland etc. miscellaneous modifications and amendments) (EU Exit) Regulations 2022', $result['title']);
        $this->assertSame($expectedAuthors, $result['authors']);
        $this->assertEquals(2, count($result['authors']));
    }

    /**
     * Test that the title is returned properly
     * @return void
     */
    public function testAccentedTitle()
    {
        $html = file_get_contents(dirname(__DIR__, 1) . '/Fixtures/multiple_authors_accented_title.html');

        $result = $this->scraper->scrape($html, 'gov.uk');
        $expectedAuthors = [
            'Department for Education',
            'The Scottish Government',
            'Welsh Government',
            'Department of Education (Northern Ireland)',
            'Department for the Economy (Northern Ireland)',
            'The Rt Hon Michael Gove MP'
        ];

        $this->assertSame('Communiqués from the Interministerial Group for Education', $result['title']);
        $this->assertSame($expectedAuthors, $result['authors']);
        $this->assertEquals(6, count($result['authors']));
    }

    /**
     * Test that a new meta data object is created with the correct fields
     * @return void
     */
    public function testCreateMetaData()
    {
        $html = file_get_contents(dirname(__DIR__, 1) . '/Fixtures/one_author.html');

        $result = $this->scraper->scrape($html, 'gov.uk');

        $metaData = new MetaData();
        $newMetaData = $this->scraper->createMetaData($result, $metaData, 'one_author.html');

        $this->assertInstanceOf('\App\MetaData\MetaData', $newMetaData);
        $this->assertNotNull($newMetaData->title);
        $this->assertIsArray($newMetaData->authors);
        $this->assertNotNull($newMetaData->url);
        $this->assertEquals('one_author.html', $newMetaData->url);
    }
}
