import './redactor';
import 'bootstrap';
import Vue from 'vue';

window.eventBus = (window.self === window.top ? new Vue() : window.top.eventBus);

window.KENT = window.KENT || {};
window.axios.defaults.headers.common = {
	'X-Requested-With': 'XMLHttpRequest'
};

window.KENT.kentbar = {
	config: {
		components: [
			'staff'
		],
		styles:{
			kentfont: 'https://static.kent.ac.uk/pantheon/static/webfonts/kentfont/css/kentfont.css',
			fonts: false,
			base: false
		}
	}
};
