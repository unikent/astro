<template>
<div class="admin-wrapper">
	<aside class="left-side">
		<section class="sidebar">
			<ul class="admin-sidebar" role="navigation">
				<side-menu-item
					v-for="item in menu"
					:link="item.link"
					:icon="item.icon"
					:title="item.title"
					:key="item.link"
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
import Icon from 'components/Icon';

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
						<icon :name="icon" className="menu-icon" />
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
					icon: 'home',
					title: 'Home'
				},
				{
					link: '/sites',
					icon: 'sites',
					title: 'Sites'
				},
				{
					link: '/media',
					icon: 'media',
					title: 'Media Manager'
				},
				{
					link: '/settings',
					icon: 'settings',
					title: 'Settings'
				}
			]
		};
	}
};
</script>
