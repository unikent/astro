import axios from 'axios';

export const baseURL = (typeof window.Laravel !== 'undefined' && typeof window.Laravel.base !== 'undefined') ?
	window.Laravel.base + '/api/' :
	'/api/';

export default axios.create({ baseURL });