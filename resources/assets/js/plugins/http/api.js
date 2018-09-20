import axios from 'axios';
import Config from '../../classes/Config';

export const baseURL = Config.get('api_url');

export default {
	...axios.create({
		baseURL,
		headers: {
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json',
		}
	}),
	all: axios.all,
	spread: axios.spread,
	run: axios
};
