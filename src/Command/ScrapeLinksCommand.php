<?php

namespace App\Command;

use App\Scrapers\GovUkScraper;
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
        $userAgent = $input->getOption('user-agent');
        $requestedAmountOfUrls = $input->getOption('number-of-urls');
        $output->writeln("User agent: $userAgent");
        $output->writeln("Number of urls to parse: $requestedAmountOfUrls");

        $govUkScraper = new GovUkScraper($userAgent);

        $govUkScraper->scrape([
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest',
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=2',
            'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=3'
        ]);

        $urls = $govUkScraper->getUrls();
        $numberOfUrls = count($govUkScraper->getUrls());

        $output->writeln("This has found: $numberOfUrls urls");

        $requestedUrls = array_slice($urls, 0, 50);
        return Command::SUCCESS;
    }
}