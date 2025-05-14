## Processes and thoughts

Started with a simple loop to go through the results and get the page. 
Then once this was working, looked at ways to make it less complex, more generic so that adding new services in theory should be simple
Updating things
Writing the tests (I know not TDD I'm sorry)

Thought about storing them, however:
1. This would create large amounts of storage
2. If a page is updated, it would still need fetching again
3. We need the meta data, which can be stored alongside a reference, could have a timestamp of when the data was fetched.