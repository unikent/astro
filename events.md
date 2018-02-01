# Events & Dynamic Blocks #

## Dynamic Blocks ##

Dynamic blocks are those with do one or both of:
 * Dynamic routing (creating dynamic pages) for URLs below that of the page in which they are included.
 * Inject custom data into the block field data dynamically (via API calls, database access, calculations)
 
A dynamic block's definition.json MUST include the attribute "dynamic" with a value of 1 or true
with a php block class extending ```App\Models\Definitions\Block```.

This block class (and the file in which it is stored in the block's definitions directory) must be
named after the {definition-name}-v{definition-version} identifier for the block, where:
 * The first character will be uppercased.
 * Any instance of one or more non-alphanumeric characters are removed, and the following character
   uppercased.
 * All remaining characters will be identical to the block definition name.
 * A suffix will be appended in the form "V{version}".
 
For example, a dynamic block definition:
 
 ```/blocks/-my_ACE-custom--block/v1/definition.json``` would have a class
named 

```MyACECustomBlockV1``` and stored in 

```/blocks/-my_ACE-custom--block/v1/MyACECustomBlockV1.php```
   

### Dynamic Content ###

To modify the data returned in the 'fields' attribute for this block, the block class must implement the
Block::filterData() method.

### Dynamic Routing ###

Any block which wishes to support dynamic routing should implement the route() method,
returning a new DynamicPage object if the requested route is matched and otherwise false.


## Events ##

__Under Construction - WIP - ignore for now__

The Astro API fires a number of events and dynamic blocks can register listeners for these.

Any custom block definition class will have access to a $this->dispatcher property.

To listen to an event, call ```$this->dispatcher->listen('event.identifier', $callback);```

A dynamic block can also specify events to listen to in its definition.json file, although these listeners
will only be triggered on requests where an instance of that block has been created (definition loaded).

```javascript

{
	// ... rest of definition here

	"events": {
		"some.event.identifier":  "handlerMethod", 
		"some.other.event.identifier": "otherHandlerMethod"

		// where "handlerMethod" and "otherHandlerMethod" are methods defined in the block's
		// php class.
	}
}

```

### Example Dynamic Block Class ###

```php

class MyDynamicBlock extends App\Models\Definitions\Block
{
	public function reroute($path, &$block, $page, $resolver)
	{
		if(preg_match('/^\/(?P<profile>[a-z0-9_-]+)$/i', $path, $matches)) {
			$this->profile = [
				'foo' => 'bar',
				'name' => 'sam yapp',
				'username' => 'sam',
				'email' => 'sam@example.com'
			];
			$this->dispatcher->listen('api.filter.routes.resolve.response', [$this, 'onFilterResponseData']);
			return true;
		}
		return false;
	}
	
	public function onFilterResponse(App\Events\FilterResponseData $event)
	{
		// do something clever with $event->data 
	}
}

```

### Available Events ###

#### api.filter.routes.resolve.response ####

Subscribing to this event allows the subscriber to modify the response data returned from 
a request to resolve a route (GET /api/v1/routes/resolve...).

Listeners will be passed an instance of a [FilterResponseData event](app/Events/FilterResponseData.php)
which will contain the response json as a php array in its $event->data attribute.

The primary use-case is expected to be dynamic blocks which need to replace the
routed page with dynamically generated data, or inject additional dynamic data into the page's
blocks when outputting to a renderer.

