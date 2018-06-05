<template>
<div class="admin-wrapper" v-if="userSitePermissionsReady">
	<aside class="left-side">
		<section class="sidebar">
			<ul v-if="homepageID" class="admin-sidebar" role="navigation">
				<side-menu-item
					v-for="item in menu"
					:item="item"
					:key="item.link"
					v-if="canUser(item.permission)"
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
import { mapState, mapGetters } from 'vuex';
import Config from 'classes/Config.js';
import Icon from 'components/Icon';
import requiresSitePermissions from 'mixins/requiresSitePermissionsMixin';

export default {
	name: 'Admin',

	mixins: [requiresSitePermissions],

	props: ['site_id'],

	components: {
		SideMenuItem: {
			props: ['item'],

			components: {
				Icon
			},

			template: `
				<li v-if="item.leave">
					<a :href="item.link" target="_blank">
						<icon :name="item.icon" className="menu-icon" />
						<span>{{ item.title }}</span>
						<icon
							v-if="item.leave"
							name="new-window"
							:width="14"
							:height="14"
							class="admin-sidebar__external-link"
						/>
					</a>
				</li>
				<li v-else>
					<router-link :to="item.link" exact>
						<icon :name="item.icon" className="menu-icon" />
						<span>{{ item.title }}</span>
					</router-link>
				</li>
			`
		}
	},

	data() {
		return {
			homepageID: null
		}
	},

	created() {
		this.fetchSiteData();
	},

	watch: {
		site_id: function(newId, oldId) {
			if (newId !== oldId) {
				this.homepageID = null;
				this.fetchSiteData();
			}
		}
	},

	computed: {

		...mapGetters([
			'canUser'
		]),

		...mapState({
			currentSite: state => state.site
		}),

		url() {
			return `/site/${this.site_id}`;
		},

		menu() {

			let menu = [
				{
					link: `${this.url}`,
					icon: 'pie-chart',
					title: 'Dashboard',
					permission: 'site.view',
				},
				{
					link: `${this.url}/page/${this.homepageID}`,
					icon: 'layout',
					title: 'Editor',
					permission: 'page.edit',
				},
				{
					link: `${this.url}/menu`,
					icon: 'menu-alt',
					title: 'Menu',
					permission: 'site.options.edit',
				},
				{
					link: `${this.url}/media`,
					icon: 'gallery',
					title: 'Media',
					permission: 'image.use',
				},
				{
					link: `${this.url}/users`,
					icon: 'user',
					title: 'Users',
					permission: 'permissions.site.assign',
				},
				{
					link: `${this.url}/profiles`,
					icon: 'write',
					title: 'Profiles',
					permission: 'profile.edit'
				}
			];

			if (Config.get('help_url')) {
				menu.push({
					link: Config.get('help_url'),
					icon: 'unknown',
					title: 'Help and Guidelines',
					leave: true,
					permission: 'site.view'
				})
			}

			return menu;
		}

	},

	methods: {

		fetchSiteData() {
			this.$store.commit('site/updateCurrentSiteID', this.site_id);
			this.$store.dispatch('site/fetchSite').then(() => {
				this.homepageID = this.currentSite.pages[0].id;
			});
		}

	}
};
</script>
