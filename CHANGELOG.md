# CHANGELOG

## HEAD
- Async operations is available on KeyValue, Event and Relationship objects. Collection and other objects will follow as soon as a proper pagination feature is implemented.
- Introduced the getException method on all objets, to read the last operation exception, in case of errors.
- Our library will not throw runtime errors anymore, please use getException as well as isError, etc, to check for errors. The only case where an error may, optionally, be thrown is with async promises's unwrap, i.e. wait(true).
- Fixed URL encode on requests, for cases an item key has slashes or other special chars.
- BC: Moved object classes to the upper level, please use andrefelipe\Orchestrate\KeyValue instead of use andrefelipe\Orchestrate\Objects\KeyValue.
- BC: Renamed our item's getBody method to getBodyArray, to avoid conflict to PSR-7 Responses.
- BC: Renamed our item's getStatus method to getReasonPhrase, to comply with PSR-7 and be cleared on their responsibility, of returning HTTP reason phrases, even though Orchestrate has custom HTTP error phrases.
- BC: Deprecated the ObjectArray class and its implementation on getValue and extract (JMESPath) methods, to ease compatibility with larger projects.

## 0.16.1 - 2016-01-05
- BC: Split methods for If-Match/If-None-Match conditionals for a much more comprehensive usage:
- Use 'putIf('myref', [value...])' instead of 'put([value...], 'myref')';
- Use 'putIf()' instead of 'put(null, true)';
- Use 'putIfNone([value...])' instead of 'put([value...], false)';
- Use 'putIfNone()' instead of 'put(null, false)';
- Use 'deleteIf('myref')' instead of 'delete('myref')';
- Same goes for all other methods, 'patchIf', 'patchMergeIf', relationship's 'putIf', 'putBothIf', 'putIfNone', 'putBothIfNone';
- Client API remains the same, and will map correctly to corresponding method.
- Added value support for relationship at the Client class.
- Fixed a few errors.

## 0.16.0 - 2016-01-03
- Upgraded our Relationship class with full support for Value body.
- Renamed 'Relation(s)' classes to 'Relationship(s)' to match Orchestrate.
- Two-way relations are made through proper 'putBoth' and 'deleteBoth' methods.
- Added getTotalRelationships method, and implemented them at the application level, to get the total item, event or relationship count of the entire application.
- Removed 'purge' method from Event to simplify, please use 'delete' instead.
- Removed 'searchEvents' from Client.
- Added 'default_http_client' function to simplify the Guzzle client instantiation.
- Removed getResponseDate() method.
- Several internal improvements.

## 0.15.0 - 2015-12-19
- Introduced the Application object to provide Root Search.
- Added support for Relation object in searches.
- Moved the Application within the Objects namespace.
- Implemented \JsonSerializable to objects.
- Removed child class support from Client.
- Added rootSearch method to Client.
- Lazily instantiates a HTTP client on any object if required.

## 0.14.0 - 2015-05-29
- Upgraded to Guzzle 6, PSR-7 compliant, cleaner and stronger.
- Requires PHP >= 5.5
- Removed our HttpClient class, in favor of direct Guzzle Client instantiation.
- getTotalItems/getTotalEvents always issue the API call.
- Added parameter to getTotalEvents to restrict to a specific event type.
- Renamed getRequestDate to getResponseDate, more concise.
- Renamed getRequestId to getOrchestrateRequestId.
- Removed getRequestUrl method.

## 0.13.2 - 2015-04-07
- Removed logic from getTotalCount so it doesn't conflict with queries total count.
- Created getTotalItems and getTotalEvents methods to get the respective values.
- Fixed collection delete status code.
- getAggregates returns ObjectArray for each object-syntax access.

## 0.13.1 - 2015-04-02
- Added host and version parameters to HttpClient constructor.
- Removed AbstractHttpConnection as we simplified Application and Client constructor.

## 0.13.0 - 2015-03-31
- Implemented Events Search!
- 'setChildClass' is split into 'setItemClass' and 'setEventClass' as now Collections can search both items and events.
- Collections and KeyValues can create event instances.
- The HTTP client is split to its own class, simplifing much of our code base.
- 'setClient' method is now 'setHttpClient' so it doesn't confuse with the Client class.
- Improved totalCount method to actually make an API call if the total count is not know yet.
- Several improvements throughout the library.

## 0.12.0 - 2015-03-27
- Added Serializable to objects.
- Simple but major change, arrays values set to a KeyValue/Event do not get converted to ObjectArray anymore. This provides consistency with our model subclass feature, as any subclass public property would not get the ObjectArray convertion, therefore creating an inconsistent behaviour.
- ObjectArrays are only created when extract values with getValue or extract methods.

## 0.11.0 - 2015-03-22
- Added support for custom setter/getters on KeyValue/Event to get properties mapped into the Value.
- Corrected how operations handles values internally on success. KeyValue GET no longer reset values before setting the new ones. Put, delete and purge, consciously reset/overwrite values according to the results. Same for Event.
- Improved protection on KeyValue setValue so indexed arrays at root level are avoided.
- Removed Countable from KeyValue/Event as we can't rely on that.

## 0.10.2 - 2015-03-13
- Ooops, missed a letter 's' on a method.

## 0.10.1 - 2015-03-13
- Considered our usage of JMESPath erroneous, by sending our objects directly, it would render JMES array functions useless. So now we only send arrays to JMESPath.
- Changed 'jmesPath' method name to 'extract', it now uses the toArray() method of each object, and returns ObjectArrays when possible.
- Introduces 'extractValue' (for KV) and 'extractValues' (for lists) methods, which uses getValue/getValues method, providing a less verbose JMESPath expression.
- Every object has a extract method.
- Fixed a bug when setting values with item[] = 'a' notation on ObjectArray.

## 0.10.0 - 2015-03-11
- Implemented two-way relation put/delete.
- Implemented KeyRangeBuilder (collection list) and TimeRangeBuilder (event list), replacing array range parameters.
- Removed setApiVersion, still too early to know if we can just change it when a new version comes up.
- Added on JMESPath to objects.

## 0.9.0 - 2015-03-09
- Implemented Refs, Events and Graph directly within the KeyValue class, check out our README.
- putRelation and deleteRelation are removed from KeyValue to favor the new, and more useful, method with the Relation class.
- next/prev methods are now named as nextPage/prevPage, much clearer on what they do.
- Implemented KeyValue and Event interfaces.
- Children classes can now easily be changed with setKeyValueClass/setEventClass at Client instance, and setChildrenClass at all list objects (Collection, Events, Refs).

## 0.8.0 - 2015-03-06
- Radical change on classes, Application is now rightfully 'Client'.
- Application is defined as the entry point to the object API.
- Collection is defined as an Object, merging KeyValues (list query) and Search classes.
- Ref and SearchResult merged into KeyValue, to favor custom child classes.
- Added methods to change the child classes generated, for example, if you extend KeyValue to create your model, you can tell a Collection to always use your class instead of the default KeyValue.

## 0.7.0 - 2015-02-28
- KeyValue stores values directly in itself, making it a snap to use as Models!
- Introduces the ObjectArray, a class that makes data accessible either via object syntax or array syntax.
- Added methods to merge one object into another (merge search results, merge items Value).
- All object's operations now return boolean of the success, i.e. $keyValue->get(), $keyValue->put(). Application and Collection still returns the respective object instance.
- Objects now have a handy toJson method.

## 0.6.0 - 2015-02-04
- Removed the automatic client linking for the Objects, it has to be linked manually with setHttpClient(). It would create conflicts on larger systems, where multiple clients could be created anywhere.
- Many getters that has required values for the API calls now has the option to throw errors
- General code cleanup and inline docs improvements

## 0.5.0 - 2015-01-08
- Implemented Patch operations!
- Full API parity
- Now road is clear to optimizations, Docs and Tests

## 0.4.0 - 2015-01-08
- Implemented search Aggregates
- Added KeyValue subclasses (Ref and SearchResult)

## 0.3.0 - 2015-01-06
- Full API parity (excluding only Patch operations)
- Graph implemented
- Cleaned project further

## 0.2.0-alpha - 2015-01-04
- Implemented List, Refs and Events
- Added pagination methods for all List operations (search, events, refs)
- Cleaner file structure

## 0.1.0-alpha - 2014-12-31
- Initial version
- Client logic OK
- KeyValue OK (without Patch yet)
- Search OK (but still misses a method for pagination)
- List, Events and Graph NOT YET IMPLEMENTED