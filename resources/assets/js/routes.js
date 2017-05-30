import VueRouter from 'vue-router';

import Admin from './components/views/Admin';

import Dashboard from './components/views/Dashboard';
import SiteList from './components/views/SiteList';
import Media from './components/views/Media';
import Settings from './components/views/Settings'

import Editor from './components/views/Editor';
import Preview from './components/views/Preview';
import NotFound from './components/views/NotFound';
import Config from './classes/Config';

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
		component: Editor,
		name: 'site'
	},
	{
		path: '/site/:site_id/page/:page_id',
		component: Editor,
		name: 'page'
	},
	{
		path: '/preview/:site_id',
		component: Preview,
		name: 'preview'
	},
	{
		path: '*',
		component: NotFound
	}
];

export const router = new VueRouter({
	mode: 'history',
	routes,
	base: `${Config.get('base_url', '')}/`
});
