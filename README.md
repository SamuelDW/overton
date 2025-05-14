# Overton Challenge

## First steps
1. Read the readme of [Overton Hiring Test](https://github.com/overtonpolicy/Overton-PHP-code-test)
2. Looked into the DOM PHP libraries
3. Had a think, wrote down some ideas.
4. Figured out a baseline plan.

## The initial plan
1. You stated ideally this should be able to be run via the cli, so I have utilised `symfony/console` for this, as I have used this previously. This is just to provide a entry point to the application. The rest will be in extendable, resusable modules.
2. I will use a sqlite database initially. This is for a very small cache I would say, I could even use it in memory for now, but I will use a database. In a proper application, I would probably use a NoSQL database, for step 2 tips. I may want to add other metadata, and NoSQL would be ideal so that new fields can be added without having to really deal with entries that do not have the field
3. Use an interface for scrapers to start with. This allows a common pattern for all scrapers.
