import VueRouter from 'vue-router';

import Admin from './components/views/Admin.vue';

import Dashboard from './components/views/Dashboard.vue';
import SiteList from './components/views/SiteList.vue';
import Media from './components/views/Media.vue';
import Settings from './components/views/Settings.vue'

import Editor from './components/views/Editor.vue';
import Preview from './components/views/Preview.vue';

import Test from './components/views/Test.vue';

import NotFound from './components/views/NotFound.vue';

/* global window */

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
