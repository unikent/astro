<template>
<div class="admin-wrapper">
	<aside class="left-side">
		<section class="sidebar">
			<ul v-if="homepageID" class="admin-sidebar" role="navigation">
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
import { mapState } from 'vuex';
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

		...mapState({
			currentSite: state => state.site
		}),

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
					link: `${this.url}/page/${this.homepageID}`,
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
				},
				{
					link: `${this.url}/profiles`,
					icon: 'user',
					title: 'Profiles'
				}
			];
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
