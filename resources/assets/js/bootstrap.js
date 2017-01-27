import jquery from 'jquery';
import './redactor';
import vue from 'vue';
import axios from 'axios';

window.$ = window.jQuery = jquery;
window.Tether = require('tether');
require('bootstrap');

window.Vue = vue;
window.axios = axios;
window.KENT = window.KENT || {};

window.axios.defaults.headers.common = {
	'X-Requested-With': 'XMLHttpRequest'
};

window.KENT.kentbar = {
	config: {
		components: [
			"staff"
		],
		styles:{
			kentfont: "https://static.kent.ac.uk/pantheon/static/webfonts/kentfont/css/kentfont.css",
			fonts: false,
			base: false
		}
	}
};
