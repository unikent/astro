import VueRouter from 'vue-router';

import Preview from './components/Preview.vue';
import Editor from './components/Editor.vue';
import NotFound from './components/NotFound.vue';

const routes = [
	// { path: '/site', component: SiteList },
	// { path: '/site/:site_id', component: Site },
	// { path: '/site/:site_id/page/:page_id', component: Page },
	{ path: '/inline', component: Editor },
	{ path: '/preview', component: Preview },
	{ path: '*', component: NotFound }
];

export const router = new VueRouter({
	mode: 'history',
	routes
});