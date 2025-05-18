<?php

namespace App\Command;

use App\MetaData\MetaData;
use App\Scrapers\MetaDataScraper;
use App\Scrapers\SearchScraper;
use App\Utility\Url;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeLinksCommand extends Command
{
    public function __construct()
    {
        parent::__construct('scrape');
    }

    protected function configure(): void
    {
        // Setting the user agent via command line, optional there is a default to fall back to
        // This could be set somewhere else, env file or config file
        // In thinking this really should be a very config driven application

        // could even make this incredibly generic, pass in the url, the delimiter and number of results wanted to parse
        // Make the parser really generic and pass in the config option
        $this
            ->setDescription('Scrape a specific link')
            ->addOption(
                'user-agent',
                null,
                InputOption::VALUE_OPTIONAL,
                'The user agent to use. The default is: OvertonBot/1.0 (+https://www.overton.io)',
                'OvertonBot/1.0 (+https://www.overton.io)'
            )
            ->addOption('number-of-urls', null, InputOption::VALUE_OPTIONAL, 'numberOfResultsToParse', 50);
        // Could set an option to set the delimiter for paginatation, and the url for specific searches, and then could loop through till hitting a error page
    }

    /**
     * Executes the Scraper
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        #region Stage 1. Downloading the data from gov.uk
        $userAgent = $input->getOption('user-agent');
        $requestedAmountOfUrls = $input->getOption('number-of-urls');
        $searchConfig = require 'config/search_config.php';

        // These could potentially be stored, maybe the base url is everything up to the pagination, we store the pagination type i.e page=2 timestamp for last access so that know how long its 
        // been since last accessed
        $listingUrls = [
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest',
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=2',
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=3'
        ];

        // This part is a bit inflexible at the moment, could have mixed urls so could do this in a loop for this simple one, otherwise, more workers
        $url = $listingUrls[0];
        $baseUrl = Url::getBaseUrl($url);
        $domain = Url::getDomain($url);
        $output->writeln([
            "Base URL: $baseUrl",
            "Domain: $domain",
            "Number of urls to parse after fetching results: $requestedAmountOfUrls",
            "User agent: $userAgent",
        ]);

        $searchScraper = new SearchScraper($userAgent, $searchConfig, $domain);
        $searchScraper->scrape($listingUrls, $domain);
        $urls = $searchScraper->getUrls();
        $normalizedUrls = $searchScraper->normalizeLinks($urls, $baseUrl);
        $numberOfUrls = count($urls);

        $output->writeln([
            "URLS Found: $numberOfUrls",
            "URLS to parse: $requestedAmountOfUrls"
        ]);

        $requestedUrls = array_slice($normalizedUrls, 0, $requestedAmountOfUrls);
        #endregion

        #region Stage 2. Processing each link
        // This should either be injected in or in a framework usually available as a default helper but potentially could be stored in the database (NoSQL) against the base domain or similar
        $config = require 'config/scraper_config.php';
        $scraper = new MetadataScraper($config);
        $pageMetaData = [];

        // This is a really big bottleneck so to improve this, either a queue or task manager, plenty of threads, either a worker can tackle one url at a time
        // Or a chunk at a time
        // Then a worker fetches the html, grabs the meta data and logs/stores that
        foreach ($requestedUrls as $url) {
            $html = $this->fetchPage($url, $userAgent);

            if (!$html) {
                // Should log this so that it can be looked into
                $output->writeln("<error>Failed to fetch $url</error>");
                continue;
            }

            // Scrape and pass in the config value, there could be better ways to do this, although I imagine these will all look roughly the same other than syntax
            $scrapedData = $scraper->scrape($html, $domain);
            $metaData = new MetaData();
            $metaData = $scraper->createMetaData($scrapedData, $metaData, $url);
            $this->writeMetaData($output, $metaData);
            $pageMetaData[] = $metaData;
        }
        // With all the entities just created, save them all to a database for post processing. Or could save them one at a time, though that does create a lot of input to the database
        #endregion

        return Command::SUCCESS;
    }

    /**
     * Get an individual page that is not cached for grabbing the meta data
     * @param string $url
     * @param string $userAgent
     * @return bool|string|null
     */
    private function fetchPage(string $url, string $userAgent): ?string
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => $userAgent,
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            return null;
        }

        return $result;
    }

    /**
     * Write out the meta data in a nice way, so that this could be called anywhere
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \App\MetaData\MetaData $metaData
     * @return void
     */
    private function writeMetaData(OutputInterface $output, MetaData $metaData)
    {
        $output->writeln([
            "URL: $metaData->url",
            "Title: $metaData->title",
            'Authors:',
        ]);
        foreach ($metaData->authors as $author) {
            $output->writeln("- $author");
        }
        $output->writeln('');
    }
}