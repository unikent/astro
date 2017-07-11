import Vue from 'vue';
import VueRouter from 'vue-router';
import ElementUI from 'element-ui';
import { Vue2Dragula } from 'vue2-dragula';

import './bootstrap';
import locale from './locale';
import store from './store';
import { router } from './routes';
import httpPlugin from './plugins/http';
import eventBusPlugin from './plugins/eventbus';
import snackBarPlugin from './plugins/snackbar';

import './directives/inline-edit';
import './directives/field';

import App from './views/App';

/* global document */


Vue.use(Vue2Dragula, {
	// logging: {
	// 	service: true
	// }
});

Vue.use(VueRouter);
Vue.use(ElementUI, { locale });

Vue.use(httpPlugin, { store, router });
Vue.use(eventBusPlugin);
Vue.use(snackBarPlugin);

const vueIfExists = (selector, options) => {
	if(document.querySelector(selector)) {
		return new Vue({
			el: selector,
			store,
			...options
		});
	}
	return null;
};

vueIfExists('#editor', {
	render: h => h(App),
	router
});
