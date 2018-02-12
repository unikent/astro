import VueRouter from 'vue-router';

import Admin from './views/Admin';

import Dashboard from './views/Dashboard';
import SiteList from './views/SiteList';
import Media from './views/Media';
import Settings from './views/Settings';

import Editor from './views/Editor';
import MenuEditor from './views/MenuEditor';
import SiteUsers from './views/SiteUsers';
import Preview from './views/Preview';
import NotFound from './views/NotFound';
import Config from './classes/Config';

import SiteProfiles from '@theme/profiles/views/SiteProfiles';
import ProfileEditor from '@theme/profiles/views/ProfileEditor';
import ProfilePreview from '@theme/profiles/views/ProfilePreview';
import ProfileTopBar from '@theme/profiles/components/topbar';

import TopBar from 'components/topbar';

const routes = [
	{
		path: '/',
		components: {
			default: SiteList,
			topbar: TopBar
		},
		name: 'site-list'
	},
	{
		path: '/site/:site_id',
		components: {
			default: Admin,
			topbar: TopBar
		},
		props: {
			default: true
		},
		children: [
			{
				path: '',
				component: Dashboard,
				name: 'dashboard',
			},
			{
				path: 'menu',
				component: MenuEditor,
				name: 'menu-editor'
			},
			{
				path: 'media',
				component: Media,
				name: 'media-manager'
			},
			{
				path: 'users',
				component: SiteUsers,
				name: 'site-users'
			},
			{
				path: 'profiles',
				component: SiteProfiles,
				name: 'site-profiles'
			}
		]
	},
	{
		path: '/site/:site_id/page',
		components: {
			default: Editor,
			topbar: TopBar
		},
		name: 'site'
	},
	{
		path: '/site/:site_id/page/:page_id',
		components: {
			default: Editor,
			topbar: TopBar
		},
		name: 'page'
	},
	{
		path: '/site/:site_id/profile/:profileId',
		components: {
			default: ProfileEditor,
			topbar: ProfileTopBar
		},
		name: 'profile-editor',
		props: {
			default: true,
			topbar: true
		}
	},
	{
		path: '/site/:site_id/preview/profile/:profileId',
		component: ProfilePreview,
		name: 'profile-preview',
		props: true
	},
	{
		path: '/preview/:page_id',
		component: Preview,
		name: 'preview'
	},
	{
		path: '/user/:user_id/settings',
		component: Settings,
		name: 'settings'
	},
	{
		path: '*',
		component: NotFound,
		name: '404'
	}
];

export const router = new VueRouter({
	mode: 'history',
	routes,
	base: `${Config.get('base_url', '')}/`
});
