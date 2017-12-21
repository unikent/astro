import Config from './classes/Config';
import { win } from './classes/helpers';

Config.init(win.astro);
Config.set(
	'api_base_url',
	Config.get('base_url', '') + Config.get('api_url', '/api/')
);
