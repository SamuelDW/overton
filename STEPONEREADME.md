# Step 1 Downloading the Data

## More about it
Use `bin/console.php scrape` to run
It should on first run call the actual pages, and then on further requests use the cached file
The results are stored against the object

## Improvements
Make the scraper generic, like the metadata scraper, could use config based rules, all pages are basically looking for similar data, its just the xpaths that are different. Having a scraper for every page would lead to hundreds of scrapers, its not maintainable in the long run. The config rules could probably be held in a database so that access is available for all services. Many rules, one service.

The cached files could be  held and hashed and in future at certain intervals, newly fetched and the hashes of the content is compared to see if the content has changed, if so, replace file in the cache and then do some more scraping otherwise ignore

URLS could be passed in via csv or remote calls

Ideally the results from search pages should be stored, rather than held in memory for future use or getting more data

## Issues

Not quite a fan of just having the links held in the command, ideally this should either be a CSV passed in, or an API call so that is flexible

Did want to cover all the sections added in tests, but as you aren't seeing my ability to test things, more my thinking I left these out so that I could concentrate on my thoughts and ideas