import VueRouter from 'vue-router';

import Admin from './app/Admin.vue';

import Dashboard from './app/Dashboard.vue';
import SiteList from './app/SiteList.vue';
import Media from './app/media/Media.vue';
import Settings from './app/Settings.vue'
import Preview from './app/Preview.vue';

import Editor from './components/Editor.vue';
import Test from './app/Test.vue';
import NotFound from './app/NotFound.vue';

const routes = [
	{
		path: '/',
		component: Admin,
		redirect: '/home',
		children: [
			{
				path: 'home',
				component: Dashboard
			},
			{
				path: 'sites',
				component: SiteList
			},
			{
				path: 'media',
				component: Media
			},
			{
				path: 'settings',
				component: Settings
			}
		]
	},
	{
		path: '/site/:site_id',
		component: Editor
	},
	{
		path: '/site/:site_id/page/:page_id',
		component: Editor
	},
	{
		path: '/preview',
		component: Preview
	},
	{
		path: '/test',
		component: Test
	},
	{
		path: '*',
		component: NotFound
	}
];

export const router = new VueRouter({
	mode: 'history',
	routes,
	base: (typeof window.Laravel.base !== 'undefined') ? `${window.Laravel.base}/` : '/'
});