<template>
<div class="admin-wrapper">
	<aside class="left-side sidebar-offcanvas">
		<section class="sidebar">
			<ul class="app-sidebar" role="navigation">
				<side-menu-item
					v-for="item in menu"
					:link="item.link"
					:icon="item.icon"
					:title="item.title"
				/>
			</ul>
		</section>
	</aside>
	<div class="router-wrapper">
		<router-view />
	</div>
</div>
</template>

<script>
import Icon from '../Icon';
import homeIcon from '!IconPath/home.svg';
import settingsIcon from '!IconPath/settings.svg';
import sitesIcon from '!IconPath/files.svg';
import mediaIcon from '!IconPath/agenda.svg';

export default {
	name: 'Admin',

	components: {
		SideMenuItem: {
			props: ['link', 'icon', 'title'],

			components: {
				Icon
			},

			template: `
				<li>
					<router-link :to="link">
						<Icon :glyph="icon" className="menu-icon" />
						<span>{{ title }}</span>
					</router-link>
				</li>
			`
		}
	},

	data() {
		return {
			menu: [
				{
					link: '/home',
					icon: homeIcon,
					title: 'Home'
				},
				{
					link: '/sites',
					icon: sitesIcon,
					title: 'Sites'
				},
				{
					link: '/media',
					icon: mediaIcon,
					title: 'Media Manager'
				},
				{
					link: '/settings',
					icon: settingsIcon,
					title: 'General Settings'
				}
			]
		};
	}
};
</script>