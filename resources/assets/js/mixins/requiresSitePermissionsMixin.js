/**
 * Provides logic to help ensure necessary site permissions are set.
 * When the username or site changes it calls the permissions state's loadSiteRole with the site id from the route.
 * It make's the permission state's userSitePermissionsReady property available.
 *
 * Components using this mixin should use a :v-if="userSitePermissionsReady" to determine whether or not they can
 * be displayed yet.
 */

import { mapGetters, mapActions } from 'vuex';

export default {

	mounted() {
		this.loadSiteRole({
			siteId: this.siteId,
			username: this.username
		});
		console.log('mounted: setting permissions');
	},

	watch: {
		username: function(newUsername) {
			// when username changes fetch site permissions
			this.loadSiteRole({
				siteId: this.siteId,
				username: newUsername
			});
			console.log('username change: setting permissions');
		},

		siteId: function(newSiteId) {
			// when site changes fetch site permissions
			this.loadSiteRole({
				siteId: newSiteId,
				username: this.username
			});
			console.log('site id change: setting permissions');
		}
	},

	computed: {
		...mapGetters(['userSitePermissionsReady']),
		...mapGetters('auth', ['username']),

		siteId() {
			return this.$route.params.site_id || this.$route.params.siteId
		},
	},

	methods: {
		...mapActions([
			'loadSiteRole'
		]),
	}
};
