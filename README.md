# Overton Challenge

## First steps
1. Read the readme of [Overton Hiring Test](https://github.com/overtonpolicy/Overton-PHP-code-test)
2. Looked into the DOM PHP libraries as I've not really done any scraping before. I imagined a lot of pattern matching, though being honest, it was more about handling the data afterwards really.
3. Had a think, wrote down some ideas.
4. Figured out a baseline plan to at least get things up and running

## Requirements
1. PHP 8.2
2. Some CLI experience
3. Run `composer install` and then for specific bits see the stage readme

## The initial plan
1. You stated ideally this should be able to be run via the cli, so I have utilised `symfony/console` for this, as I have used this previously. This is just to provide a entry point to the application. The rest will be in classes called in the command, ideally these could be held in an external library so more than just the command could use them.
2. I will use a very simple cache with a folder and using `file_put_contents`. In a proper application, I would probably use a NoSQL database, for step 2 tips. I may want to add other metadata, and NoSQL would be ideal so that new fields can be added without having to really deal with entries that do not have the field (otherwise potentially masses of migration files)
3. Use an interface for scrapers to start with. This allows a common pattern for all scrapers. Addendum this could probably be a very generic scraper and do more config based stuff.


## Ideal flow
1. Get the links, user agent etc set up 
2. Get the pages for the links, store them in the cache if they don't exists
3. Extract the links and sort them out and store them somewhere
3. Create the metadata scraper
4. Get the pages for the links found in the first run
5. Scrape the metadata information from those pages
6. Store and display the information gained.

## The actual flow
1. did get the user agent and parts set up
2. Got the pages for the links and got those being stored whilst extracting the links.
3. Did create a very tightly coupled scraper for the meta data then made this config based so should be simpler to extend

## Structure
### Scrapers
I have created a base abstract class for the scraper. This will allow future scrapers to inherit off this, keeping logic in one place, other than the extract links, which may require different paths for different sites. Addendum: This might be better off as a config driven thing, pages will all be HTML, it's only the DOMXPath that will differ, so I started the process of making config files. 
Probably needs a bit more thought into how the config works, could potentially store this in a database as well, so multiple services can use it.

This has some base defaults for the scrapers, such as a delay, cache location.




