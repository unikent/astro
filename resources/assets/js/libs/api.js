import axios from 'axios'

const baseURL = (typeof window.Laravel !== 'undefined' && typeof window.Laravel.base !== 'undefined') ?
	window.Laravel.base + '/api/' :
	'/api/';

module.exports = axios.create({ baseURL });