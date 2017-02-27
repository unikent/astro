import './bootstrap';

import Vue from 'vue';
import VueRouter from 'vue-router';
import ElementUI from 'element-ui';
import locale from './libs/locale';
import store from './store';
import App from './components/App.vue';
import { router } from './routes';

Vue.use(VueRouter);
Vue.use(ElementUI, { locale });

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

vueIfExists('#app', {
	render: h => h(App)
});

const inlineEditor = vueIfExists('#editor', {
	template: '<router-view></router-view>',
	router
});
