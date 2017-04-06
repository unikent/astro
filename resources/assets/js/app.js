import './bootstrap';

import Vue from 'vue';
import VueRouter from 'vue-router';
import ElementUI from 'element-ui';

import httpPlugin from './plugins/http'
import eventBusPlugin from './plugins/eventbus';
import snackBarPlugin from './plugins/snackbar';
import locale from './libs/locale';
import store from './store';
import App from './components/views/App.vue';
import { router } from './routes';

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

const inlineEditor = vueIfExists('#editor', {
	render: h => h(App),
	router
});
