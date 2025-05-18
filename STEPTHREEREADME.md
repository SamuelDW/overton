# Stage 3 Scaling up

## Possible requirements
1. NoSQL Database (Mongo, Elastic, Dynamo) for Meta data and probably one for searches
2. Task Manager
3. Proxy Manager
4. Domain Delay Registry
5. Domain Back Off Map
6. Search page cache?
7. Rate Limiter


1. NoSQL Database
## Why
This would be used for the metadata store at a minimum. Fields are flexible, generally higher write throughput. Probably mostly key based lookups, no complex join queries needed.
Could use it to store and cache the search results from the search type pages
Could have a separate one for the sources of the articles and their config

## What to use
Probably elastic search, its what you use so there is most likely a reason, either for analysis, search or easy to scale, or throughput (writes)

## How
Elastic has a very handy composer package `composer require elasticsearch/elasticsearch` and some excellent documentation.

2. Task Manager
## Why
Something to control when a task should be run, which proxy to assign and which worker gets the task. 

## What to use
Many good options, a lot of PHP options, Laravel Queue, RabbitMQ, Symfony Messenger (CakePHP has something but its not the most fleshed out)
Temporal or something in a different language, the queue doesn't have to be PHP, could use Python for pushing the jobs to the queue, PHP for the workers

## How
Not entirely sure on this part, never used one before. Something the docs cover in plenty of detail I'm sure! I imagine JSON objects are task objects, as all languages support JSON
 
3. Proxy Manager
## Why
So that IPs can be rotated, reduces risk of IP blocks. Can speed up rate of access, 1 access per second per IP, get 20 requests a second potentially
If one IP is banned, still have potentially 19 others to continue scraping, IPS are seen more like a normal user


## Proxies
Keep a map of proxies and when they were last used by domain so that the delay per IP is respected
Then we can assign a proxy to a task only if it is able to be used from the delays
i.e
Very small example
```php
if (time() < $lastCallPerDomainAndProxy[$domain][$proxy]) {
    continue; // skip this task, try another
}
```
`$lastCallPerDomainAndProxy['whitehouse.gov']['proxy1.example.com'] = timestamp;`

```php
foreach ($proxies as $proxy) {
    if (!isBlocked($domain, $proxy) && isAvailable($domain, $proxy)) {
        assignProxyToTask($task, $proxy);
        break;
    }
}
```

## Task
array 
    url => url to fetch
    domain => domain its from for config
    next allowed time // enforce the delay
    retry count // if failed how many times



## Backoff
when a 429 is recieved, this can be placed in a shared cache so that all workers can check if the domain is blocked, and to hold off on requests to it until it is expired
```php
if ($response->getStatusCode() === 429) {
    $domainBackoffUntil[$domain] = time() + 3600;
}
```

Sort tasks by next allowed time, so delays are taken into account, and possible retries are more limited
Tasks are only taken after the next allowed time is past, so that no domain is hit before its minimum delay


The scroll of rough thinking is attached as `Overton Stage 3 diagram`