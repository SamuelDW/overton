# Stage 3 Scaling up

## What is probably needed.
1. A Event/Worker Queue or Task Dispatcher
2. Rate limiter
3. Proxy manager
4. Worker pool
5. backoff manager
6. Several types of caches


### Event/Worker Queue / Task Dispatcher
#### Why
Store tasks, each worker can take the next available task so not so much waiting. Could potentially split workers into tasks that take longer, those have more workers dedicated to them 

### Worker pool
#### Why
So that tasks could be run in parallel

### Rate limiter
#### Why
Requests shouldn't be spammed out, runs the risk of bans, plus time to process.

### Proxy manager
#### Why
So that IPs can be rotated, reduces risk of IP blocks. Can speed up rate of access, 1 access per second per IP, get 20 requests a second potentially
If one IP is banned, still have potentially 19 others to continue scraping, IPS are seen more like a normal user


# Thoughts
Could potentially cache the search results pages (like the three you've given me) either as a hash which is compared after got any results which can be compared to newer fetches


## very rough code

## Queue
Redis or anything else

Backoffs are seen by all workers, so that domains aren't continually blocked, quick to fetch

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