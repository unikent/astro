# Events in Astro #

The Astro API fires a number of events and dynamic blocks can register listeners for these.

Any custom block definition class will have access to a $this->dispatcher property.

To listen to an event, call ```$this->dispatcher->listen('event.identifier', $callback);```

## Example Dynamic Block Class ##

```php
<?php

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

## Available Events ##

### api.filter.routes.resolve.response ###

Subscribing to this event allows the subscriber to modify the response data returned from 
a request to resolve a route (GET /api/v1/routes/resolve...).

Listeners will be passed an instance of a [FilterResponseData event](app/src/Events/FilterResponseData.php)
which will contain the response json as a php array in its $event->data attribute.

The primary use-case is expected to be dynamic blocks which need to replace the
routed page with dynamically generated data, or inject additional dynamic data into the page's
blocks when outputting to a renderer.

