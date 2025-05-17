<?php

namespace App\Command;

use App\MetaData\MetaData;
use App\Scrapers\MetaDataScraper;
use App\Scrapers\GovUkScraper;
use App\Scrapers\SearchScraper;
use App\Utility\Url;
use InvalidArgumentException;
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
     * Executes the GovUK scraper
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        #region Stage 1. Downloading the data from gov.uk
        // So that you can set this at the start
        $userAgent = $input->getOption('user-agent');
        $requestedAmountOfUrls = $input->getOption('number-of-urls');
        $searchConfig = require 'config/search_config.php';

        // These could potentially be stored, maybe the base url is everything up to the pagination, we store the pagination type i.e page= timestamp for last access so that know how long its 
        // been since last accessed
        $listingUrls = [
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest',
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=2',
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=3'
        ];

        // This part is a bit inflexible at the moment, could have mixed urls
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
        $searchScraper->scrape($listingUrls, $domain, $baseUrl);
        $urls = $searchScraper->getUrls();
        $numberOfUrls = count($urls);

        // Some output
        $output->writeln("$numberOfUrls urls to parse for metadata have been found, we will parse the first $requestedAmountOfUrls");
        $requestedUrls = array_slice($urls, 0, $requestedAmountOfUrls);
        #endregion

        #region Stage 2. Processing each link
        // This should either be injected in or in a framework usually available as a default helper but potentially could be stored in the database (NoSQL) against the base domain or similar
        $config = require 'config/scraper_config.php';
        $scraper = new MetadataScraper($config);
        $pageMetaData = [];
        // Now need to get the pages that are found from the above, and grab the meta data from them.

        // This is a really big bottleneck, so I would probably use either worker forks or async or a queue/event listener cause otherwise it would take forever for longer lists (probably what stage 3 is for really)
        // What could be done is one fork gets the data, the other reads from the array its populating so that they could be done at the same time
        $output->writeln('Now commencing parsing of urls');
        foreach ($requestedUrls as $url) {
            $html = $this->fetchPage($url, $userAgent);

            if (!$html) {
                $output->writeln("<error>Failed to fetch $url</error>");
                continue;
            }

            // Scrape and pass in the config value, there could be better ways to do this, although I imagine these will all look roughly the same other than syntax
            $scrapedData = $scraper->scrape($html, 'gov.uk');
            $metaData = new MetaData();
            $metaData = $scraper->createMetaData($scrapedData, $metaData, $url);
            $pageMetaData[] = $metaData;
        }
        // With all the entities just created, save them all to a database for post processing. Could display a summary of results, rather than every result

        foreach ($pageMetaData as $meta) {
            $output->writeln("URL: $meta->url");
            $output->writeln("Title: $meta->title");
            $output->writeln('Authors:');
            foreach ($meta->authors as $author) {
                $output->writeln($author);
            }

            $output->writeln('');
        }
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
}