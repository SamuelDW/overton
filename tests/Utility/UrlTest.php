<?php

declare(strict_types=1);

namespace App\Tests\Utility;

use App\Utility\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    /**
     * This should return the base url from the url i.e https://www.domain
     * @uses \App\Utility\Url::getBaseUrl()
     * @return void
     */
    public function testGetBaseUrl(): void
    {
        $url = 'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest';

        $actual = Url::getBaseUrl($url);

        $this->assertEquals('https://www.gov.uk', $actual);
    }

    /**
     * This should return the base url from the url https://www.domain i.e domain
     * @uses \App\Utility\Url::getDomain()
     * @return void
     */
    public function testGetDomain(): void
    {
        $url = 'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest';

        $actual = Url::getDomain($url);

        $this->assertEquals('gov.uk', $actual);
    }
}