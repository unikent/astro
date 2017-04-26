import Config from './classes/Config';

/* global window */

Config.init(window.astro);
Config.set(
	'api_base_url',
	Config.get('base_url', '') + Config.get('api_url', '/api/')
);
