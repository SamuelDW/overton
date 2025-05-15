## Processes and thoughts

Started with a simple loop to go through the results and get the page. 
Then once this was working, looked at ways to make it less complex, more generic so that adding new services in theory should be simple see the `scraper_config.php` for how I've gone about this so far.
Updating things
Writing the tests (I know not TDD I'm sorry)

Thought about storing them, however:
1. This would create large amounts of storage
2. If a page is updated, it would still need fetching again
3. We need the meta data, which can be stored alongside a reference, could have a timestamp of when the data was fetched.

## Improvements
Could store the config in the database, again for availability and so that easier visability or updating via a dashboard etc, rather than a PR request for every change


## Discussion
`What other approaches could we take to extracting titles, authors and publication dates from pages like these?`

Check that APIs are used or not for this data already, saves resources scraping if the platform already has this information available via an API

Microdata or json+ld, this may contain all the necessary information or at least parts of it. I've used a small amount for my own projects for google search and can parse this as JSON, should be a bit simpler, but caveats is that not all json+ld will contain the necessary info

Given that a page might not always have these meta tags, we can fall back on some element or patterns such as the `<title>` attribute or `<h1>` or common patterns like Published: Date

Could potentially use Symfony CSS crawler, which could be easier to read quicker though might not be flexible all the time

For anything that falls outside these, log them could potentially do analysis or machine learning stuff
