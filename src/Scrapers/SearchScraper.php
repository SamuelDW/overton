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

    // /**
    //  * Gets the link results for the search page
    //  * This could also be made generic and config driven, so that there is one scraper, and potentially more config. I will leave it for now otherwise I'll be spending hours on this
    //  * As there is only one xpath query made, I would make this a config driven thing
    //  * @param string $html
    //  * @param string $domain
    //  * @return string[]
    //  */
    // protected function extractLinks(string $html, array $config): array
    // {
    //     libxml_use_internal_errors(true);
    //     $dom = new DOMDocument();
    //     $dom->loadHTML($html);
    //     $xpath = new DOMXPath($dom);
    //     // $domainConfig = $this->config[$domain];
    //     $data = [];

    //     foreach ($config as $key => $info) {
    //         $nodes = $xpath->query($info['xpath']);
    //         foreach ($nodes as $node) {
    //             $href = trim($node->getAttribute($info['attribute']));
    //             if ($href) {
    //                $data[$key][] = $href; 
    //             }
    //         }
    //     }
    //     dd($data);

    //     return $data;
    // }
}