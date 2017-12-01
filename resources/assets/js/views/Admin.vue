<template>
<div class="admin-wrapper">
	<aside class="left-side">
		<section class="sidebar">
			<ul class="admin-sidebar" role="navigation">
				<side-menu-item
					v-for="item in menu"
					:item="item"
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

	props: ['site_id'],

	components: {
		SideMenuItem: {
			props: ['item'],

			components: {
				Icon
			},

			template: `
				<li>
					<router-link :to="item.link" exact>
						<icon :name="item.icon" className="menu-icon" />
						<span>{{ item.title }}</span>
						<icon
							v-if="item.leave"
							name="new-window"
							width="14"
							height="14"
							class="admin-sidebar__external-link"
						/>
					</router-link>
				</li>
			`
		}
	},

	computed: {

		url() {
			return `/site/${this.site_id}`;
		},

		menu() {
			return [
				{
					link: `${this.url}`,
					icon: 'pie-chart',
					title: 'Dashboard'
				},
				{
					link: `${this.url}/page/1`,
					icon: 'layout',
					title: 'Editor',
					leave: true
				},
				{
					link: `${this.url}/menu`,
					icon: 'menu-alt',
					title: 'Menu'
				},
				{
					link: `${this.url}/media`,
					icon: 'gallery',
					title: 'Media'
				},
				{
					link: `${this.url}/users`,
					icon: 'user',
					title: 'Users'
				}
			];
		}

	}
};
</script>
