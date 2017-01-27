import './bootstrap';

import Vue from 'vue'
import App from './vue/App.vue'
import PageList from './vue/PageList.vue'

var VueResource = require('vue-resource');

Vue.use(VueResource);

new Vue({
	el: '#app',
	render: h => h(App)
});

new Vue({
	el: '#js-page-list',
	...PageList
});

$('[data-toggle="offcanvas"]').click(function() {
	$('.row-offcanvas').toggleClass('active');
	$('.row-oncanvas').toggleClass('col-sm-12').toggleClass('col-sm-8');
});