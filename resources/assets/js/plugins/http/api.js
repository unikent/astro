import axios from 'axios';
import Config from '../../classes/Config';

export const baseURL = Config.get('api_base_url');

export default axios.create({
	baseURL,
	headers: {
		'X-Requested-With': 'XMLHttpRequest',
		'Accept': 'application/json',
		'Authorization': `Bearer ${Config.get('api_token', 'unknown')}`
	}
});
