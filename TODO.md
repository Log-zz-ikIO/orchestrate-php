# TODO and IDEAS   

- Rethink the responsibility of Collections, as a pagination is implemented. It would not store results within itself anymore, and would evolve into a more responsible factory. Other list classes would merge in as well. 

- Implement a better pagination (study AWS 3), including an option to automatically load the next pages when doing a iteration (foreach) — maybe a simple, foreach ($collection->page() as $page) { foreach ($page as $item) } }  ??

- Maybe be more strict on api methods.. for example, if a get() is not sucessful, maybe it should reset the value after all?

- Maybe our Client class is badly named, it's a Factory after all. Maybe if Application and Collection class is upgraded to factories too, the Client could be removed entirelly.

- Work on Search Query builder (query + sort + aggregate builder, then be used on collection->query($queryBuilder)) or better, use collection->search('query*', $options), where $options = (new Orchestrate/Query/SearchOptions())->limit(10)->sort('title')
- scheme: app->query(‘*')->sort(’title:asc')->get(20) — ou ->find() ou ->send() ou ->search()
- scheme: app->search(‘*’, [’sort’ => ’title:asc’, ‘limit’ => 20]]);
- scheme: listItems()->range()->limit()->get() (maybe always end in 'get')
---Do not use the object itself to build the query chain, it's confusing---

- Provide a quicker access to query builders by allowing regular arrays, which get passed to a init on each query builder! I.E. collection->search('query*', ['limit' => 10, 'sort' => 'title'])

- Field Filtering feature

- o event não poderia usar o setKeyRefFromLocation ?? parece que sim!

- maybe add method 'resetPath' to intentionally reset only the objects identification properties?

- Maybe add an 'newInstance' method to each Object, to make it easier to create a empty instance to work on. Probably it should come pre-set with the same name/collection.

- Consider the usage of 'clone' directive, should we do something custom? __clone()

- Docs on Graph Get and Graph conditionals, check for client api change as well.

- Study the removal of the constructor params in favor of a single array or string — if array use init / if string consider as path and split accordingly

- Add relationships(), relationship() methods to Collection (and probably Application)

- Implement Bulk operations support 

- Add a class map on Application/Collection to make it easier to instantiate different items and collections depending on rules

- getReftime could automatically load the reftime if not provided? only if required, to not make API calls without the user knowing

- Maybe change 'patchMerge' on KeyValue to just 'merge'

- Add feature of getting lists above the limit of 100, even passing -1 to get entire list (pages loading will happen in background)

- Consider BLOB storage support?

- could have find/search/findFirst method on KeyValue to search the collection and load the first match?

- Collection could follow too? findFirst?

- Maybe implement __toString and __debugInfo for debug pourposes. But study the best pattern before release, so it doesn't change later. __toString could print some like the fully qualified path

- Review all inline Docs

- Suggest/make it easier to add Cache interface?

- Implement Tests

- Add sort operations to List results?

- Add method to move an item to another Collection or even Application?

- Event system? Maybe a subclass of a collection could be set up to log all activity, for example?

- Implement Guzzle logger interface?
