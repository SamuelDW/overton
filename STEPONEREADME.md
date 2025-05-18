# Step 1 Downloading the Data

## More about it
Use `bin/console.php scrape` to run
Use --user-agent to pass in a specific user agent
It should on first run call the actual pages, and then on further requests use the cached file
The results are stored against the object

## Improvements
Make the scraper generic, like the metadata scraper, could use config based rules, all pages are basically looking for similar data, its just the xpaths that are different. Having a scraper for every page would lead to hundreds of scrapers, its not maintainable in the long run. The config rules could probably be held in a database so that access is available for all services. Many rules, one service. I've stuck an very short example in the `search_config.php` which currently isn't used

Improvements added in the branch `generic-scraper`. 

## Further Improvements from SamuelDW/generic-scraper
- As of now, the urls to parse are hardcoded in. This is not great, and there should be a way to pass in the urls either as an options argument, a CSV, or ideally a remote call to a database.
The scraper only accepts URLs in the list that are of the same URL, if the URLs are mixed, then it would break. Ideally this could if it's the only one deployed parse the url and call the correct domain for the URL. 
- Alternatively, I could use Swoole, Guzzle or I think FrankenPHP for having a pool of workers, each worker deals with a specific url

The cached files could be  held and hashed and in future at certain intervals, newly fetched and the hashes of the content is compared to see if the content has changed, if so, replace file in the cache and then do some more scraping otherwise ignore

## Issues

Did want to cover all the sections added in tests, but as you aren't seeing my ability to test things, more my thinking I left these out so that I could concentrate on my thoughts and ideas
Couldn't quite figure out the test for the search scraper, think that was just a case of not quite able to get the file properly