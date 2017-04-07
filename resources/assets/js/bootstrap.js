import './redactor';
import Vue from 'vue';

window.eventBus = (window.self === window.top ? new Vue() : window.top.eventBus);

window.axios.defaults.headers.common = {
	'X-Requested-With': 'XMLHttpRequest',
	'Accept': 'application/json'
};
