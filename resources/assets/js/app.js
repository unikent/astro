import Vue from 'vue';
import VueRouter from 'vue-router';
import ElementUI from 'element-ui';

import './bootstrap';
import locale from './locale';
import store from './store';
import { router } from './routes';
import httpPlugin from './plugins/http';
import eventBusPlugin from './plugins/eventbus';
import snackBarPlugin from './plugins/snackbar';
import './directives/inline-edit';
import App from './components/views/App';

/* global document */

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
